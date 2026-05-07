<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use App\Models\Prediction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // HELPER: bangun data statistik per komoditas dari Prediction
    // ─────────────────────────────────────────────────────────────
    private function buildPredictionStats(): \Illuminate\Support\Collection
    {
        return Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->map(function ($pred) {
                $forecast      = $pred->payload['forecast']     ?? [];
                $hargaTerakhir = (float) ($pred->payload['harga_terakhir'] ?? 0);

                $maxHarga = !empty($forecast) ? max($forecast) : 0;
                $selisih  = $maxHarga - $hargaTerakhir;
                $persen   = $hargaTerakhir > 0
                    ? round(($selisih / $hargaTerakhir) * 100, 2)
                    : 0;

                return [
                    'commodity_name' => $pred->commodity_name,
                    'category'       => $pred->payload['kategori'] ?? '',
                    'harga_sekarang' => $maxHarga,
                    'harga_lama'     => $hargaTerakhir,
                    'selisih'        => $selisih,
                    'persen'         => $persen,
                ];
            })
            ->values();
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: rata-rata per kategori dari harga terbaru per komoditas
    // Menggunakan aggregation MongoDB agar semua kategori masuk
    // ─────────────────────────────────────────────────────────────
    private function buildPerKategori(): \Illuminate\Support\Collection
    {
        $raw = PriceHistory::raw(function ($collection) {
            return $collection->aggregate([
                // 1. Urutkan by date desc agar $first = terbaru
                ['$sort' => ['date' => -1]],

                // 2. Ambil harga terbaru per komoditas
                [
                    '$group' => [
                        '_id'            => '$commodity_name',
                        'category'       => ['$first' => '$category'],
                        'harga_sekarang' => ['$first' => '$harga_sekarang'],
                    ],
                ],

                // 3. Rata-rata per kategori
                [
                    '$group' => [
                        '_id'       => '$category',
                        'rata_rata' => ['$avg' => '$harga_sekarang'],
                        'jumlah'    => ['$sum' => 1],
                    ],
                ],

                ['$sort' => ['rata_rata' => -1]],
            ]);
        });

        return collect($raw)->map(fn($item) => [
            'category'  => $item['_id'] ?? '-',
            'rata_rata' => round($item['rata_rata']),
            'jumlah'    => $item['jumlah'],
        ])->values();
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/statistics
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $totalKomoditas = Commodity::count();
        $hasPrediction  = Prediction::where('status', 'completed')->exists();

        // Rata-rata per kategori selalu dari history (data paling lengkap)
        // tapi harga komoditas yang punya prediction di-override
        $perKategoriBase = $this->buildPerKategori();

        if ($hasPrediction) {
            // ════════════════════════════════════════════════════
            // STATISTIK DARI PREDICTION
            // ════════════════════════════════════════════════════
            $predStats = $this->buildPredictionStats();

            $naikCount  = $predStats->where('persen', '>', 0)->count();
            $turunCount = $predStats->where('persen', '<', 0)->count();

            $topNaik = $predStats
                ->where('persen', '>', 0)
                ->sortByDesc('persen')
                ->take(5)
                ->map(fn($item) => [
                    'commodity_name' => $item['commodity_name'],
                    'category'       => $item['category'],
                    'harga_sekarang' => $item['harga_sekarang'],
                    'persen'         => $item['persen'],
                    'selisih'        => $item['selisih'],
                ])
                ->values();

            $topTurun = $predStats
                ->where('persen', '<', 0)
                ->sortBy('persen')
                ->take(5)
                ->map(fn($item) => [
                    'commodity_name' => $item['commodity_name'],
                    'category'       => $item['category'],
                    'harga_sekarang' => $item['harga_sekarang'],
                    'persen'         => $item['persen'],
                    'selisih'        => $item['selisih'],
                ])
                ->values();

            // Override rata-rata kategori dengan harga prediction
            $predMap = $predStats->keyBy('commodity_name');

            // Ambil semua history terbaru per komoditas
            $historyItems = PriceHistory::raw(function ($collection) {
                return $collection->aggregate([
                    ['$sort' => ['date' => -1]],
                    [
                        '$group' => [
                            '_id'            => '$commodity_name',
                            'category'       => ['$first' => '$category'],
                            'harga_sekarang' => ['$first' => '$harga_sekarang'],
                        ],
                    ],
                ]);
            });

            $perKategori = collect($historyItems)
                ->map(function ($item) use ($predMap) {
                    $name  = $item['_id'] ?? '';
                    $harga = $predMap->has($name)
                        ? $predMap->get($name)['harga_sekarang']
                        : (float) ($item['harga_sekarang'] ?? 0);

                    return [
                        'category'       => $item['category'] ?? '-',
                        'harga_sekarang' => $harga,
                    ];
                })
                ->groupBy('category')
                ->map(fn($items, $cat) => [
                    'category'  => $cat,
                    'rata_rata' => round($items->avg('harga_sekarang')),
                    'jumlah'    => $items->count(),
                ])
                ->sortByDesc('rata_rata')
                ->values();

        } else {
            // ════════════════════════════════════════════════════
            // FALLBACK: STATISTIK DARI PRICE HISTORY
            // ════════════════════════════════════════════════════
            $sevenDaysAgo = Carbon::now()->subDays(7);

            $naikCount = PriceHistory::where('persen', '>', 0)
                ->where('date', '>=', $sevenDaysAgo)
                ->distinct('commodity_id')
                ->count('commodity_id');

            $turunCount = PriceHistory::where('persen', '<', 0)
                ->where('date', '>=', $sevenDaysAgo)
                ->distinct('commodity_id')
                ->count('commodity_id');

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

            // Fallback perKategori: semua kategori dari history
            $perKategori = $perKategoriBase;
        }

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
