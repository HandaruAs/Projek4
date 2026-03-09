<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommodityController extends Controller
{
    /**
     * Ambil semua komoditas.
     * Bisa difilter by category_id via query param.
     *
     * GET /api/commodities
     * GET /api/commodities?category_id=xxx
     * Akses: semua role
     */
    public function index(Request $request)
    {
        $query = Commodity::with('category')->orderBy('name', 'asc');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $commodities = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $commodities,
        ]);
    }

    /**
     * Ambil detail satu komoditas beserta histori harga terbaru (30 data).
     *
     * GET /api/commodities/{id}
     * Akses: semua role
     */
    public function show(string $id)
    {
        $commodity = Commodity::with('category')->find($id);

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        // Ambil 30 data harga terakhir untuk preview di detail
        $recentPrices = $commodity->priceHistories()
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get(['date', 'price', 'stok']);

        return response()->json([
            'success' => true,
            'data'    => array_merge($commodity->toArray(), [
                'recent_prices' => $recentPrices,
            ]),
        ]);
    }

    /**
     * Tambah komoditas baru.
     *
     * POST /api/commodities
     * Akses: admin
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:commodities,name',
            'category_id' => 'required|string',
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

        // Pastikan category_id valid
        $category = Category::find($request->category_id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        $commodity = Commodity::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'unit'        => $request->unit,
            'stok_unit'   => $request->stok_unit,
            'description' => $request->description,
        ]);

        $commodity->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Komoditas berhasil ditambahkan',
            'data'    => $commodity,
        ], 201);
    }

    /**
     * Update komoditas.
     * Menggunakan 'sometimes' agar bisa partial update (tidak wajib kirim semua field).
     *
     * PUT /api/commodities/{id}
     * Akses: admin
     */
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
            'category_id' => 'sometimes|string',
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

        // Validasi category_id jika diubah
        if ($request->has('category_id')) {
            $category = Category::find($request->category_id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan',
                ], 404);
            }
        }

        $commodity->update($request->only([
            'name',
            'category_id',
            'unit',
            'stok_unit',
            'description',
        ]));

        $commodity->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Komoditas berhasil diperbarui',
            'data'    => $commodity,
        ]);
    }

    /**
     * Hapus komoditas.
     * Tidak bisa dihapus jika masih punya data harga atau prediksi.
     *
     * DELETE /api/commodities/{id}
     * Akses: admin
     */
    public function destroy(string $id)
    {
        $commodity = Commodity::find($id);

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
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
