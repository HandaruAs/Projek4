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

    private function resolveCategory($value): string
    {
        if (is_array($value))  return $value['name'] ?? '-';
        if (is_object($value)) return $value->name   ?? '-';
        return (string) ($value ?? '-');
    }

    // ── Helper: build prediction rows pakai accessor model ──
    private function buildPredictionRows(): \Illuminate\Support\Collection
    {
        $today = Carbon::today()->format('Y-m-d');

        // Prediksi terbaru per komoditas (completed)
        $prediksiCompleted = \App\Models\Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name');

        // Prediksi pending/processing
        $prediksiPending = \App\Models\Prediction::where('status', 'pending')
            ->orWhere('status', 'processing')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->keyBy('commodity_name');

        // ── Baris completed ──────────────────────────────────────
        $rows = $prediksiCompleted->map(function ($pred) use ($today) {
            $forecast = $pred->forecast;
            $tanggal  = $pred->tanggal_pred;
            $ciLower  = $pred->ci_lower;
            $ciUpper  = $pred->ci_upper;
            $metrics  = $pred->metrics;

            $hargaHariIni   = null;
            $tanggalHariIni = null;

            if (!empty($tanggal) && !empty($forecast)) {
                $indexHariIni = array_search($today, $tanggal);

                if ($indexHariIni !== false) {
                    $hargaHariIni   = (float) $forecast[$indexHariIni];
                    $tanggalHariIni = $tanggal[$indexHariIni];
                } else {
                    $indexTerdekat = null;
                    foreach ($tanggal as $i => $tgl) {
                        if ($tgl <= $today) $indexTerdekat = $i;
                    }
                    if ($indexTerdekat !== null) {
                        $hargaHariIni   = (float) $forecast[$indexTerdekat];
                        $tanggalHariIni = $tanggal[$indexTerdekat];
                    } else {
                        $hargaHariIni   = (float) $forecast[0];
                        $tanggalHariIni = $tanggal[0];
                    }
                }
            }

            $hargaTerakhir = (float) ($pred->current_price ?? 0);
            $selisihPersen = $hargaTerakhir > 0 && $hargaHariIni
                ? round((($hargaHariIni - $hargaTerakhir) / $hargaTerakhir) * 100, 2)
                : 0;

            $tanggalAkhir = !empty($tanggal) ? end($tanggal) : null;
            $tanggalMulai = !empty($tanggal) ? $tanggal[0]   : null;

            return (object) [
                'commodity_name'  => $pred->commodity_name,
                'category'        => $this->resolveCategory($pred->kategori),
                'harga_sekarang'  => $hargaHariIni,
                'harga_terakhir'  => $hargaTerakhir,
                'satuan'          => $pred->satuan,
                'date'            => $tanggalHariIni,
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_akhir'   => $tanggalAkhir,
                'selisih_persen'  => $selisihPersen,
                'ci_lower'        => !empty($ciLower) ? (float) $ciLower[0] : null,
                'ci_upper'        => !empty($ciUpper) ? (float) $ciUpper[0] : null,
                'mape'            => $metrics['mape']     ?? null,
                'accuracy'        => $metrics['accuracy'] ?? null,
                'pred_status'     => 'completed',
                'created_at'      => $pred->created_at?->format('M d, Y H:i'),
                'is_prediction'   => true,
            ];
        });

        // ── Baris pending/processing ─────────────────────────────
        $completedNames = $prediksiCompleted->pluck('commodity_name')->toArray();

        $pendingRows = $prediksiPending->map(function ($pred) use ($completedNames) {
            if (in_array($pred->commodity_name, $completedNames)) {
                return null;
            }
            return (object) [
                'commodity_name' => $pred->commodity_name,
                'category'       => $this->resolveCategory($pred->kategori),
                'harga_sekarang' => null,
                'harga_terakhir' => (float) ($pred->current_price ?? 0),
                'satuan'         => $pred->satuan ?? '-',
                'date'           => null,
                'tanggal_mulai'  => null,
                'tanggal_akhir'  => null,
                'selisih_persen' => null,
                'pred_status'    => $pred->status,
                'created_at'     => $pred->created_at?->format('M d, Y H:i'),
                'is_prediction'  => true,
            ];
        })->filter()->values();

        // Urutan: completed → pending/processing saja
        return $rows->values()->concat($pendingRows);
    }

    // ── Helper: map prediction terbaru per komoditas untuk lookup ──
    private function getPrediksiTerbaru(): \Illuminate\Support\Collection
    {
        return \App\Models\Prediction::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('commodity_name')
            ->keyBy('commodity_name');
    }

    // ── ADMIN: /admin/harga ───────────────────────────────────
    public function index()
    {
        $totalHistoryRecords = PriceHistory::count();
        $totalPredRecords    = \App\Models\Prediction::where('status', 'completed')->count();
        $totalRecords        = $totalHistoryRecords + $totalPredRecords;

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
        $predictionRows = $this->buildPredictionRows();
        $prediksiMap    = $this->getPrediksiTerbaru();

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
            'predictionRows',
            'prediksiMap',
        ));
    }

    // ── ADMIN: /admin/harga/realtime (polling endpoint) ──────
    public function realtimeData(Request $request)
    {
        $query = PriceHistory::query();

        if ($request->search) {
            $query->where('commodity_name', 'like', '%' . $request->search . '%');
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->date) {
            $date = Carbon::parse($request->date);
            $query->whereBetween('date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ]);
        }

        $hargaList      = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $predictionRows = $this->buildPredictionRows();
        $prediksiMap    = $this->getPrediksiTerbaru();

        $hargaItems = collect($hargaList->items())->map(function ($item) use ($prediksiMap) {
            $pred          = $prediksiMap->get($item->commodity_name);
            $hargaPrediksi = $pred ? (float) ($pred->forecast[0] ?? 0) : null;
            $hargaHistoris = (float) ($item->harga_sekarang ?? 0);

            $selisihPersen = ($hargaPrediksi && $hargaHistoris > 0)
                ? round((($hargaPrediksi - $hargaHistoris) / $hargaHistoris) * 100, 2)
                : null;

            return array_merge($item->toArray(), [
                'harga_prediksi' => $hargaPrediksi,
                'selisih_persen' => $selisihPersen,
            ]);
        });

        return response()->json([
            'predictionRows' => $predictionRows,
            'hargaList'      => $hargaItems,
            'pagination'     => [
                'firstItem' => $hargaList->firstItem(),
                'lastItem'  => $hargaList->lastItem(),
                'total'     => $hargaList->total(),
                'predCount' => $predictionRows->count(),
            ],
            'hasFilter'  => (bool) ($request->search || $request->category || $request->date),
            'lastUpdate' => now()->format('H:i:s'),
        ]);
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