<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Commodity;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    /**
     * Ambil semua hasil prediksi.
     * Bisa difilter by commodity_id.
     *
     * GET /api/predictions
     * GET /api/predictions?commodity_id=xxx
     * Akses: semua role
     */
    public function index(Request $request)
    {
        $query = Prediction::with('commodity')
            ->orderBy('predicted_at', 'desc');

        if ($request->has('commodity_id')) {
            $query->where('commodity_id', $request->commodity_id);
        }

        $predictions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $predictions->items(),
            'meta'    => [
                'current_page' => $predictions->currentPage(),
                'per_page'     => $predictions->perPage(),
                'total'        => $predictions->total(),
                'last_page'    => $predictions->lastPage(),
            ],
        ]);
    }

    /**
     * Ambil detail satu hasil prediksi beserta data training job-nya.
     *
     * GET /api/predictions/{id}
     * Akses: semua role
     */
    public function show(string $id)
    {
        $prediction = Prediction::with(['commodity', 'trainingJob'])->find($id);

        if (!$prediction) {
            return response()->json([
                'success' => false,
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $prediction,
        ]);
    }

    /**
     * Ambil prediksi terbaru untuk satu komoditas.
     * Ini endpoint utama yang dipakai Flutter & dashboard untuk tampil grafik prediksi.
     *
     * GET /api/predictions/latest?commodity_id=xxx
     * Akses: semua role
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

        $prediction = Prediction::latestByCommodity($request->commodity_id)->first();

        if (!$prediction) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data prediksi untuk komoditas ini',
            ], 404);
        }

        // Gabungkan dengan harga aktual terakhir untuk perbandingan di grafik
        $lastActualPrice = $commodity->priceHistories()
            ->orderBy('date', 'desc')
            ->first(['date', 'price', 'stok']);

        return response()->json([
            'success' => true,
            'data'    => [
                'commodity'        => [
                    'id'        => $commodity->id,
                    'name'      => $commodity->name,
                    'unit'      => $commodity->unit,
                    'stok_unit' => $commodity->stok_unit,
                ],
                'predicted_at'     => $prediction->predicted_at,
                'horizon_days'     => $prediction->horizon_days,
                'metrics'          => $prediction->metrics,
                'results'          => $prediction->results,
                'last_actual_price'=> $lastActualPrice,
            ],
        ]);
    }

    /**
     * Ambil prediksi terbaru untuk SEMUA komoditas sekaligus.
     * Dipakai untuk halaman ringkasan / overview di dashboard.
     *
     * GET /api/predictions/summary
     * Akses: semua role
     */
    public function summary()
    {
        $commodities = Commodity::all(['_id', 'name', 'unit', 'stok_unit']);

        $summary = $commodities->map(function ($commodity) {
            $prediction = Prediction::latestByCommodity($commodity->id)->first();

            if (!$prediction) {
                return [
                    'commodity'   => $commodity,
                    'has_prediction' => false,
                    'predicted_at'   => null,
                    'next_day'       => null,
                    'metrics'        => null,
                ];
            }

            // Ambil prediksi hari pertama saja untuk tampilan ringkasan
            $nextDay = collect($prediction->results)->first();

            return [
                'commodity'      => $commodity,
                'has_prediction' => true,
                'predicted_at'   => $prediction->predicted_at,
                'horizon_days'   => $prediction->horizon_days,
                'next_day'       => $nextDay,
                'metrics'        => $prediction->metrics,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    /**
     * Hapus data prediksi.
     *
     * DELETE /api/predictions/{id}
     * Akses: admin
     */
    public function destroy(string $id)
    {
        $prediction = Prediction::find($id);

        if (!$prediction) {
            return response()->json([
                'success' => false,
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        $prediction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data prediksi berhasil dihapus',
        ]);
    }
}
