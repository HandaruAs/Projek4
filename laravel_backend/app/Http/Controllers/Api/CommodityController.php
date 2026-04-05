<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommodityController extends Controller
{
    public function index(Request $request)
    {
        $query = Commodity::orderBy('name', 'asc');

        if ($request->has('category_id')) {
            $query->where('category', $request->category_id);
        }

        $commodities = $query->get();

        $result = $commodities->map(function ($commodity) {
            $prices = $commodity->priceHistories()
                ->orderBy('date', 'desc')
                ->limit(2)
                ->get();

            $currentPrice  = $prices->first()?->harga_sekarang ?? 0;
            $previousPrice = $prices->skip(1)->first()?->harga_sekarang ?? 0;

            // getAttributes() ambil semua field dari MongoDB tanpa dibatasi $fillable
            $raw = $commodity->getAttributes();

            return [
                '_id'            => (string) $commodity->_id,
                'name'           => $raw['name']        ?? '',
                'category'       => $raw['category']    ?? '',
                'unit'           => $raw['unit']         ?? '',
                'stok_unit'      => $raw['stok_unit']    ?? '',
                'description'    => $raw['description']  ?? '',
                'current_price'  => $currentPrice,
                'previous_price' => $previousPrice,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    public function show(string $id)
    {
        $commodity = Commodity::find($id);

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $recentPrices = $commodity->priceHistories()
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get(['date', 'price', 'stok']);

        $raw = $commodity->getAttributes();

        return response()->json([
            'success' => true,
            'data'    => [
                '_id'           => (string) $commodity->_id,
                'name'          => $raw['name']       ?? '',
                'category'      => $raw['category']   ?? '',
                'unit'          => $raw['unit']        ?? '',
                'stok_unit'     => $raw['stok_unit']   ?? '',
                'description'   => $raw['description'] ?? '',
                'recent_prices' => $recentPrices,
            ],
        ]);
    }

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
            'name',
            'category',
            'unit',
            'stok_unit',
            'description',
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
