<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Prediction;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MongoDB\BSON\ObjectId;
use Carbon\Carbon;

class CommodityController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // HELPER: harga dinamis dari forecast (sama dengan UserController)
    // ─────────────────────────────────────────────────────────────
    private function getHargaForDate(
        array $forecast,
        array $tanggalPred,
        float $hargaTerakhir,
        Carbon $targetDate
    ): float {
        if (empty($forecast) || empty($tanggalPred)) return $hargaTerakhir;

        $targetStr = $targetDate->toDateString();
        $index     = array_search($targetStr, $tanggalPred);

        if ($index !== false && isset($forecast[$index])) {
            return (float) $forecast[$index];
        }
        if ($targetStr < $tanggalPred[0]) return $hargaTerakhir;

        $lastTanggal = end($tanggalPred);
        if ($targetStr > $lastTanggal) return (float) end($forecast);

        foreach ($tanggalPred as $i => $tgl) {
            if ($tgl >= $targetStr) return (float) ($forecast[$i] ?? $hargaTerakhir);
        }

        return $hargaTerakhir;
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: ambil prediction terbaru untuk suatu komoditas
    // ─────────────────────────────────────────────────────────────
    private function getLatestPrediction(string $commodityName): ?array
    {
        $pred = Prediction::where('status', 'completed')
            ->where('commodity_name', $commodityName)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$pred) return null;

        return $pred->payload ?? [];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/commodities
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Commodity::orderBy('name', 'asc');

        if ($request->has('category_id')) {
            $query->where('category', $request->category_id);
        }

        $commodities = $query->get();
        $today       = Carbon::today();

        $result = $commodities->map(function ($commodity) use ($today) {
            $payload = $this->getLatestPrediction($commodity->name);

            if ($payload) {
                $forecast    = array_map('floatval', $payload['forecast']     ?? []);
                $tanggalPred = $payload['tanggal_pred'] ?? [];
                $hargaAktual = (float) ($payload['harga_terakhir'] ?? 0);

                $tanggalMulai    = !empty($tanggalPred) ? Carbon::parse($tanggalPred[0]) : null;
                $tanggalAkhir    = !empty($tanggalPred) ? Carbon::parse(end($tanggalPred)) : null;
                $dalamRange      = $tanggalMulai && $tanggalAkhir
                    && $today->gte($tanggalMulai) && $today->lte($tanggalAkhir);
                $sudahKadaluarsa = $tanggalAkhir && $today->gt($tanggalAkhir);

                if ($dalamRange) {
                    $currentPrice = $this->getHargaForDate($forecast, $tanggalPred, $hargaAktual, $today);
                } elseif ($sudahKadaluarsa) {
                    $currentPrice = (float) (end($forecast) ?: $hargaAktual);
                } else {
                    $currentPrice = $hargaAktual;
                }

                // previousPrice = harga kemarin dari forecast
                $yesterday      = $today->copy()->subDay();
                $previousPrice  = $this->getHargaForDate($forecast, $tanggalPred, $hargaAktual, $yesterday);
            } else {
                // Fallback ke price_histories (TANPA hit Flask)
                $prices        = $commodity->priceHistories()->orderBy('date', 'desc')->limit(2)->get();
                $currentPrice  = $prices->first()?->harga_sekarang ?? 0;
                $previousPrice = $prices->skip(1)->first()?->harga_sekarang ?? 0;
            }

            $raw = $commodity->getAttributes();
            return [
                '_id'            => (string) $commodity->_id,
                'name'           => $raw['name']       ?? '',
                'category'       => $raw['category']   ?? '',
                'unit'           => $raw['unit']        ?? '',
                'stok_unit'      => $raw['stok_unit']   ?? '',
                'description'    => $raw['description'] ?? '',
                'current_price'  => round($currentPrice),
                'previous_price' => round($previousPrice),
            ];
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/commodities/{id}
    // FIX: Tidak lagi hit Flask langsung → pakai data Prediction di DB
    // ─────────────────────────────────────────────────────────────
    public function show(string $id)
    {
        try {
            $objectId = new ObjectId($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format ID tidak valid',
            ], 400);
        }

        $commodity = Commodity::where('_id', $objectId)->first();

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $today   = Carbon::today();
        $payload = $this->getLatestPrediction($commodity->name);

        if ($payload) {
            $forecast    = array_map('floatval', $payload['forecast']     ?? []);
            $tanggalPred = $payload['tanggal_pred'] ?? [];
            $hargaAktual = (float) ($payload['harga_terakhir'] ?? 0);

            $tanggalMulai    = !empty($tanggalPred) ? Carbon::parse($tanggalPred[0]) : null;
            $tanggalAkhir    = !empty($tanggalPred) ? Carbon::parse(end($tanggalPred)) : null;
            $dalamRange      = $tanggalMulai && $tanggalAkhir
                && $today->gte($tanggalMulai) && $today->lte($tanggalAkhir);
            $sudahKadaluarsa = $tanggalAkhir && $today->gt($tanggalAkhir);

            if ($dalamRange) {
                $currentPrice = $this->getHargaForDate($forecast, $tanggalPred, $hargaAktual, $today);
            } elseif ($sudahKadaluarsa) {
                $currentPrice = (float) (end($forecast) ?: $hargaAktual);
            } else {
                $currentPrice = $hargaAktual;
            }

            $yesterday     = $today->copy()->subDay();
            $previousPrice = $this->getHargaForDate($forecast, $tanggalPred, $hargaAktual, $yesterday);

        } else {
            // Fallback ke price_histories — TIDAK hit Flask
            $prices        = $commodity->priceHistories()->orderBy('date', 'desc')->limit(2)->get();
            $currentPrice  = $prices->first()?->harga_sekarang  ?? 0;
            $previousPrice = $prices->skip(1)->first()?->harga_sekarang ?? 0;
        }

        $raw = $commodity->getAttributes();

        return response()->json([
            'success' => true,
            'data'    => [
                '_id'            => (string) $commodity->_id,
                'name'           => $raw['name']       ?? '',
                'category'       => $raw['category']   ?? '',
                'unit'           => $raw['unit']        ?? '',
                'stok_unit'      => $raw['stok_unit']   ?? '',
                'description'    => $raw['description'] ?? '',
                'current_price'  => round($currentPrice),
                'previous_price' => round($previousPrice),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/commodities/{id}/forecast
    // Endpoint BARU: return data forecast lengkap untuk detail screen
    // ─────────────────────────────────────────────────────────────
    public function forecast(string $id)
    {
        try {
            $objectId = new ObjectId($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format ID tidak valid',
            ], 400);
        }

        $commodity = Commodity::where('_id', $objectId)->first();

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $pred = Prediction::where('status', 'completed')
            ->where('commodity_name', $commodity->name)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$pred) {
            return response()->json([
                'success'       => true,
                'has_forecast'  => false,
                'data'          => [],
                'message'       => 'Belum ada data prediksi untuk komoditas ini',
            ]);
        }

        $payload       = $pred->payload ?? [];
        $forecast      = array_map('floatval', $payload['forecast']     ?? []);
        $tanggalPred   = $payload['tanggal_pred'] ?? [];
        $hargaAktual   = (float) ($payload['harga_terakhir'] ?? 0);
        $today         = Carbon::today();

        // ── Range & status ───────────────────────────────────────
        $tanggalMulai    = !empty($tanggalPred) ? Carbon::parse($tanggalPred[0]) : null;
        $tanggalAkhir    = !empty($tanggalPred) ? Carbon::parse(end($tanggalPred)) : null;
        $dalamRange      = $tanggalMulai && $tanggalAkhir
            && $today->gte($tanggalMulai) && $today->lte($tanggalAkhir);
        $sudahKadaluarsa = $tanggalAkhir && $today->gt($tanggalAkhir);
        $belumMulai      = $tanggalMulai && $today->lt($tanggalMulai);

        // ── Harga hari ini (dinamis) ─────────────────────────────
        if ($dalamRange) {
            $hargaHariIni = $this->getHargaForDate($forecast, $tanggalPred, $hargaAktual, $today);
        } elseif ($sudahKadaluarsa) {
            $hargaHariIni = (float) (end($forecast) ?: $hargaAktual);
        } else {
            $hargaHariIni = $hargaAktual;
        }

        // ── Jumlah hari forecast yang tersedia ───────────────────
        $totalDays = count($forecast);

        // ── Tentukan periode yang tersedia (30 / 60 / 90) ────────
        $availablePeriods = [];
        if ($totalDays >= 30) $availablePeriods[] = 30;
        if ($totalDays >= 60) $availablePeriods[] = 60;
        if ($totalDays >= 90) $availablePeriods[] = 90;
        if (empty($availablePeriods) && $totalDays > 0) {
            $availablePeriods[] = $totalDays; // misal admin set 14 hari
        }

        // ── Build array forecast per hari ────────────────────────
        // Setiap item: { date, harga, isForecast }
        // Mulai dari tanggal_pred[0] atau hari ini
        $forecastItems = [];
        foreach ($tanggalPred as $i => $tgl) {
            if (!isset($forecast[$i])) break;
            $forecastItems[] = [
                'date'        => $tgl,
                'harga'       => round((float) $forecast[$i]),
                'is_forecast' => true,
            ];
        }

        // ── Harga historis (30 hari ke belakang) untuk grafik ────
        $histories = PriceHistory::where('commodity_id', (string) $commodity->_id)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->map(fn($h) => [
                'date'        => Carbon::parse($h->date)->toDateString(),
                'harga'       => (float) $h->harga_sekarang,
                'is_forecast' => false,
            ])
            ->sortBy('date')
            ->values()
            ->toArray();

        return response()->json([
            'success'          => true,
            'has_forecast'     => true,
            'commodity_name'   => $commodity->name,
            'satuan'           => $payload['satuan'] ?? $commodity->unit ?? 'kg',
            // Harga dinamis hari ini
            'harga_hari_ini'   => round($hargaHariIni),
            'harga_aktual'     => round($hargaAktual),
            // Status prediksi
            'status_prediksi'  => $dalamRange ? 'aktif' : ($sudahKadaluarsa ? 'kadaluarsa' : 'belum_mulai'),
            'dalam_range'      => $dalamRange,
            'sudah_kadaluarsa' => $sudahKadaluarsa,
            'belum_mulai'      => $belumMulai,
            'tanggal_mulai'    => $tanggalMulai?->toDateString(),
            'tanggal_akhir'    => $tanggalAkhir?->toDateString(),
            // Periode yang tersedia
            'total_forecast_days' => $totalDays,
            'available_periods'   => $availablePeriods,
            // Data forecast per hari
            'forecast'         => $forecastItems,
            // Data historis 30 hari terakhir
            'history'          => $histories,
            // Metadata model
            'accuracy'         => $payload['accuracy'] ?? null,
            'generated_at'     => $pred->created_at?->toDateString(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // store / update / destroy — tidak berubah
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:commodities,name',
            'category'    => 'required|string',
            'unit'        => 'required|string|max:50',
            'stok_unit'   => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $commodity = Commodity::create([
            'name'        => $request->name,
            'category'    => $request->category,
            'unit'        => $request->unit,
            'stok_unit'   => $request->stok_unit,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Komoditas berhasil ditambahkan',
            'data'    => $commodity,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $commodity = Commodity::find($id);

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255|unique:commodities,name,' . $id . ',_id',
            'category'    => 'sometimes|string',
            'unit'        => 'sometimes|string|max:50',
            'stok_unit'   => 'sometimes|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $commodity->update($request->only([
            'name', 'category', 'unit', 'stok_unit', 'description',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Komoditas berhasil diperbarui',
            'data'    => $commodity,
        ]);
    }

    public function destroy(string $id)
    {
        $commodity = Commodity::find($id);

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak bisa ditemukan',
            ], 404);
        }

        if ($commodity->priceHistories()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak bisa dihapus karena masih memiliki data harga',
            ], 409);
        }

        if ($commodity->predictions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak bisa dihapus karena masih memiliki data prediksi',
            ], 409);
        }

        $commodity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Komoditas berhasil dihapus',
        ]);
    }
}
