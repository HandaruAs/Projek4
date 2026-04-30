<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PrediksiService;
use Illuminate\Http\Request;

class UserPrediksiController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    public function prediksi(Request $request)
    {
        // 1. Ambil daftar komoditas dari Flask
        $komoditasList = PrediksiService::getCommodities();

        // 2. Tentukan komoditas yang dipilih
        $selectedNama = $request->get('komoditas');
        if (!$selectedNama && count($komoditasList) > 0) {
            $selectedNama = $komoditasList[0];
        }

        $prediksiData     = null;
        $chartData        = null;
        $prediksiMingguan = [];
        $estimasiHarga    = null;
        $trenPersen       = null;
        $kepercayaan      = null;

        if ($selectedNama) {
            try {
                $data = $this->prediksiService->generate($selectedNama, 30);

                $forecast    = $data['forecast']    ?? [];
                $tanggal     = $data['tanggal_pred'] ?? [];
                $ciLower     = $data['ci_lower']    ?? [];
                $ciUpper     = $data['ci_upper']    ?? [];
                $hargaKini   = $data['harga_terakhir'] ?? 0;
                $accuracy    = $data['accuracy']['accuracy'] ?? null;

                // Estimasi harga hari ke-30
                $estimasiHarga = !empty($forecast) ? end($forecast) : null;

                // Tren persen
                $trenPersen = ($hargaKini > 0 && $estimasiHarga)
                    ? round(($estimasiHarga - $hargaKini) / $hargaKini * 100, 1)
                    : null;

                $kepercayaan = $accuracy;

                // Chart data — 14 hari pertama
                $chartData = [
                    'pred_tanggal' => array_slice($tanggal, 0, 14),
                    'pred_harga'   => array_slice($forecast, 0, 14),
                    'ci_lower'     => array_slice($ciLower, 0, 14),
                    'ci_upper'     => array_slice($ciUpper, 0, 14),
                    'harga_kini'   => $hargaKini,
                ];

                // Tabel mingguan
                $prediksiMingguan = $this->buildWeeklyTable($tanggal, $forecast, $hargaKini);

                $prediksiData = $data;

            } catch (\Exception $e) {
                $prediksiData = ['error' => $e->getMessage()];
            }
        }

        return view('user.prediksi', compact(
            'komoditasList',
            'selectedNama',
            'prediksiData',
            'chartData',
            'prediksiMingguan',
            'estimasiHarga',
            'trenPersen',
            'kepercayaan'
        ));
    }

    private function buildWeeklyTable(array $tanggal, array $forecast, float $hargaKini): array
    {
        $weeks   = [];
        $chunks  = array_chunk(array_map(null, $tanggal, $forecast), 7);

        foreach ($chunks as $i => $chunk) {
            $chunk   = array_filter($chunk);
            $prices  = array_column($chunk, 1);
            $dates   = array_column($chunk, 0);

            if (empty($prices)) continue;

            $avg       = array_sum($prices) / count($prices);
            $deltaPct  = $hargaKini > 0 ? round(($avg - $hargaKini) / $hargaKini * 100, 1) : 0;

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
