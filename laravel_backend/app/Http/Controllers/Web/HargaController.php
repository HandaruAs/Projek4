<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HargaController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        if (($user['role'] ?? null) !== 'admin') return redirect('/dashboard');
        return $user;
    }

    public function index()
    {
        $totalRecords   = PriceHistory::count();
        $todayRecords   = PriceHistory::whereBetween('date', [
            Carbon::today()->startOfDay(),
            Carbon::today()->endOfDay(),
        ])->count();
        $totalKomoditas = Commodity::count();

        $query = PriceHistory::query();

        if (request('search')) {
            $query->where('commodity_name', 'like', '%' . request('search') . '%');
        }

        if (request('category')) {
            $query->where('category', request('category'));
        }

        if (request('date')) {
            $date = Carbon::parse(request('date'));
            $query->whereBetween('date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ]);
        }

        $hargaList = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();

        $categoryList = collect(
            PriceHistory::raw(fn($col) => $col->distinct('category', []))
        )->filter()->sort()->values();

        return view('admin.harga', compact(
            'totalRecords',
            'todayRecords',
            'totalKomoditas',
            'hargaList',
            'categoryList',
        ));
    }

    // ── USER: /harga ─────────────────────────────────────────
    public function userIndex()
    {
        $totalRecords   = PriceHistory::count();
        $todayRecords   = PriceHistory::whereBetween('date', [
            Carbon::today()->startOfDay(),
            Carbon::today()->endOfDay(),
        ])->count();
        $totalKomoditas = Commodity::count();

        $query = PriceHistory::query();

        if (request('search')) {
            $query->where('commodity_name', 'like', '%' . request('search') . '%');
        }
        if (request('category')) {
            $query->where('category', request('category'));
        }
        if (request('date')) {
            $date = Carbon::parse(request('date'));
            $query->whereBetween('date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ]);
        }

        $hargaList = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();

        $categoryList = collect(
            PriceHistory::raw(fn($col) => $col->distinct('category', []))
        )->filter()->sort()->values();

        return view('user.harga', compact(
            'totalRecords',
            'todayRecords',
            'totalKomoditas',
            'hargaList',
            'categoryList',
        ));
    }
}