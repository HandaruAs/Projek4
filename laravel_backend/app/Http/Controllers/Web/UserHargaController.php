<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use Illuminate\Http\Request;

class UserHargaController extends Controller
{
    public function harga(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category');
        $date     = $request->get('date');

        // ── Base query: ikut search + category (tanpa date) ──────────────
        $baseNoDate = PriceHistory::query();

        if ($search) {
            $baseNoDate->where('commodity_name', 'like', "%{$search}%");
        }
        if ($category && $category !== 'Semua') {
            $baseNoDate->where('category', $category);
        }

        // ── Base query LENGKAP: ikut search + category + date ────────────
        $baseQuery = clone $baseNoDate;

        if ($date) {
            $start = \Carbon\Carbon::parse($date)->startOfDay()->toDateTimeString();
            $end   = \Carbon\Carbon::parse($date)->endOfDay()->toDateTimeString();
            $baseQuery->whereBetween('date', [$start, $end]);
        }

        // ── Tabel (ikut semua filter) ─────────────────────────────────────
        $hargaList = (clone $baseQuery)
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // ── Range hari ini ────────────────────────────────────────────────
        $todayStart = now()->startOfDay()->toDateTimeString();
        $todayEnd   = now()->endOfDay()->toDateTimeString();

        // ── Card 1: Rata-rata Harga Hari Ini (ikut search + category) ─────
        $avgHargaHariIni = (clone $baseNoDate)
            ->whereBetween('date', [$todayStart, $todayEnd])
            ->avg('harga_sekarang');

        // ── Card 2: Naik Tertinggi Hari Ini (ikut search + category) ──────
        $naikTertinggi = (clone $baseNoDate)
            ->whereBetween('date', [$todayStart, $todayEnd])
            ->where('selisih', '>', 0)
            ->orderBy('selisih', 'desc')
            ->first();

        // ── Card 3: Total Komoditas (ikut semua filter termasuk date) ──────
        $totalKomoditas = (clone $baseQuery)->count('commodity_name');

        // ── Daftar kategori untuk filter bar ──────────────────────────────
        $categoryList = PriceHistory::distinct('category')
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        $categoryList = collect(['Semua'])->merge($categoryList)->toArray();

        return view('user.harga', compact(
            'hargaList',
            'avgHargaHariIni',
            'naikTertinggi',
            'totalKomoditas',
            'categoryList'
        ));
    }
}