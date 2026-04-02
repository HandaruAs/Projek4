<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceHistoryController extends Controller
{
    /**
     * Ambil data harga & stok dengan filter.
     * Dipakai untuk monitoring di dashboard Laravel & Flutter.
     *
     * GET /api/price-histories
     * GET /api/price-histories?commodity_id=xxx
     * GET /api/price-histories?commodity_id=xxx&start_date=2024-01-01&end_date=2024-12-31
     * Akses: semua role
     */
    public function index(Request $request)
    {
        $query = PriceHistory::with('commodity')->orderBy('date', 'desc');

        if ($request->has('commodity_id')) {
            $query->byCommodity($request->commodity_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Pagination — default 30 data per halaman
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

    /**
     * Ambil detail satu data harga.
     *
     * GET /api/price-histories/{id}
     * Akses: semua role
     */
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

    /**
     * Tambah satu data harga & stok.
     *
     * POST /api/price-histories
     * Akses: admin, petugas
     */
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

        // Pastikan commodity_id valid
        $commodity = Commodity::find($request->commodity_id);
        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        // Cegah duplikat data pada tanggal yang sama untuk komoditas yang sama
        $exists = PriceHistory::where('commodity_id', $request->commodity_id)
            ->whereDate('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga untuk komoditas ini pada tanggal tersebut sudah ada',
            ], 409);
        }

        $priceHistory = PriceHistory::create([
            'commodity_id'   => $request->commodity_id,
            'commodity_name' => $commodity->name,
            'date'           => $request->date,
            'price'          => $request->price,
            'stok'           => $request->stok,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data harga berhasil ditambahkan',
            'data'    => $priceHistory,
        ], 201);
    }

    /**
     * Import banyak data harga sekaligus (bulk insert).
     * Berguna saat admin upload dataset dari CSV.
     *
     * POST /api/price-histories/bulk
     * Body: { "data": [ { commodity_id, date, price, stok }, ... ] }
     * Akses: admin
     */
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

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($request->data as $index => $item) {
            $commodity = Commodity::find($item['commodity_id']);

            if (!$commodity) {
                $errors[] = "Baris {$index}: commodity_id '{$item['commodity_id']}' tidak ditemukan";
                $skipped++;
                continue;
            }

            // Skip jika sudah ada data pada tanggal yang sama
            $exists = PriceHistory::where('commodity_id', $item['commodity_id'])
                ->whereDate('date', $item['date'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            PriceHistory::create([
                'commodity_id'   => $item['commodity_id'],
                'commodity_name' => $commodity->name,
                'date'           => $item['date'],
                'price'          => $item['price'],
                'stok'           => $item['stok'],
            ]);

            $inserted++;
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

    /**
     * Update data harga & stok.
     *
     * PUT /api/price-histories/{id}
     * Akses: admin, petugas
     */
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

        // Cegah duplikat jika tanggal diubah
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

    /**
     * Hapus satu data harga.
     *
     * DELETE /api/price-histories/{id}
     * Akses: admin
     */
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

    /**
     * Ambil data khusus untuk keperluan training LSTM di Flask.
     * Data diurutkan ascending by date (wajib untuk time series).
     *
     * GET /api/price-histories/training-data?commodity_id=xxx
     * GET /api/price-histories/training-data?commodity_id=xxx&start_date=2023-01-01&end_date=2024-01-01
     * Akses: admin
     */
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

        // LSTM butuh minimal 30 data untuk training yang valid
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
}
