<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceHistoryController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // HELPER: Buat notifikasi harga baru ke semua user (broadcast)
    // ══════════════════════════════════════════════════════════

    private function createPriceNotification(
        string $commodityName,
        float $newPrice,
        ?float $oldPrice
    ): void {
        // Tentukan naik/turun/baru
        if ($oldPrice === null) {
            $title = "Harga {$commodityName} Tersedia";
            $body  = "Data harga {$commodityName} baru telah ditambahkan oleh admin.";
        } else {
            $diff    = $newPrice - $oldPrice;
            $pct     = $oldPrice > 0 ? abs($diff / $oldPrice * 100) : 0;
            $pctStr  = number_format($pct, 1);
            $arah    = $diff > 0 ? 'naik' : 'turun';
            $title   = "Harga {$commodityName} " . ucfirst($arah) . "!";
            $body    = "Harga {$commodityName} {$arah} {$pctStr}% "
                     . "dari Rp " . number_format($oldPrice, 0, ',', '.')
                     . " menjadi Rp " . number_format($newPrice, 0, ',', '.') . ".";
        }

        Notification::create([
            'user_id'    => null,   // null = broadcast ke semua user
            'title'      => $title,
            'body'       => $body,
            'type'       => 'price_alert',
            'commodity'  => $commodityName,
            'meta'       => ['tabIndex' => 0],
            'is_read_by' => [],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/price-histories
    // ══════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = PriceHistory::with('commodity')->orderBy('date', 'desc');

        if ($request->has('commodity_id')) {
            $query->byCommodity($request->commodity_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $data = $query->paginate($request->get('per_page', 30));

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/price-histories/{id}
    // ══════════════════════════════════════════════════════════

    public function show(string $id)
    {
        $priceHistory = PriceHistory::with('commodity')->find($id);

        if (!$priceHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $priceHistory,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /api/price-histories
    // ══════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commodity_id' => 'required|string',
            'date'         => 'required|date',
            'price'        => 'required|numeric|min:0',
            'stok'         => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $commodity = Commodity::find($request->commodity_id);
        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $exists = PriceHistory::where('commodity_id', $request->commodity_id)
            ->whereDate('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga untuk komoditas ini pada tanggal tersebut sudah ada',
            ], 409);
        }

        // Ambil harga terakhir sebelum insert (untuk perbandingan notifikasi)
        $lastPrice = PriceHistory::where('commodity_id', $request->commodity_id)
            ->orderBy('date', 'desc')
            ->value('price');

        $priceHistory = PriceHistory::create([
            'commodity_id'   => $request->commodity_id,
            'commodity_name' => $commodity->name,
            'date'           => $request->date,
            'price'          => $request->price,
            'stok'           => $request->stok,
        ]);

        // Kirim notifikasi harga baru
        $this->createPriceNotification(
            $commodity->name,
            (float) $request->price,
            $lastPrice !== null ? (float) $lastPrice : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Data harga berhasil ditambahkan',
            'data'    => $priceHistory,
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // POST /api/price-histories/bulk
    // ══════════════════════════════════════════════════════════

    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data'                => 'required|array|min:1',
            'data.*.commodity_id' => 'required|string',
            'data.*.date'         => 'required|date',
            'data.*.price'        => 'required|numeric|min:0',
            'data.*.stok'         => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $inserted          = 0;
        $skipped           = 0;
        $errors            = [];
        $notifiedCommodity = []; // Hindari notif duplikat per komoditas dalam satu bulk

        foreach ($request->data as $index => $item) {
            $commodity = Commodity::find($item['commodity_id']);

            if (!$commodity) {
                $errors[] = "Baris {$index}: commodity_id '{$item['commodity_id']}' tidak ditemukan";
                $skipped++;
                continue;
            }

            $exists = PriceHistory::where('commodity_id', $item['commodity_id'])
                ->whereDate('date', $item['date'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Ambil harga terakhir sebelum insert (hanya sekali per komoditas)
            $lastPrice = null;
            if (!isset($notifiedCommodity[$item['commodity_id']])) {
                $lastPrice = PriceHistory::where('commodity_id', $item['commodity_id'])
                    ->orderBy('date', 'desc')
                    ->value('price');
            }

            PriceHistory::create([
                'commodity_id'   => $item['commodity_id'],
                'commodity_name' => $commodity->name,
                'date'           => $item['date'],
                'price'          => $item['price'],
                'stok'           => $item['stok'],
            ]);

            $inserted++;

            // Kirim notifikasi hanya sekali per komoditas dalam satu bulk
            if (!isset($notifiedCommodity[$item['commodity_id']])) {
                $this->createPriceNotification(
                    $commodity->name,
                    (float) $item['price'],
                    $lastPrice !== null ? (float) $lastPrice : null
                );
                $notifiedCommodity[$item['commodity_id']] = true;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$inserted} data berhasil diimport, {$skipped} data dilewati",
            'data'    => [
                'inserted' => $inserted,
                'skipped'  => $skipped,
                'errors'   => $errors,
            ],
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // PUT /api/price-histories/{id}
    // ══════════════════════════════════════════════════════════

    public function update(Request $request, string $id)
    {
        $priceHistory = PriceHistory::find($id);

        if (!$priceHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date'  => 'sometimes|date',
            'price' => 'sometimes|numeric|min:0',
            'stok'  => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($request->has('date') && $request->date !== $priceHistory->date->toDateString()) {
            $exists = PriceHistory::where('commodity_id', $priceHistory->commodity_id)
                ->whereDate('date', $request->date)
                ->where('_id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data harga untuk komoditas ini pada tanggal tersebut sudah ada',
                ], 409);
            }
        }

        $priceHistory->update($request->only(['date', 'price', 'stok']));

        return response()->json([
            'success' => true,
            'message' => 'Data harga berhasil diperbarui',
            'data'    => $priceHistory,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // DELETE /api/price-histories/{id}
    // ══════════════════════════════════════════════════════════

    public function destroy(string $id)
    {
        $priceHistory = PriceHistory::find($id);

        if (!$priceHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga tidak ditemukan',
            ], 404);
        }

        $priceHistory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data harga berhasil dihapus',
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/price-histories/training-data
    // ══════════════════════════════════════════════════════════

    public function trainingData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commodity_id' => 'required|string',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $commodity = Commodity::find($request->commodity_id);
        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $query = PriceHistory::forTraining($request->commodity_id);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $data = $query->get();

        if ($data->count() < 30) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak cukup untuk training, minimal 30 data diperlukan',
                'data'    => ['count' => $data->count()],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'commodity'  => [
                    'id'        => $commodity->id,
                    'name'      => $commodity->name,
                    'unit'      => $commodity->unit,
                    'stok_unit' => $commodity->stok_unit,
                ],
                'total_rows' => $data->count(),
                'records'    => $data,
            ],
        ]);
    }

    // GET /api/price-histories/flask/{commodityName}
    public function fromFlask(string $commodityName, Request $request)
    {
        $period  = $request->get('period', '30days');
        $steps   = match ($period) {
            '7days'   => 7,
            '3months' => 90,
            default   => 30,
        };

        try {
            $prediksiService = app(\App\Services\PrediksiService::class);
            $flaskData       = $prediksiService->generate(strtoupper($commodityName), $steps);

            $forecast  = $flaskData['forecast']      ?? [];
            $tanggals  = $flaskData['tanggal_pred']  ?? [];
            $hargaKini = $flaskData['harga_terakhir'] ?? 0;

            // Bentuk data seperti format priceHistories biasa
            $result = [];
            foreach ($forecast as $i => $harga) {
                $result[] = [
                    'date'           => $tanggals[$i] ?? now()->addDays($i)->toDateString(),
                    'harga_sekarang' => (float) $harga,
                    'harga_kemarin'  => $i === 0 ? (float) $hargaKini : (float) $forecast[$i - 1],
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal ambil data dari Flask: ' . $e->getMessage(),
            ], 500);
        }
    }
}
