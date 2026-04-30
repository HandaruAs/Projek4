<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Category;
use Illuminate\Http\Request;

class UserHargaController extends Controller
{
    public function harga(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category'); // sekarang berisi category_id
        $date     = $request->get('date');

        $baseNoDate = PriceHistory::query();
        if ($search) {
            $baseNoDate->where('commodity_name', 'like', "%{$search}%");
        }
        if ($category && $category !== 'Semua') {
            $baseNoDate->where('category_id', $category); // FIX
        }

        $baseQuery = clone $baseNoDate;
        if ($date) {
            $start = \Carbon\Carbon::parse($date)->startOfDay()->toDateTimeString();
            $end   = \Carbon\Carbon::parse($date)->endOfDay()->toDateTimeString();
            $baseQuery->whereBetween('date', [$start, $end]);
        }

        $hargaList = (clone $baseQuery)
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // Eager load categoryRelation
        $hargaList->load('categoryRelation');

        $todayStart = now()->startOfDay()->toDateTimeString();
        $todayEnd   = now()->endOfDay()->toDateTimeString();

        $avgHargaHariIni = (clone $baseNoDate)
            ->whereBetween('date', [$todayStart, $todayEnd])
            ->avg('harga_sekarang');

        $naikTertinggi = (clone $baseNoDate)
            ->whereBetween('date', [$todayStart, $todayEnd])
            ->where('selisih', '>', 0)
            ->orderBy('selisih', 'desc')
            ->first();

        $totalKomoditas = (clone $baseQuery)->count('commodity_name');

        // FIX: categoryList dari model Category
        $categoryList = Category::orderBy('name')->get(['_id', 'name']);

        $selectedCategoryName = $category ? optional(\App\Models\Category::find($category))->name : null;

        return view('user.harga', compact(
            'hargaList',
            'avgHargaHariIni',
            'naikTertinggi',
            'totalKomoditas',
            'categoryList',
            'selectedCategoryName'
        ));
    }
}