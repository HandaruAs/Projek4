<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Prediction;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function home(Request $request)
    {
        $user     = Auth::user();
        $search   = $request->get('search');
        $category = $request->get('category'); // nama kategori (string)
        $date     = $request->get('date');

        // ── Ambil semua prediksi terbaru per komoditas ──
        // Karena MongoDB, kita ambil semua dulu lalu group by commodity_name
        // untuk mendapatkan prediksi terbaru per komoditas
        $allPredictions = Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil prediksi terbaru per komoditas (deduplicate)
        $latestPerCommodity = $allPredictions
            ->groupBy('commodity_name')
            ->map(fn($group) => $group->first()) // sudah sorted desc, jadi first = terbaru
            ->values();

        // ── Terapkan filter ──
        if ($search) {
            $latestPerCommodity = $latestPerCommodity->filter(
                fn($p) => str_contains(strtolower($p->commodity_name), strtolower($search))
            )->values();
        }
    

        if ($category && $category !== 'Semua') {
            $latestPerCommodity = $latestPerCommodity->filter(
                fn($p) => strtolower($p->kategori) === strtolower($category)
            )->values();
        }

        if ($date) {
            $targetDate = Carbon::parse($date)->toDateString();
            $latestPerCommodity = $latestPerCommodity->filter(function ($p) use ($targetDate) {
                $createdAt = $p->created_at instanceof Carbon
                    ? $p->created_at->toDateString()
                    : Carbon::parse($p->created_at)->toDateString();
                return $createdAt === $targetDate;
            })->values();
        }

        // ── Hitung selisih harga (harga_terakhir vs forecast pertama) ──
        foreach ($latestPerCommodity as $item) {
            $hargaLama        = $item->current_price ?? 0;
            $forecastPertama  = $item->forecast[0] ?? $hargaLama;
            $item->selisih    = $forecastPertama - $hargaLama;
            $item->persen     = $hargaLama > 0 ? ($item->selisih / $hargaLama) * 100 : 0;
            // Tambah field virtual untuk Blade agar kolom "Harga" tetap bisa tampil
            $item->harga_sekarang = $hargaLama;
            $item->date           = $item->created_at;
        }

        $latestPerCommodity = $latestPerCommodity
            ->sortByDesc(fn($p) => $p->current_price ?? 0)
            ->values();

        // ── Manual pagination ──
        $perPage     = 10;
        $currentPage = (int) $request->get('page', 1);
        $totalKomoditas = PriceHistory::distinct('commodity_name')->count('commodity_name');
        $items       = $latestPerCommodity->forPage($currentPage, $perPage);

        $recentPrices = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $totalKomoditas,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ── Stat cards ──

        $rataRataHarga = (int) round(
            $latestPerCommodity->avg(fn($p) => $p->current_price ?? 0) ?? 0
        );

        $komoditasTertinggi     = $latestPerCommodity->first(); // sudah sortByDesc current_price
        $hargaTertinggi      = $komoditasTertinggi?->current_price ?? 0;
        $namaKomoditasTertinggi = $komoditasTertinggi?->commodity_name ?? '-';

      $totalKomoditas = Commodity::count();

        // ── Volatilitas: dari forecast 7 nilai pertama prediksi terbaru ──
      $last7 = [];
$komoditasTertinggi = $latestPerCommodity->first(); // sudah sortByDesc, first = harga tertinggi
if ($komoditasTertinggi) {
    $last7 = array_slice($komoditasTertinggi->forecast, 0, 7);
}

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

        // ── Category list untuk filter dropdown ──
        // Ambil dari kategori unik yang ada di data prediksi
        $categoryList = $allPredictions
            ->pluck('kategori')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn($name) => (object) ['_id' => $name, 'name' => $name]);

        // ── Pie Chart: rata-rata harga_terakhir per kategori ──
        try {
            $grouped = $allPredictions
                ->groupBy('commodity_name')
                ->map(fn($group) => $group->first()) // terbaru per komoditas
                ->values()
                ->filter(fn($p) => !empty($p->kategori) && ($p->current_price ?? 0) > 0)
                ->groupBy('kategori')
                ->map(fn($items) => (int) round($items->avg(fn($p) => $p->current_price ?? 0)))
                ->filter(fn($val) => $val > 0)
                ->sortDesc();

            $chartLabels = array_values($grouped->keys()->toArray());
            $chartValues = array_values($grouped->values()->map(fn($v) => (int) $v)->toArray());
        } catch (\Exception $e) {
            Log::error('Pie Chart Error: ' . $e->getMessage());
            $chartLabels = [];
            $chartValues = [];
        }

return view('user.home', compact(
    'user', 'recentPrices',
    'rataRataHarga', 'hargaTertinggi', 'namaKomoditasTertinggi', 'totalKomoditas',
    'statusVolatilitas', 'indexVolatilitas',
    'categoryList', 'chartLabels', 'chartValues'
));
    }

    public function downloadPdf(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category');
        $date     = $request->get('date');

        $allPredictions = Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $latestPerCommodity = $allPredictions
            ->groupBy('commodity_name')
            ->map(fn($group) => $group->first())
            ->values();

        if ($search) {
            $latestPerCommodity = $latestPerCommodity->filter(
                fn($p) => str_contains(strtolower($p->commodity_name), strtolower($search))
            )->values();
        }

        if ($category && $category !== 'Semua') {
            $latestPerCommodity = $latestPerCommodity->filter(
                fn($p) => strtolower($p->kategori) === strtolower($category)
            )->values();
        }

        if ($date) {
            $targetDate = Carbon::parse($date)->toDateString();
            $latestPerCommodity = $latestPerCommodity->filter(function ($p) use ($targetDate) {
                return Carbon::parse($p->created_at)->toDateString() === $targetDate;
            })->values();
        }

        // Siapkan field virtual untuk Blade PDF (sama dengan home())
        foreach ($latestPerCommodity as $item) {
            $hargaLama           = $item->current_price ?? 0;
            $forecastPertama     = $item->forecast[0] ?? $hargaLama;
            $item->selisih       = $forecastPertama - $hargaLama;
            $item->persen        = $hargaLama > 0 ? ($item->selisih / $hargaLama) * 100 : 0;
            $item->harga_sekarang = $hargaLama;
            $item->date           = $item->created_at;
        }

        $recentPrices = $latestPerCommodity->take(500);

        $adaFilter = $search || ($category && $category !== 'Semua') || $date;

        if ($adaFilter && $latestPerCommodity->isNotEmpty()) {
            $hargaTerbaruData = $latestPerCommodity->first();
            $hargaTerbaru     = $hargaTerbaruData->current_price ?? 0;
            $namaKomoditas    = $hargaTerbaruData->commodity_name;
        } else {
            $hargaTerbaru     = (int) round($latestPerCommodity->avg(fn($p) => $p->current_price ?? 0) ?? 0);
            $namaKomoditas    = 'Semua Komoditas';
            $hargaTerbaruData = $latestPerCommodity->first();
        }

        $hargaBulanLalu = $hargaTerbaru;
        if ($hargaTerbaruData) {
            $prediksiLama = Prediction::where('status', 'completed')
                ->where('commodity_name', $hargaTerbaruData->commodity_name)
                ->where('created_at', '<=', Carbon::now()->subMonth())
                ->orderBy('created_at', 'desc')
                ->first();
            if ($prediksiLama) {
                $hargaBulanLalu = $prediksiLama->current_price ?? $hargaTerbaru;
            }
        }

        $hargaKemarin = $hargaBulanLalu;
        $hargaChange  = $hargaTerbaru - $hargaBulanLalu;
        $hargaPercent = $hargaBulanLalu > 0 ? ($hargaChange / $hargaBulanLalu) * 100 : 0;

        $last7 = $hargaTerbaruData ? array_slice($hargaTerbaruData->forecast, 0, 7) : [];
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

        // Pie chart PDF
        try {
            $grouped = $allPredictions
                ->groupBy('commodity_name')
                ->map(fn($group) => $group->first())
                ->values()
                ->filter(fn($p) => !empty($p->kategori) && ($p->current_price ?? 0) > 0)
                ->groupBy('kategori')
                ->map(fn($items) => (int) round($items->avg(fn($p) => $p->current_price ?? 0)))
                ->filter(fn($val) => $val > 0)
                ->sortDesc();

            $chartLabels = array_values($grouped->keys()->toArray());
            $chartValues = array_values($grouped->values()->map(fn($v) => (int) $v)->toArray());
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

        $filename = 'laporan-prediksi-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}

