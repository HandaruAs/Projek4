<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Prediction;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserHargaController extends Controller
{
    public function harga(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category');
        $date     = $request->get('date');

        // ── Base query ──────────────────────────────────────────
        $baseNoDate = PriceHistory::query();
        if ($search) {
            $baseNoDate->where('commodity_name', 'like', "%{$search}%");
        }
        if ($category && $category !== 'Semua') {
            $baseNoDate->where('category', $category);
        }

        $baseQuery = clone $baseNoDate;
        if ($date) {
            $start = Carbon::parse($date)->startOfDay()->toDateTimeString();
            $end   = Carbon::parse($date)->endOfDay()->toDateTimeString();
            $baseQuery->whereBetween('date', [$start, $end]);
        }

        // ── Harga list ──────────────────────────────────────────
        $hargaList = (clone $baseQuery)
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // Hitung selisih per item di tabel
        foreach ($hargaList as $item) {
            $prev = PriceHistory::where('commodity_name', $item->commodity_name)
                ->where('date', '<', $item->date)
                ->orderBy('date', 'desc')
                ->first();

            $hargaLama     = $prev ? $prev->harga_sekarang : $item->harga_sekarang;
            $item->selisih = $item->harga_sekarang - $hargaLama;
            $item->persen  = $hargaLama > 0 ? ($item->selisih / $hargaLama) * 100 : 0;
        }

        // ── Stat card: dari predictions ─────────────────────────
        $latestPredictions = Prediction::raw(function ($collection) {
            return $collection->aggregate([
                ['$sort' => ['created_at' => -1]],
                ['$group' => [
                    '_id'            => '$commodity_name',
                    'harga_terakhir' => ['$first' => '$payload.harga_terakhir'],
                    'forecast'       => ['$first' => '$payload.forecast'],
                    'commodity_name' => ['$first' => '$commodity_name'],
                ]],
            ])->toArray();
        });

        // Rata-rata harga_terakhir
        $avgHargaHariIni = null;
        if (count($latestPredictions) > 0) {
            $total = array_sum(array_map(fn($p) => $p['harga_terakhir'] ?? 0, $latestPredictions));
            $avgHargaHariIni = round($total / count($latestPredictions));
        }

        // Harga tertinggi saat ini
        $naikTertinggi = null;
        $maxHarga = 0;
        foreach ($latestPredictions as $p) {
            $harga = $p['harga_terakhir'] ?? 0;
            if ($harga > $maxHarga) {
                $maxHarga = $harga;
                $obj = new \stdClass();
                $obj->commodity_name = $p['commodity_name'] ?? $p['_id'];
                $obj->harga_terakhir = $harga;
                $forecast = $p['forecast'] ?? [];
                $hargaPred    = $forecast[0] ?? $harga;
                $obj->selisih = $hargaPred - $harga;
                $obj->persen  = $harga > 0 ? ($obj->selisih / $harga) * 100 : 0;
                $naikTertinggi = $obj;
            }
        }

        // ── Total komoditas ─────────────────────────────────────
        $totalKomoditas = (clone $baseQuery)
            ->distinct('commodity_name')
            ->count('commodity_name');

        // ── Category list ───────────────────────────────────────
        $categoryList = Category::orderBy('name')->get(['_id', 'name']);

        $selectedCategoryName = $category
            ? optional(Category::find($category))->name
            : null;

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
