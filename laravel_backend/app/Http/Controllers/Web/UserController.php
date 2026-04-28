<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PriceHistory;
use App\Models\Category;
use App\Models\Commodity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function home(Request $request)
    {
        $user = Auth::user();

        $search   = $request->get('search');
        $category = $request->get('category'); // ini sekarang category_id
        $date     = $request->get('date');

        $query = PriceHistory::query();

        if ($search) {
            $query->where('commodity_name', 'like', '%' . $search . '%');
        }

        // FIX: filter by category_id, bukan category string
        if ($category && $category !== 'Semua') {
            $query->where('category_id', $category);
        }

        if ($date) {
            $query->where('date', '>=', Carbon::parse($date)->startOfDay())
                  ->where('date', '<=', Carbon::parse($date)->endOfDay());
        }

        $recentPrices = $query->orderBy('date', 'desc')->paginate(10)->appends(request()->query());
        $recentPrices->load('categoryRelation');

        foreach ($recentPrices as $item) {
            $hargaKemarin = PriceHistory::where('commodity_name', $item->commodity_name)
                ->where('date', '>=', Carbon::parse($item->date)->subDay()->startOfDay())
                ->where('date', '<=', Carbon::parse($item->date)->subDay()->endOfDay())
                ->first();

            $hargaLama     = $hargaKemarin ? $hargaKemarin->harga_sekarang : $item->harga_sekarang;
            $item->selisih = $item->harga_sekarang - $hargaLama;
            $item->persen  = $hargaLama > 0 ? ($item->selisih / $hargaLama) * 100 : 0;
        }

        $statQuery = PriceHistory::query();
        if ($search) {
            $statQuery->where('commodity_name', 'like', '%' . $search . '%');
        }
        if ($category && $category !== 'Semua') {
            $statQuery->where('category_id', $category); // FIX
        }
        if ($date) {
            $statQuery->whereDate('date', $date);
        }

        $adaFilter = $search || ($category && $category !== 'Semua') || $date;

        if ($adaFilter) {
            $hargaTerbaruData = (clone $statQuery)->orderBy('date', 'desc')->first();
            $hargaTerbaru     = $hargaTerbaruData ? $hargaTerbaruData->harga_sekarang : 0;
            $namaKomoditas    = $hargaTerbaruData ? $hargaTerbaruData->commodity_name : '-';
        } else {
            $hargaTerbaru     = round((clone $statQuery)->orderBy('date', 'desc')->avg('harga_sekarang') ?? 0);
            $namaKomoditas    = 'Semua Komoditas';
            $hargaTerbaruData = (clone $statQuery)->orderBy('date', 'desc')->first();
        }

        if ($adaFilter && isset($hargaTerbaruData)) {
            $bulanLalu = Carbon::parse($hargaTerbaruData->date)->subMonth()->endOfDay();
            $hargaBulanLaluData = PriceHistory::where('commodity_name', $hargaTerbaruData->commodity_name)
                ->where('date', '<=', $bulanLalu)
                ->orderBy('date', 'desc')
                ->first();
            $hargaBulanLalu = $hargaBulanLaluData ? $hargaBulanLaluData->harga_sekarang : $hargaTerbaru;
        } else {
            $bulanLalu      = Carbon::now()->subMonth()->endOfDay();
            $hargaBulanLalu = round(PriceHistory::where('date', '<=', $bulanLalu)->avg('harga_sekarang') ?? $hargaTerbaru);
        }

        $hargaKemarin = $hargaBulanLalu;
        $hargaChange  = $hargaTerbaru - $hargaBulanLalu;
        $hargaPercent = $hargaBulanLalu > 0 ? ($hargaChange / $hargaBulanLalu) * 100 : 0;

        $last7Days = (clone $statQuery)->orderBy('date', 'desc')->take(7)->pluck('harga_sekarang')->toArray();

        if (count($last7Days) > 1) {
            $mean             = array_sum($last7Days) / count($last7Days);
            $variance         = array_sum(array_map(fn($x) => pow($x - $mean, 2), $last7Days)) / count($last7Days);
            $indexVolatilitas = round(sqrt($variance) / $mean, 2);
            $statusVolatilitas = match(true) {
                $indexVolatilitas < 0.1 => 'Rendah',
                $indexVolatilitas < 0.3 => 'Sedang',
                default                 => 'Tinggi',
            };
        } else {
            $statusVolatilitas = 'Rendah';
            $indexVolatilitas  = 0;
        }

        // FIX: categoryList dari model Category, bukan distinct dari PriceHistory
        $categoryList = Category::orderBy('name')->get(['_id', 'name']);

        // PIE CHART — pakai categoryRelation
        try {
            $allData = PriceHistory::whereNotNull('category_id')
                ->orderBy('date', 'desc')
                ->limit(1000)
                ->get(['category_id', 'commodity_name', 'harga_sekarang', 'date']);

            if ($allData->isEmpty()) {
                $chartLabels = [];
                $chartValues = [];
            } else {
                // Load category names
                $categoryNames = Category::all()->keyBy(fn($c) => (string) $c->_id);

                $grouped = $allData
                    ->filter(fn($item) => !empty($item->category_id))
                    ->groupBy(fn($item) => (string) $item->category_id)
                    ->map(function ($items) {
                        $latestPerCommodity = $items
                            ->groupBy('commodity_name')
                            ->map(function ($rows) {
                                return $rows->sortByDesc(fn($r) => $r->date instanceof Carbon
                                    ? $r->date->timestamp
                                    : strtotime($r->date)
                                )->first()->harga_sekarang;
                            });
                        return (int) round($latestPerCommodity->avg());
                    })
                    ->filter(fn($val) => $val > 0)
                    ->sortDesc();

                $chartLabels = array_values($grouped->keys()->map(
                    fn($id) => $categoryNames[$id]->name ?? 'Unknown'
                )->toArray());
                $chartValues = array_values($grouped->values()->map(fn($v) => (int) $v)->toArray());
            }
        } catch (\Exception $e) {
            Log::error('Pie Chart Error: ' . $e->getMessage());
            $chartLabels = [];
            $chartValues = [];
        }

        return view('user.home', compact(
            'user', 'recentPrices', 'namaKomoditas', 'hargaTerbaru',
            'hargaChange', 'hargaPercent', 'hargaKemarin',
            'statusVolatilitas', 'indexVolatilitas',
            'categoryList', 'chartLabels', 'chartValues'
        ));
    }

    public function downloadPdf(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category');
        $date     = $request->get('date');

        $query = PriceHistory::query();

        if ($search) {
            $query->where('commodity_name', 'like', '%' . $search . '%');
        }
        if ($category && $category !== 'Semua') {
            $query->where('category_id', $category); // FIX
        }
        if ($date) {
            $query->where('date', '>=', Carbon::parse($date)->startOfDay())
                  ->where('date', '<=', Carbon::parse($date)->endOfDay());
        }

        $recentPrices = (clone $query)->orderBy('date', 'desc')->take(500)->get();
        $recentPrices->load('categoryRelation');

        foreach ($recentPrices as $item) {
            $hargaKemarin  = PriceHistory::where('commodity_name', $item->commodity_name)
                ->where('date', '>=', Carbon::parse($item->date)->subDay()->startOfDay())
                ->where('date', '<=', Carbon::parse($item->date)->subDay()->endOfDay())
                ->first();
            $hargaLama     = $hargaKemarin ? $hargaKemarin->harga_sekarang : $item->harga_sekarang;
            $item->selisih = $item->harga_sekarang - $hargaLama;
            $item->persen  = $hargaLama > 0 ? ($item->selisih / $hargaLama) * 100 : 0;
        }

        $adaFilter = $search || ($category && $category !== 'Semua') || $date;

        if ($adaFilter) {
            $hargaTerbaruData = (clone $query)->orderBy('date', 'desc')->first();
            $hargaTerbaru     = $hargaTerbaruData ? $hargaTerbaruData->harga_sekarang : 0;
            $namaKomoditas    = $hargaTerbaruData ? $hargaTerbaruData->commodity_name : '-';
        } else {
            $hargaTerbaru     = round(PriceHistory::orderBy('date', 'desc')->avg('harga_sekarang') ?? 0);
            $namaKomoditas    = 'Semua Komoditas';
            $hargaTerbaruData = PriceHistory::orderBy('date', 'desc')->first();
        }

        if ($adaFilter && isset($hargaTerbaruData)) {
            $bulanLalu = Carbon::parse($hargaTerbaruData->date)->subMonth()->endOfDay();
            $hargaBulanLaluData = PriceHistory::where('commodity_name', $hargaTerbaruData->commodity_name)
                ->where('date', '<=', $bulanLalu)->orderBy('date', 'desc')->first();
            $hargaBulanLalu = $hargaBulanLaluData ? $hargaBulanLaluData->harga_sekarang : $hargaTerbaru;
        } else {
            $bulanLalu      = Carbon::now()->subMonth()->endOfDay();
            $hargaBulanLalu = round(PriceHistory::where('date', '<=', $bulanLalu)->avg('harga_sekarang') ?? $hargaTerbaru);
        }

        $hargaKemarin  = $hargaBulanLalu;
        $hargaChange   = $hargaTerbaru - $hargaBulanLalu;
        $hargaPercent  = $hargaBulanLalu > 0 ? ($hargaChange / $hargaBulanLalu) * 100 : 0;

        $last7 = PriceHistory::orderBy('date', 'desc')->take(7)->pluck('harga_sekarang')->toArray();
        if (count($last7) > 1) {
            $mean              = array_sum($last7) / count($last7);
            $variance          = array_sum(array_map(fn($x) => pow($x - $mean, 2), $last7)) / count($last7);
            $indexVolatilitas  = round(sqrt($variance) / $mean, 2);
            $statusVolatilitas = match(true) {
                $indexVolatilitas < 0.1 => 'Rendah',
                $indexVolatilitas < 0.3 => 'Sedang',
                default                 => 'Tinggi',
            };
        } else {
            $statusVolatilitas = 'Rendah';
            $indexVolatilitas  = 0;
        }

        // PIE CHART PDF
        try {
            $allData = PriceHistory::whereNotNull('category_id')
                ->orderBy('date', 'desc')
                ->limit(500)
                ->get(['category_id', 'commodity_name', 'harga_sekarang', 'date']);

            if ($allData->isEmpty()) {
                $chartLabels = [];
                $chartValues = [];
            } else {
                $categoryNames = Category::all()->keyBy(fn($c) => (string) $c->_id);

                $grouped = $allData
                    ->groupBy(fn($item) => (string) $item->category_id)
                    ->map(function ($items) {
                        $latestPerCommodity = $items
                            ->groupBy('commodity_name')
                            ->map(fn($rows) => $rows->sortByDesc('date')->first()->harga_sekarang);
                        return (int) round($latestPerCommodity->avg());
                    })
                    ->sortDesc();

                $chartLabels = array_values($grouped->keys()->map(
                    fn($id) => $categoryNames[$id]->name ?? 'Unknown'
                )->toArray());
                $chartValues = array_values($grouped->values()->map(fn($v) => (int) $v)->toArray());
            }
        } catch (\Exception $e) {
            Log::error('Pie Chart PDF Error: ' . $e->getMessage());
            $chartLabels = [];
            $chartValues = [];
        }

        $periodeLabel = $date
            ? Carbon::parse($date)->locale('id')->isoFormat('DD MMMM YYYY')
            : 'Semua Periode';

        $pdf = Pdf::loadView('user.pdf.laporan', compact(
            'recentPrices', 'namaKomoditas', 'hargaTerbaru',
            'hargaChange', 'hargaPercent', 'hargaKemarin',
            'statusVolatilitas', 'indexVolatilitas',
            'chartLabels', 'chartValues', 'periodeLabel'
        ))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont'     => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isPhpEnabled'    => true,
            'dpi'             => 150,
        ]);

        $filename = 'laporan-harga-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}