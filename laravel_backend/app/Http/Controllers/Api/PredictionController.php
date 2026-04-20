<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Commodity;
use App\Models\PriceHistory;
use App\Services\PrediksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PredictionController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    /**
     * GET /api/predictions
     * Sinkron Flask: GET /api/admin/prediction_logs
     *
     * Query params:
     *   commodity_id  → filter by commodity
     *   per_page      → jumlah per halaman (default 15)
     */
    public function index(Request $request)
    {
        $query = Prediction::orderBy('predicted_at', 'desc');

        if ($request->has('commodity_id')) {
            $query->where('commodity_id', $request->commodity_id);
        }

        // Sembunyikan payload besar (tanggal_pred, forecast, ci_lower, ci_upper, results)
        // saat listing — hanya tampilkan metadata
        $predictions = $query->paginate($request->get('per_page', 15));

        $items = collect($predictions->items())->map(fn($p) => [
            'id'             => $p->id,
            'commodity_id'   => $p->commodity_id,
            'commodity_name' => $p->commodity_name,
            'predicted_at'   => $p->predicted_at,
            'horizon_days'   => $p->horizon_days,
            'current_price'  => $p->current_price,
            'satuan'         => $p->satuan,
            'kategori'       => $p->kategori,
            'metrics'        => [
                'mae'                  => $p->metrics['mae']                  ?? null,
                'rmse'                 => $p->metrics['rmse']                 ?? null,
                'mape'                 => $p->metrics['mape']                 ?? null,
                'accuracy'             => $p->metrics['accuracy']             ?? null,
                'recommendation'       => $p->metrics['recommendation']       ?? null,
                'recommendation_score' => $p->metrics['recommendation_score'] ?? null,
            ],
            'created_at' => $p->created_at,
        ])->toArray();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $predictions->currentPage(),
                'per_page'     => $predictions->perPage(),
                'total'        => $predictions->total(),
                'last_page'    => $predictions->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/predictions/{id}
     * Sinkron Flask: payload lengkap (tanggal_pred, forecast, ci_lower, ci_upper)
     */
    public function show(string $id)
    {
        $p = Prediction::find($id);

        if (!$p) {
            return response()->json([
                'success' => false,
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $p->id,
                'commodity_id'   => $p->commodity_id,
                'commodity_name' => $p->commodity_name,
                'predicted_at'   => $p->predicted_at,
                'horizon_days'   => $p->horizon_days,
                'satuan'         => $p->satuan,
                'kategori'       => $p->kategori,
                // Sinkron Flask payload fields:
                'harga_terakhir'   => (int) $p->current_price,
                'tanggal_pred'     => $p->tanggal_pred,
                'forecast'         => $p->forecast,
                'ci_lower'         => $p->ci_lower,
                'ci_upper'         => $p->ci_upper,
                // Detail rows (untuk tabel):
                'results'          => $p->results,
                'metrics'          => $p->metrics,
                'accuracy'         => $p->metrics['accuracy'] ?? null,
            ],
        ]);
    }

    /**
     * GET /api/predictions/latest?commodity_id=xxx
     * Sinkron Flask: GET /api/prediksi/<komoditas> (dengan cache 24 jam)
     *
     * Mengembalikan payload lengkap termasuk tanggal_pred, forecast,
     * ci_lower, ci_upper — identik dengan Flask.
     */
    public function latest(Request $request)
    {
        if (!$request->has('commodity_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter commodity_id wajib diisi',
            ], 422);
        }

        $commodity = Commodity::find($request->commodity_id);
        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Komoditas tidak ditemukan',
            ], 404);
        }

        $p = Prediction::latestByCommodity($request->commodity_id)->first();

        if (!$p) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data prediksi untuk komoditas ini',
            ], 404);
        }

        // Harga terakhir aktual (sinkron Flask: get_series() .iloc[-1])
        $lastActual = PriceHistory::where('commodity_id', $request->commodity_id)
            ->orderBy('date', 'desc')
            ->first(['date', 'harga_sekarang', 'satuan']);

        return response()->json([
            'success' => true,
            'data'    => [
                'commodity' => [
                    'id'     => (string) $commodity->_id,
                    'name'   => $commodity->name,
                    'unit'   => $commodity->unit,
                ],
                // Sinkron Flask payload:
                'commodity_name'    => $p->commodity_name,
                'predicted_at'      => $p->predicted_at,
                'horizon_days'      => $p->horizon_days,
                'satuan'            => $p->satuan ?? $lastActual?->satuan ?? 'kg',
                'kategori'          => $p->kategori,
                'harga_terakhir'    => (int) ($lastActual?->harga_sekarang ?? $p->current_price),
                'tanggal_terakhir'  => $lastActual?->date?->format('Y-m-d'),
                'tanggal_pred'      => $p->tanggal_pred,
                'forecast'          => $p->forecast,
                'ci_lower'          => $p->ci_lower,
                'ci_upper'          => $p->ci_upper,
                'accuracy'          => $p->metrics['accuracy'] ?? null,
                'metrics'           => $p->metrics,
                // Data aktual terakhir
                'last_actual_price' => $lastActual,
            ],
        ]);
    }

    /**
     * GET /api/predictions/summary
     * Sinkron Flask: GET /api/dashboard
     *
     * Mengembalikan ringkasan tiap komoditas: harga kini, prediksi 7 hari,
     * status tren, rekomendasi.
     */
    public function summary()
    {
        $commodities = Commodity::all(['_id', 'name', 'unit']);

        $summary = $commodities->map(function ($c) {
            $p = Prediction::latestByCommodity((string) $c->_id)->first();

            if (!$p) {
                return [
                    'commodity'      => [
                        'id'   => (string) $c->_id,
                        'name' => $c->name,
                        'unit' => $c->unit,
                    ],
                    'has_prediction' => false,
                    'predicted_at'   => null,
                    'next_day'       => null,
                    'metrics'        => null,
                ];
            }

            $results = $p->results ?? [];
            $forecast = $p->forecast ?? array_column($results, 'predicted_price');

            // Prediksi 7 hari rata-rata (sinkron Flask: pred_7hari)
            $prices7  = array_slice($forecast, 0, 7);
            $avg7     = count($prices7) > 0 ? (int)(array_sum($prices7) / count($prices7)) : $p->current_price;
            $deltaPred = $p->current_price > 0
                ? round(($avg7 - $p->current_price) / $p->current_price * 100, 1)
                : 0;

            // Status label (sinkron Flask _status())
            $status = $this->statusLabel($deltaPred);

            return [
                'commodity' => [
                    'id'     => (string) $c->_id,
                    'name'   => $c->name,
                    'unit'   => $c->unit ?? $p->satuan ?? 'kg',
                ],
                'has_prediction' => true,
                'predicted_at'   => $p->predicted_at,
                'horizon_days'   => $p->horizon_days,
                // Sinkron Flask: harga_kini, delta_pred, pred_7hari, status
                'harga_kini'     => (int) $p->current_price,
                'pred_7hari'     => $avg7,
                'delta_pred'     => $deltaPred,
                'status'         => $status,
                'satuan'         => $p->satuan ?? $c->unit ?? 'kg',
                'kategori'       => $p->kategori ?? '',
                'next_day'       => $results[0] ?? null,
                'metrics'        => [
                    'mae'              => $p->metrics['mae']              ?? null,
                    'mape'             => $p->metrics['mape']             ?? null,
                    'accuracy'         => $p->metrics['accuracy']         ?? null,
                    'recommendation'   => $p->metrics['recommendation']   ?? null,
                    'recommendation_score' => $p->metrics['recommendation_score'] ?? null,
                    'warna'            => $p->metrics['warna']            ?? null,
                    'emoji'            => $p->metrics['emoji']            ?? null,
                    'delta_pct_7'      => $p->metrics['delta_pct_7']     ?? null,
                    'delta_pct_30'     => $p->metrics['delta_pct_30']    ?? null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    /**
     * POST /api/predictions/generate  (admin only)
     * Sinkron Flask: POST /api/admin/run_prediksi
     *
     * Body: { "commodity_id": "...", "steps": 30 }
     */
    public function generate(Request $request)
    {
        $request->validate([
            'commodity_id' => 'required|string',
            'steps'        => 'required|integer|min:1|max:90',
        ]);

        try {
            $predictionData = $this->prediksiService->generate(
                $request->commodity_id,
                (int) $request->steps
            );

            $saved = Prediction::create($predictionData);

            return response()->json([
                'success' => true,
                'message' => "Prediksi untuk {$predictionData['commodity_name']} berhasil digenerate.",
                'data'    => [
                    'id'             => $saved->id,
                    'commodity_name' => $saved->commodity_name,
                    'predicted_at'   => $saved->predicted_at,
                    'horizon_days'   => $saved->horizon_days,
                    'accuracy'       => $saved->metrics['accuracy'] ?? null,
                    'recommendation' => $saved->metrics['recommendation'] ?? null,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Prediction generate failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate prediksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/predictions/{id}  (admin only)
     */
    public function destroy(string $id)
    {
        $p = Prediction::find($id);

        if (!$p) {
            return response()->json([
                'success' => false,
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        $p->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data prediksi berhasil dihapus',
        ]);
    }

    /** Sinkron Flask: _status() */
    private function statusLabel(float $dp): string
    {
        if ($dp > 5)  return 'naik_signifikan';
        if ($dp > 2)  return 'naik';
        if ($dp < -5) return 'turun_signifikan';
        if ($dp < -2) return 'turun';
        return 'stabil';
    }
}
