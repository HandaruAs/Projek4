<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Services\PrediksiService;
use Illuminate\Http\Request;

class UserPrediksiController extends Controller
{
    // GET /prediksi
    // User hanya MELIHAT hasil prediksi yang sudah di-generate oleh admin.
    // Tidak ada generate dari sisi user.
    public function prediksi(Request $request)
    {
        // 1. Ambil daftar komoditas yang sudah pernah di-generate admin
        //    (diambil dari MongoDB predictions, bukan dari Flask langsung)
        $prediksiList = Prediction::orderBy('created_at', 'desc')
            ->get(['commodity_name', 'steps', 'created_at', 'accuracy_mape',
                   'accuracy_mae', 'accuracy_rmse', 'status', '_id']);

        // Daftar unik komoditas untuk dropdown
        $komoditasList = $prediksiList->pluck('commodity_name')->unique()->values()->toArray();

        // 2. Komoditas yang dipilih
        $selectedNama = $request->get('komoditas', $komoditasList[0] ?? null);

        $prediction       = null;
        $chartData        = null;
        $prediksiMingguan = [];
        $estimasiHarga    = null;
        $trenPersen       = null;
        $kepercayaan      = null;

        if ($selectedNama) {
            // Ambil prediksi terbaru dari MongoDB untuk komoditas ini
            $prediction = Prediction::where('commodity_name', $selectedNama)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($prediction) {
                $payload   = $prediction->payload ?? [];
                $forecast  = $payload['forecast']       ?? [];
                $tanggal   = $payload['tanggal_pred']   ?? [];
                $ciLower   = $payload['ci_lower']       ?? [];
                $ciUpper   = $payload['ci_upper']       ?? [];
                $hargaKini = $payload['harga_terakhir'] ?? 0;
                $accuracy  = $payload['accuracy']['accuracy'] ?? null;

                // Estimasi harga hari ke-30
                $estimasiHarga = !empty($forecast) ? end($forecast) : null;

                // Tren persen vs harga saat ini
                $trenPersen = ($hargaKini > 0 && $estimasiHarga)
                    ? round(($estimasiHarga - $hargaKini) / $hargaKini * 100, 1)
                    : null;

                $kepercayaan = $accuracy;

                // Chart data — 14 hari pertama
                $chartData = [
                    'pred_tanggal' => array_slice($tanggal, 0, 14),
                    'pred_harga'   => array_slice($forecast, 0, 14),
                    'ci_lower'     => array_slice(is_array($ciLower) ? $ciLower : [], 0, 14),
                    'ci_upper'     => array_slice(is_array($ciUpper) ? $ciUpper : [], 0, 14),
                    'harga_kini'   => $hargaKini,
                ];

                // Tabel mingguan
                $prediksiMingguan = $this->buildWeeklyTable($tanggal, $forecast, $hargaKini);
            }
        }

        return view('user.prediksi', compact(
            'komoditasList',
            'selectedNama',
            'prediction',
            'chartData',
            'prediksiMingguan',
            'estimasiHarga',
            'trenPersen',
            'kepercayaan'
        ));
    }

    private function buildWeeklyTable(array $tanggal, array $forecast, float $hargaKini): array
    {
        $weeks  = [];
        $chunks = array_chunk(array_map(null, $tanggal, $forecast), 7);

        foreach ($chunks as $i => $chunk) {
            $chunk  = array_filter($chunk);
            $prices = array_column($chunk, 1);
            $dates  = array_column($chunk, 0);

            if (empty($prices)) continue;

            $avg      = array_sum($prices) / count($prices);
            $deltaPct = $hargaKini > 0
                ? round(($avg - $hargaKini) / $hargaKini * 100, 1)
                : 0;

            $weeks[] = [
                'minggu'    => 'W' . ($i + 1),
                'periode'   => ($dates[0] ?? '') . ' – ' . (end($dates) ?: ''),
                'estimasi'  => (int) round($avg),
                'delta_pct' => $deltaPct,
            ];
        }

        return $weeks;
    }
}