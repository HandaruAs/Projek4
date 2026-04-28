<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrediksiService;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    // GET /api/predictions?komoditas=Beras Premium&steps=30
    public function index(Request $request)
    {
        $komoditas = $request->get('komoditas', '');
        $steps     = (int) $request->get('steps', 30);

        if (!$komoditas) {
            // Kembalikan daftar komoditas jika tidak ada filter
            $list = PrediksiService::getCommodities();
            return response()->json(['success' => true, 'data' => $list]);
        }

        try {
            $data = $this->prediksiService->generate($komoditas, $steps);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // GET /api/predictions/{komoditas}?steps=30
    public function show(string $komoditas, Request $request)
    {
        $steps = (int) $request->get('steps', 30);
        // decode URL encoding (misal: Beras%20Premium → Beras Premium)
        $komoditas = rawurldecode($komoditas);

        try {
            $data = $this->prediksiService->generate($komoditas, $steps);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST /api/predictions/rekomendasi
    public function rekomendasi(Request $request)
    {
        $request->validate([
            'komoditas' => 'required|string',
            'konsumsi'  => 'nullable|numeric|min:0.1',
        ]);

        try {
            $data = $this->prediksiService->rekomendasi(
                $request->komoditas,
                (float) ($request->konsumsi ?? 1.0)
            );
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
