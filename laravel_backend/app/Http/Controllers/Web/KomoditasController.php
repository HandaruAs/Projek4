<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\PriceHistory;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    public function index(Request $request)
    {
        $totalKomoditas = Commodity::count();

        // ✅ Fix count & collect: gunakan pluck() sebagai alternatif distinct
        $distinctCategories = Commodity::pluck('category')->filter()->unique();
        $totalCategories    = $distinctCategories->count();

        $activeKomoditas = Commodity::whereHas('priceHistories')->count();

        $query = Commodity::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $commodities  = $query->paginate(10)->withQueryString();
        $categoryList = Commodity::pluck('category')->filter()->unique()->sort()->values();

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
            'category' => $category->name,
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
        // ✅ Fix ObjectId: hindari new ObjectId(), gunakan string $id langsung
        // MongoDB Laravel driver otomatis cast string ke ObjectId
        PriceHistory::where('commodity_id', $id)->delete();
        Commodity::findOrFail($id)->delete();
        return redirect('/admin/komoditas')->with('success', 'Komoditas berhasil dihapus.');
    }
}