<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Ambil semua kategori.
     *
     * GET /api/categories
     * Akses: semua role
     */
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    /**
     * Ambil detail satu kategori beserta daftar komoditasnya.
     *
     * GET /api/categories/{id}
     * Akses: semua role
     */
    public function show(string $id)
    {
        $category = Category::with('commodities')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $category,
        ]);
    }

    /**
     * Tambah kategori baru.
     *
     * POST /api/categories
     * Akses: admin
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category = Category::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data'    => $category,
        ], 201);
    }

    /**
     * Update kategori.
     *
     * PUT /api/categories/{id}
     * Akses: admin
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id . ',_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'data'    => $category,
        ]);
    }

    /**
     * Hapus kategori.
     * Tidak bisa dihapus jika masih punya komoditas.
     *
     * DELETE /api/categories/{id}
     * Akses: admin
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        // Cek apakah masih ada komoditas yang pakai kategori ini
        if ($category->commodities()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih memiliki komoditas',
            ], 409);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ]);
    }
}
