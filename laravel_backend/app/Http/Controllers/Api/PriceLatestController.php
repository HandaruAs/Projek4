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
    // HELPER: commodity_name => commodity _id
    // ─────────────────────────────────────────────────────────────
    private function buildCommodityIdMap(): \Illuminate\Support\Collection
    {
        return Commodity::all()->mapWithKeys(
            fn($c) => [strtolower(trim($c->name)) => (string) $c->_id]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: ambil harga forecast untuk tanggal tertentu
    // Identik dengan UserController::getHargaForDate()
    // ─────────────────────────────────────────────────────────────
    private function getHargaForDate(
        array $forecast,
        array $tanggalPred,
        float $hargaTerakhir,
        Carbon $targetDate
    ): float {
        if (empty($forecast) || empty($tanggalPred)) {
            return $hargaTerakhir;
        }

        $targetStr = $targetDate->toDateString();

        // Exact match
        $index = array_search($targetStr, $tanggalPred);
        if ($index !== false && isset($forecast[$index])) {
            return (float) $forecast[$index];
        }

        // Sebelum range → harga aktual
        if ($targetStr < $tanggalPred[0]) {
            return $hargaTerakhir;
        }

        // Setelah range → forecast terakhir
        $lastTanggal = end($tanggalPred);
        if ($targetStr > $lastTanggal) {
            return (float) end($forecast);
        }

        // Tanggal terdekat
        foreach ($tanggalPred as $i => $tgl) {
            if ($tgl >= $targetStr) {
                return (float) ($forecast[$i] ?? $hargaTerakhir);
            }
        }

        return $hargaTerakhir;
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: build prediction map dengan harga DINAMIS per hari
    // Logika identik dengan UserController::buildPrediksiItem()
    // ─────────────────────────────────────────────────────────────
    private function buildPredictionMap(): \Illuminate\Support\Collection
    {
        $commodityIdMap = $this->buildCommodityIdMap();
        $today          = Carbon::today();

        return Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->mapWithKeys(function ($pred) use ($commodityIdMap, $today) {
                $payload       = $pred->payload ?? [];
                $forecast      = $payload['forecast']         ?? [];
                $tanggalPred   = $payload['tanggal_pred']     ?? [];
                $hargaTerakhir = (float) ($payload['harga_terakhir'] ?? 0);
                $kategori      = $payload['kategori']         ?? '';
                $satuan        = $payload['satuan']           ?? 'kg';

                // ── Range prediksi ──────────────────────────────
                $tanggalArr   = (array) $tanggalPred;
                $tanggalMulai = !empty($tanggalArr)
                    ? Carbon::parse($tanggalArr[0])
                    : null;
                $tanggalAkhir = !empty($tanggalArr)
                    ? Carbon::parse(end($tanggalArr))
                    : null;

                $dalamRange      = $tanggalMulai && $tanggalAkhir
                    && $today->gte($tanggalMulai)
                    && $today->lte($tanggalAkhir);
                $sudahKadaluarsa = $tanggalAkhir && $today->gt($tanggalAkhir);
                $belumMulai      = $tanggalMulai && $today->lt($tanggalMulai);

                // ── Harga dinamis sesuai kondisi hari ini ───────
                if ($dalamRange) {
                    // Hari ini ada di range → ambil forecast hari ini
                    $hargaHariIni = $this->getHargaForDate(
                        array_map('floatval', $forecast),
                        $tanggalArr,
                        $hargaTerakhir,
                        $today
                    );
                    $tanggalHarga  = $today->toDateString();
                    $statusPred    = 'aktif';

                } elseif ($sudahKadaluarsa) {
                    // Prediksi habis → pakai forecast terakhir
                    $lastForecast = end((array) $forecast);
                    $hargaHariIni = (float) ($lastForecast ?: $hargaTerakhir);
                    $tanggalHarga  = $tanggalAkhir->toDateString();
                    $statusPred    = 'kadaluarsa';

                } else {
                    // Belum mulai → pakai harga aktual terakhir
                    $hargaHariIni  = $hargaTerakhir;
                    $tanggalHarga  = $payload['tanggal_terakhir'] ?? null;
                    $statusPred    = 'belum_mulai';
                }

                // Selisih vs harga aktual terakhir (sama seperti web)
                $selisih = $hargaHariIni - $hargaTerakhir;
                $persen  = $hargaTerakhir > 0
                    ? round(($selisih / $hargaTerakhir) * 100, 2)
                    : 0;

                $commodityId = $commodityIdMap->get(
                    strtolower(trim($pred->commodity_name)), ''
                );

                return [
                    $pred->commodity_name => [
                        'commodity_id'     => $commodityId,
                        'commodity_name'   => $pred->commodity_name,
                        'category'         => $kategori,
                        'satuan'           => $satuan,
                        'unit'             => $satuan,
                        'date'             => $tanggalHarga,
                        // ✅ harga dinamis sesuai hari ini
                        'harga_sekarang'   => round($hargaHariIni),
                        // harga aktual terakhir (untuk referensi selisih)
                        'harga_lama'       => $hargaTerakhir,
                        'selisih'          => round($selisih),
                        'persen'           => (float) $persen,
                        // ── Field status prediksi untuk Flutter ──
                        'is_prediction'    => true,
                        'status_prediksi'  => $statusPred,
                        'dalam_range'      => $dalamRange,
                        'sudah_kadaluarsa' => $sudahKadaluarsa,
                        'belum_mulai'      => $belumMulai,
                        'tanggal_mulai'    => $tanggalMulai?->toDateString(),
                        'tanggal_akhir'    => $tanggalAkhir?->toDateString(),
                        // forecast untuk keperluan detail screen
                        'forecast'         => array_map(
                            'floatval', array_slice($forecast, 0, 30)
                        ),
                        'tanggal_pred'     => array_slice($tanggalArr, 0, 30),
                    ],
                ];
            });
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/prices/latest
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search   = $request->get('search');

        // ── 1. Harga history per komoditas ───────────────────────
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
                ['$group' => [
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
                ]],
                ['$sort' => ['commodity_name' => 1]],
            ]);

            return $collection->aggregate($pipeline);
        });

        // ── 2. Build maps ────────────────────────────────────────
        $predMap        = $this->buildPredictionMap();
        $commodityIdMap = $this->buildCommodityIdMap();

        // ── 3. Merge: prediction override history ────────────────
        $result = collect($latest)->map(function ($item) use ($predMap, $commodityIdMap) {
            $name = $item['commodity_name'] ?? '';

            if ($predMap->has($name)) {
                return $predMap->get($name);
            }

            $commodityId = $commodityIdMap->get(strtolower(trim($name)), '');
            if (empty($commodityId) && isset($item['commodity_id'])) {
                $commodityId = (string) $item['commodity_id'];
            }

            return [
                'commodity_id'     => $commodityId,
                'commodity_name'   => $name,
                'category'         => $item['category'] ?? '',
                'satuan'           => $item['satuan']   ?? '',
                'unit'             => $item['satuan']   ?? 'kg',
                'date'             => isset($item['date'])
                    ? Carbon::parse($item['date'])->toDateString()
                    : null,
                'harga_sekarang'   => (float) ($item['harga_sekarang'] ?? 0),
                'harga_lama'       => (float) ($item['harga_lama']     ?? 0),
                'selisih'          => (float) ($item['selisih']        ?? 0),
                'persen'           => (float) ($item['persen']         ?? 0),
                'is_prediction'    => false,
                'status_prediksi'  => 'tidak_ada',
                'dalam_range'      => false,
                'sudah_kadaluarsa' => false,
                'belum_mulai'      => false,
                'tanggal_mulai'    => null,
                'tanggal_akhir'    => null,
                'forecast'         => [],
                'tanggal_pred'     => [],
            ];
        });

        // ── 4. Prediksi yang tidak ada di history ────────────────
        $historyNames = collect($latest)->pluck('commodity_name');
        $predOnly     = $predMap->filter(
            fn($_, $name) => !$historyNames->contains($name)
        )->values();

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

        return response()->json(['success' => true, 'data' => $merged]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/prices/categories
    // ─────────────────────────────────────────────────────────────
    public function categories()
    {
        $categories = collect(
            PriceHistory::raw(fn($col) => $col->distinct('category', []))
        )->filter()->sort()->prepend('Semua')->values();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/prices/top?limit=3
    // Harga top juga pakai logika dinamis
    // ─────────────────────────────────────────────────────────────
    public function top(Request $request)
    {
        $limit   = (int) $request->get('limit', 3);
        $predMap = $this->buildPredictionMap();

        if ($predMap->isNotEmpty()) {
            $result = $predMap
                ->sortByDesc('harga_sekarang')
                ->take($limit)
                ->values();

            return response()->json(['success' => true, 'data' => $result]);
        }

        // Fallback ke PriceHistory
        $commodityIdMap = $this->buildCommodityIdMap();
        $top = PriceHistory::raw(function ($collection) use ($limit) {
            return $collection->aggregate([
                ['$sort' => ['date' => -1]],
                ['$group' => [
                    '_id'            => '$commodity_name',
                    'commodity_id'   => ['$first' => '$commodity_id'],
                    'commodity_name' => ['$first' => '$commodity_name'],
                    'category'       => ['$first' => '$category'],
                    'satuan'         => ['$first' => '$satuan'],
                    'harga_sekarang' => ['$first' => '$harga_sekarang'],
                    'harga_lama'     => ['$first' => '$harga_lama'],
                    'selisih'        => ['$first' => '$selisih'],
                    'persen'         => ['$first' => '$persen'],
                ]],
                ['$sort' => ['harga_sekarang' => -1]],
                ['$limit' => $limit],
            ]);
        });

        $result = collect($top)->map(function ($item) use ($commodityIdMap) {
            $name        = $item['commodity_name'] ?? '';
            $commodityId = $commodityIdMap->get(strtolower(trim($name)), '');
            if (empty($commodityId) && isset($item['commodity_id'])) {
                $commodityId = (string) $item['commodity_id'];
            }
            return [
                'commodity_id'     => $commodityId,
                'commodity_name'   => $name,
                'category'         => $item['category'] ?? '',
                'satuan'           => $item['satuan']   ?? '',
                'unit'             => $item['satuan']   ?? 'kg',
                'harga_sekarang'   => (float) ($item['harga_sekarang'] ?? 0),
                'harga_lama'       => (float) ($item['harga_lama']     ?? 0),
                'selisih'          => (float) ($item['selisih']        ?? 0),
                'persen'           => (float) ($item['persen']         ?? 0),
                'is_prediction'    => false,
                'status_prediksi'  => 'tidak_ada',
                'dalam_range'      => false,
                'sudah_kadaluarsa' => false,
                'belum_mulai'      => false,
                'tanggal_mulai'    => null,
                'tanggal_akhir'    => null,
                'forecast'         => [],
                'tanggal_pred'     => [],
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $result]);
    }
}
