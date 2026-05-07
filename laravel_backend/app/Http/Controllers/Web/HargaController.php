<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HargaController extends Controller
{
    private function getCategories(): \Illuminate\Support\Collection
    {
        return PriceHistory::pluck('category')
            ->filter(fn($c) => !is_null($c) && $c !== '')
            ->unique()
            ->sort()
            ->values();
    }

    // ── ADMIN: /admin/harga ───────────────────────────────────
    public function index()
    {
        // ── Stat Cards ──────────────────────────────────────────────
        $totalHistoryRecords = PriceHistory::count();
        $totalPredRecords    = \App\Models\Prediction::where('status', 'completed')->count();
        $totalRecords        = $totalHistoryRecords + $totalPredRecords;

        // Records Today: history hari ini + prediction dibuat hari ini
        $todayRecords = PriceHistory::whereBetween('date', [
                            Carbon::today()->startOfDay(),
                            Carbon::today()->endOfDay(),
                        ])->count()
                    + \App\Models\Prediction::where('status', 'completed')
                            ->whereBetween('created_at', [
                                Carbon::today()->startOfDay(),
                                Carbon::today()->endOfDay(),
                            ])->count();

        $totalKomoditas = Commodity::count();

        // ── Prediction rows (paling atas tabel) ─────────────────────
        $predictionRows = \App\Models\Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->map(function ($pred) {
                $forecast      = $pred->payload['forecast']     ?? [];
                $tanggal       = $pred->payload['tanggal_pred'] ?? [];
                $hargaTerakhir = (float) ($pred->payload['harga_terakhir'] ?? 0);

                $maxHarga   = !empty($forecast) ? max($forecast) : 0;
                $maxIndex   = !empty($forecast) ? array_search($maxHarga, $forecast) : 0;
                $maxTanggal = $tanggal[$maxIndex] ?? null;

                return (object) [
                    'commodity_name' => $pred->commodity_name,
                    'category'       => $pred->payload['kategori'] ?? '-',
                    'harga_sekarang' => $maxHarga,
                    'satuan'         => $pred->payload['satuan']   ?? '-',
                    'date'           => $maxTanggal,
                    'is_prediction'  => true,
                ];
            })
            ->values();

        // ── Price History (filter seperti biasa) ────────────────────
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

        $hargaList  = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $categories = $this->getCategories();

        return view('admin.harga', compact(
            'totalRecords',
            'todayRecords',
            'totalKomoditas',
            'hargaList',
            'categories',
            'predictionRows',   // ← tambahan
        ));
    }

    // ── USER: /harga ──────────────────────────────────────────
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

        $hargaList  = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $categories = $this->getCategories();

        return view('user.harga', compact(
            'totalRecords',
            'todayRecords',
            'totalKomoditas',
            'hargaList',
            'categories',
        ));
    }
}
