<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class KomoditasController extends Controller
{
    public function index(Request $request)
    {
        $totalKomoditas  = Commodity::count();
        $totalCategories = count(
            Commodity::raw(fn($col) => $col->distinct('category', []))
        );
        $activeKomoditas = Commodity::whereHas('priceHistories')->count();

        $query = Commodity::orderBy('name');

        // Server-side search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $commodities = $query->paginate(10)->withQueryString();

        $categoryList = collect(
            Commodity::raw(fn($col) => $col->distinct('category', []))
        )->filter()->sort()->values();

        return view('admin.komoditas', compact(
            'commodities', 'totalKomoditas',
            'totalCategories', 'activeKomoditas', 'categoryList'
        ));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.komoditas-create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
        ]);

        $category = Category::findOrFail($request->category_id);

        Commodity::create([
            'name'     => $request->name,
            'category' => $category->name, // simpan nama kategori
        ]);

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $commodity  = Commodity::findOrFail($id);
        $categories = Category::orderBy('name')->get();

        return view('admin.komoditas-edit', compact('commodity', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
        ]);

        $category = Category::findOrFail($request->category_id);

        Commodity::findOrFail($id)->update([
            'name'     => $request->name,
            'category' => $category->name,
        ]);

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        PriceHistory::where('commodity_id', new ObjectId($id))->delete();
        Commodity::findOrFail($id)->delete();

        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil dihapus.');
    }
}
