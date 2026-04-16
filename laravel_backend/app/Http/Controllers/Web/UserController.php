<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    private function checkUser()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        return $user;
    }

    public function home(Request $request)
    {
        $user = $this->checkUser();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // Filter parameters
        $search = $request->get('search');
        $category = $request->get('category');
        $date = $request->get('date');

        // Query recent prices
        $query = PriceHistory::query();

        if ($search) {
            $query->where('commodity_name', 'like', '%' . $search . '%');
        }

        if ($category && $category !== 'Semua') {
            $query->where('category', $category);
        }

        if ($date) {
            $query->where('date', '>=', Carbon::parse($date)->startOfDay())
                  ->where('date', '<=', Carbon::parse($date)->endOfDay());
        }

        $recentPrices = $query->orderBy('date', 'desc')->paginate(10)->appends(request()->query());

        // Hitung selisih dan persen untuk setiap item di tabel
        foreach ($recentPrices as $item) {
            $hargaKemarin = PriceHistory::where('commodity_name', $item->commodity_name)
                ->where('date', '>=', Carbon::parse($item->date)->subDay()->startOfDay())
                ->where('date', '<=', Carbon::parse($item->date)->subDay()->endOfDay())
                ->first();

            $hargaLama = $hargaKemarin ? $hargaKemarin->harga_sekarang : $item->harga_sekarang;
            $item->selisih = $item->harga_sekarang - $hargaLama;
            $item->persen = $hargaLama > 0 ? ($item->selisih / $hargaLama) * 100 : 0;
        }

        // Stat cards data — ikut filter yang aktif
        $statQuery = PriceHistory::query();
        if ($search) {
            $statQuery->where('commodity_name', 'like', '%' . $search . '%');
        }
        if ($category && $category !== 'Semua') {
            $statQuery->where('category', $category);
        }
        if ($date) {
            $statQuery->whereDate('date', $date);
        }

        $adaFilter = $search || ($category && $category !== 'Semua') || $date;

        // Harga terbaru
        if ($adaFilter) {
            $hargaTerbaruData = (clone $statQuery)->orderBy('date', 'desc')->first();
            $hargaTerbaru     = $hargaTerbaruData ? $hargaTerbaruData->harga_sekarang : 0;
            $namaKomoditas    = $hargaTerbaruData ? $hargaTerbaruData->commodity_name : '-';
        } else {
            // Tanpa filter — tampilkan rata-rata harga semua komoditas terbaru
            $hargaTerbaru  = (clone $statQuery)->orderBy('date', 'desc')->avg('harga_sekarang') ?? 0;
            $hargaTerbaru  = round($hargaTerbaru);
            $namaKomoditas = 'Semua Komoditas';
            $hargaTerbaruData = (clone $statQuery)->orderBy('date', 'desc')->first(); // untuk referensi tanggal
        }

        // Perubahan bulanan
        if ($adaFilter && isset($hargaTerbaruData)) {
            // Filter aktif — bandingkan komoditas spesifik vs sebulan lalu
            $bulanLalu = Carbon::parse($hargaTerbaruData->date)->subMonth()->endOfDay();
            $hargaBulanLaluData = PriceHistory::where('commodity_name', $hargaTerbaruData->commodity_name)
                ->where('date', '<=', $bulanLalu)
                ->orderBy('date', 'desc')
                ->first();
            $hargaBulanLalu = $hargaBulanLaluData ? $hargaBulanLaluData->harga_sekarang : $hargaTerbaru;
        } else {
            // Tanpa filter — rata-rata harga semua komoditas sebulan lalu
            $bulanLalu    = Carbon::now()->subMonth()->endOfDay();
            $hargaBulanLalu = PriceHistory::where('date', '<=', $bulanLalu)
                ->avg('harga_sekarang') ?? $hargaTerbaru;
            $hargaBulanLalu = round($hargaBulanLalu);
        }

        $hargaKemarin = $hargaBulanLalu;
        $hargaChange  = $hargaTerbaru - $hargaBulanLalu;
        $hargaPercent = $hargaBulanLalu > 0 ? ($hargaChange / $hargaBulanLalu) * 100 : 0;

        // Volatilitas 7 hari — dari komoditas/filter yang aktif
        $last7Days = (clone $statQuery)->orderBy('date', 'desc')->take(7)->pluck('harga_sekarang')->toArray();

        if (count($last7Days) > 1) {
            $mean      = array_sum($last7Days) / count($last7Days);
            $variance  = array_sum(array_map(fn($x) => pow($x - $mean, 2), $last7Days)) / count($last7Days);
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

        // Data untuk filter category
        $categoryList = PriceHistory::distinct('category')->pluck('category')->filter()->sort()->values();

        // Data pie chart — rata-rata harga terbaru per kategori
        $chartData = PriceHistory::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$sort' => ['date' => -1]
                ],
                [
                    '$group' => [
                        '_id'        => '$category',
                        'rata_harga' => ['$avg' => '$harga_sekarang'],
                        'jumlah'     => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => ['_id' => ['$ne' => null]]
                ],
                [
                    '$sort' => ['rata_harga' => -1]
                ],
            ]);
        });

        $chartLabels = collect($chartData)->pluck('_id')->values();
        $chartValues = collect($chartData)->map(fn($d) => round($d['rata_harga']))->values();

        return view('user.home', compact(
            'user',
            'recentPrices',
            'namaKomoditas',
            'hargaTerbaru',
            'hargaChange',
            'hargaPercent',
            'hargaKemarin',
            'statusVolatilitas',
            'indexVolatilitas',
            'categoryList',
            'chartLabels',
            'chartValues'
        ));
    }
}
