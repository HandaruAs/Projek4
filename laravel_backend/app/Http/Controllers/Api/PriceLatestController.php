<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\PriceHistory;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PriceLatestController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // HELPER: build map commodity_name => commodity _id
    // ─────────────────────────────────────────────────────────────
    private function buildCommodityIdMap(): \Illuminate\Support\Collection
    {
        return Commodity::all()->mapWithKeys(
            fn($c) => [strtolower(trim($c->name)) => (string) $c->_id]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: bangun map commodity_name => data dari Prediction
    // ─────────────────────────────────────────────────────────────
    private function buildPredictionMap(): \Illuminate\Support\Collection
    {
        // ✅ Build commodity ID map sekali untuk semua prediction
        $commodityIdMap = $this->buildCommodityIdMap();

        return Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->mapWithKeys(function ($pred) use ($commodityIdMap) {
                $forecast      = $pred->payload['forecast']     ?? [];
                $tanggal       = $pred->payload['tanggal_pred'] ?? [];
                $hargaTerakhir = (float) ($pred->payload['harga_terakhir'] ?? 0);

                // Ambil harga forecast tertinggi
                $maxHarga   = !empty($forecast) ? max($forecast) : 0;
                $maxIndex   = !empty($forecast) ? array_search($maxHarga, $forecast) : 0;
                $maxTanggal = $tanggal[$maxIndex] ?? null;

                $selisih = $maxHarga - $hargaTerakhir;
                $persen  = $hargaTerakhir > 0
                    ? round(($selisih / $hargaTerakhir) * 100, 2)
                    : 0;

                // ✅ Ambil commodity _id yang benar berdasarkan nama
                $commodityId = $commodityIdMap->get(
                    strtolower(trim($pred->commodity_name)), ''
                );

                return [
                    $pred->commodity_name => [
                        'commodity_id'   => $commodityId, // ✅ ID commodity, bukan ID prediction
                        'commodity_name' => $pred->commodity_name,
                        'category'       => $pred->payload['kategori'] ?? '',
                        'satuan'         => $pred->payload['satuan']   ?? 'kg',
                        'unit'           => $pred->payload['satuan']   ?? 'kg',
                        'date'           => $maxTanggal
                            ? Carbon::parse($maxTanggal)->toDateString()
                            : null,
                        'harga_sekarang' => (float) $maxHarga,
                        'harga_lama'     => $hargaTerakhir,
                        'selisih'        => (float) $selisih,
                        'persen'         => (float) $persen,
                        'is_prediction'  => true,
                    ],
                ];
            });
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/prices/latest
    // Gabungan PriceHistory + Prediction
    // Jika suatu komoditas punya prediction → pakai data prediction
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search   = $request->get('search');

        // ── 1. Ambil data history per komoditas ──────────────────
        $latest = PriceHistory::raw(function ($collection) use ($category, $search) {
            $match = [];

            if ($category && $category !== 'Semua') {
                $match['category'] = $category;
            }
            if ($search) {
                $match['commodity_name'] = ['$regex' => $search, '$options' => 'i'];
            }

            $pipeline = [];
            if (!empty($match)) {
                $pipeline[] = ['$match' => $match];
            }

            $pipeline = array_merge($pipeline, [
                ['$sort' => ['date' => -1]],
                [
                    '$group' => [
                        '_id'            => '$commodity_name',
                        'commodity_id'   => ['$first' => '$commodity_id'],
                        'commodity_name' => ['$first' => '$commodity_name'],
                        'category'       => ['$first' => '$category'],
                        'satuan'         => ['$first' => '$satuan'],
                        'harga_sekarang' => ['$first' => '$harga_sekarang'],
                        'harga_lama'     => ['$first' => '$harga_lama'],
                        'selisih'        => ['$first' => '$selisih'],
                        'persen'         => ['$first' => '$persen'],
                        'date'           => ['$first' => '$date'],
                    ],
                ],
                ['$sort' => ['commodity_name' => 1]],
            ]);

            return $collection->aggregate($pipeline);
        });

        // ── 2. Build prediction map ──────────────────────────────
        $predMap = $this->buildPredictionMap();

        // ── 3. Build commodity ID map untuk fallback history ─────
        $commodityIdMap = $this->buildCommodityIdMap();

        // ── 4. Merge: prediction override history jika ada ───────
        $result = collect($latest)->map(function ($item) use ($predMap, $commodityIdMap) {
            $name = $item['commodity_name'] ?? '';

            // Jika ada prediction untuk komoditas ini → pakai prediction
            if ($predMap->has($name)) {
                return $predMap->get($name);
            }

            // ✅ Fallback ke data history dengan commodity_id yang benar
            $commodityId = $commodityIdMap->get(strtolower(trim($name)), '');

            // Jika tidak ketemu di map, coba cast dari field history
            if (empty($commodityId) && isset($item['commodity_id'])) {
                $commodityId = (string) $item['commodity_id'];
            }

            return [
                'commodity_id'   => $commodityId,
                'commodity_name' => $name,
                'category'       => $item['category'] ?? '',
                'satuan'         => $item['satuan'] ?? '',
                'unit'           => $item['satuan'] ?? 'kg',
                'date'           => isset($item['date'])
                    ? Carbon::parse($item['date'])->toDateString()
                    : null,
                'harga_sekarang' => (float) ($item['harga_sekarang'] ?? 0),
                'harga_lama'     => (float) ($item['harga_lama'] ?? 0),
                'selisih'        => (float) ($item['selisih'] ?? 0),
                'persen'         => (float) ($item['persen'] ?? 0),
                'is_prediction'  => false,
            ];
        });

        // ── 5. Tambahkan komoditas prediction yang tidak ada di history ──
        $historyNames = collect($latest)->pluck('commodity_name');
        $predOnly = $predMap->filter(
            fn($_, $name) => !$historyNames->contains($name)
        )->values();

        // Apply filter manual untuk predOnly
        if ($category && $category !== 'Semua') {
            $predOnly = $predOnly->filter(
                fn($p) => strtolower($p['category']) === strtolower($category)
            );
        }
        if ($search) {
            $predOnly = $predOnly->filter(
                fn($p) => str_contains(strtolower($p['commodity_name']), strtolower($search))
            );
        }

        $merged = $result->concat($predOnly->values())
            ->sortBy('commodity_name')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $merged,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/prices/categories
    // ─────────────────────────────────────────────────────────────
    public function categories()
    {
        $categories = collect(
            PriceHistory::raw(fn($col) => $col->distinct('category', []))
        )->filter()->sort()->prepend('Semua')->values();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/prices/top?limit=3
    // ─────────────────────────────────────────────────────────────
    public function top(Request $request)
    {
        $limit = (int) $request->get('limit', 3);

        $predMap = $this->buildPredictionMap();

        // Jika ada prediction → pakai prediction untuk top
        if ($predMap->isNotEmpty()) {
            $result = $predMap->sortByDesc('harga_sekarang')
                ->take($limit)
                ->values();

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        }

        // Fallback ke PriceHistory jika belum ada prediction sama sekali
        $commodityIdMap = $this->buildCommodityIdMap();

        $top = PriceHistory::raw(function ($collection) use ($limit) {
            return $collection->aggregate([
                ['$sort' => ['date' => -1]],
                [
                    '$group' => [
                        '_id'            => '$commodity_name',
                        'commodity_id'   => ['$first' => '$commodity_id'],
                        'commodity_name' => ['$first' => '$commodity_name'],
                        'category'       => ['$first' => '$category'],
                        'satuan'         => ['$first' => '$satuan'],
                        'harga_sekarang' => ['$first' => '$harga_sekarang'],
                        'harga_lama'     => ['$first' => '$harga_lama'],
                        'selisih'        => ['$first' => '$selisih'],
                        'persen'         => ['$first' => '$persen'],
                    ],
                ],
                ['$sort' => ['harga_sekarang' => -1]],
                ['$limit' => $limit],
            ]);
        });

        $result = collect($top)->map(function ($item) use ($commodityIdMap) {
            $name = $item['commodity_name'] ?? '';

            // ✅ Ambil commodity _id yang benar
            $commodityId = $commodityIdMap->get(strtolower(trim($name)), '');
            if (empty($commodityId) && isset($item['commodity_id'])) {
                $commodityId = (string) $item['commodity_id'];
            }

            return [
                'commodity_id'   => $commodityId,
                'commodity_name' => $name,
                'category'       => $item['category'] ?? '',
                'satuan'         => $item['satuan'] ?? '',
                'unit'           => $item['satuan'] ?? 'kg',
                'harga_sekarang' => (float) ($item['harga_sekarang'] ?? 0),
                'harga_lama'     => (float) ($item['harga_lama'] ?? 0),
                'selisih'        => (float) ($item['selisih'] ?? 0),
                'persen'         => (float) ($item['persen'] ?? 0),
                'is_prediction'  => false,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
