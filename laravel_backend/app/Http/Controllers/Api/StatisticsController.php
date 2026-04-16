<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index()
    {
        // Ambil data harga terbaru per komoditas (7 hari terakhir)
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Total komoditas
        $totalKomoditas = Commodity::count();

        // Komoditas harga naik (persen > 0)
        $naikCount = PriceHistory::where('persen', '>', 0)
            ->where('date', '>=', $sevenDaysAgo)
            ->distinct('commodity_id')
            ->count('commodity_id');

        // Komoditas harga turun (persen < 0)
        $turunCount = PriceHistory::where('persen', '<', 0)
            ->where('date', '>=', $sevenDaysAgo)
            ->distinct('commodity_id')
            ->count('commodity_id');

        // Top 5 naik tertinggi minggu ini
        $topNaik = PriceHistory::where('persen', '>', 0)
            ->where('date', '>=', $sevenDaysAgo)
            ->orderBy('persen', 'desc')
            ->limit(5)
            ->get(['commodity_name', 'category', 'harga_sekarang', 'persen', 'selisih'])
            ->map(fn($item) => [
                'commodity_name' => $item->commodity_name,
                'category'       => $item->category,
                'harga_sekarang' => $item->harga_sekarang,
                'persen'         => round($item->persen, 2),
                'selisih'        => $item->selisih,
            ]);

        // Top 5 turun terbanyak minggu ini
        $topTurun = PriceHistory::where('persen', '<', 0)
            ->where('date', '>=', $sevenDaysAgo)
            ->orderBy('persen', 'asc')
            ->limit(5)
            ->get(['commodity_name', 'category', 'harga_sekarang', 'persen', 'selisih'])
            ->map(fn($item) => [
                'commodity_name' => $item->commodity_name,
                'category'       => $item->category,
                'harga_sekarang' => $item->harga_sekarang,
                'persen'         => round($item->persen, 2),
                'selisih'        => $item->selisih,
            ]);

        // Rata-rata harga per kategori (dari data terbaru)
        $latestDate = PriceHistory::orderBy('date', 'desc')->value('date');

        $perKategori = PriceHistory::where('date', '>=', Carbon::parse($latestDate)->startOfDay())
            ->where('date', '<=', Carbon::parse($latestDate)->endOfDay())
            ->get(['category', 'harga_sekarang'])
            ->groupBy('category')
            ->map(fn($items, $cat) => [
                'category'       => $cat,
                'rata_rata'      => round($items->avg('harga_sekarang')),
                'jumlah'         => $items->count(),
            ])
            ->values()
            ->sortByDesc('rata_rata')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'ringkasan' => [
                    'total_komoditas' => $totalKomoditas,
                    'naik'            => $naikCount,
                    'turun'           => $turunCount,
                ],
                'top_naik'     => $topNaik,
                'top_turun'    => $topTurun,
                'per_kategori' => $perKategori,
            ],
        ]);
    }
}
