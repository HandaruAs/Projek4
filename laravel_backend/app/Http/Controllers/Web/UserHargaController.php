<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Commodity;
use Illuminate\Http\Request;

class UserHargaController extends Controller
{
    public function harga(Request $request)
    {
        $search   = $request->get('searchInput');
        $category = $request->get('categoryFilter');
        $date     = $request->get('dateFilter');

        // Base query pakai model yang benar
        $baseQuery = PriceHistory::query();

        if ($search) {
            $baseQuery->where('commodity_name', 'like', "%{$search}%");
        }
        if ($category && $category != 'Semua') {
            $baseQuery->where('category', $category);
        }
        if ($date) {
            $baseQuery->whereDate('date', $date);
        }

        // Pagination
        $hargaList = (clone $baseQuery)
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->appends(request()->query());

        // Stat cards ikut filter
        $totalRecords   = (clone $baseQuery)->count();
        $todayRecords   = (clone $baseQuery)
            ->whereDate('date', now()->toDateString())
            ->count();
        $totalKomoditas = (clone $baseQuery)
            ->distinct('commodity_name')
            ->count();

        // Kategori untuk filter bar
        $categoryList = PriceHistory::distinct('category')
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        $categoryList = collect(['Semua'])->merge($categoryList)->toArray();

        return view('user.harga', compact(
            'hargaList',
            'totalRecords',
            'todayRecords',
            'totalKomoditas',
            'categoryList'
        ));
    }
}