<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Prediction;
use App\Models\PriceHistory;
use App\Services\PrediksiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserPrediksiController extends Controller
{
    /**
     * GET /prediksi
     *
     * Menampilkan halaman prediksi harga untuk user.
     * Sinkron dengan Flask:
     *  - GET /api/prediksi/<komoditas>  → ambil forecast + accuracy dari DB
     *  - Data disajikan langsung ke view (bukan dummy statis)
     */
    public function prediksi(Request $request)
    {
        // 1. Ambil semua komoditas (untuk dropdown)
$komoditasList = PrediksiService::getCommodities()
            ->map(fn($c) => [
                'id'   => (string) $c->_id,
                'nama' => $c->name,
                'unit' => $c->unit ?? 'kg',
            ])->toArray();

        // 2. Tentukan komoditas yang dipilih
        $selectedId = $request->get('commodity_id');

        // Jika tidak ada pilihan, pakai komoditas pertama
        if (!$selectedId && count($komoditasList) > 0) {
            $selectedId = $komoditasList[0]['id'];
        }

        $prediksiData     = null;
        $selectedKomoditas = null;
        $chartData        = null;
        $prediksiMingguan = [];
        $estimasiHarga    = null;
        $trenPersen       = null;
        $kepercayaan      = null;

        if ($selectedId) {
            $commodity = Commodity::find($selectedId);
            $selectedKomoditas = $commodity ? [
                'id'   => (string) $commodity->_id,
                'nama' => $commodity->name,
                'unit' => $commodity->unit ?? 'kg',
            ] : null;

            // 3. Ambil prediksi terbaru dari DB (sinkron: seperti GET /api/prediksi/<komoditas>)
            $prediction = Prediction::latestByCommodity($selectedId)->first();

            if ($prediction) {
                $results  = $prediction->results  ?? [];
                $metrics  = $prediction->metrics  ?? [];

                // 4. Harga terakhir dari price_histories
                $latestPrice = PriceHistory::where('commodity_id', $selectedId)
                    ->orderBy('date', 'desc')
                    ->first(['harga_sekarang', 'date', 'satuan']);

                $hargaKini   = $latestPrice ? (float) $latestPrice->harga_sekarang : ($prediction->current_price ?? 0);
                $satuan      = $latestPrice->satuan ?? $commodity->unit ?? 'kg';

                // 5. Hitung estimasi & tren dari results (mirror Flask payload)
                if (!empty($results)) {
                    $prices30 = array_slice(array_column($results, 'predicted_price'), 0, 30);
                    $estimasiHarga = round(end($prices30));                          // harga hari ke-30
                    $avg7d         = count($prices30) >= 7
                        ? round(array_sum(array_slice($prices30, 0, 7)) / 7)
                        : round($prices30[0] ?? $hargaKini);

                    $trenPersen = $hargaKini > 0
                        ? round((end($prices30) - $hargaKini) / $hargaKini * 100, 1)
                        : 0;
                }

                // 6. Kepercayaan = accuracy dari metrics
                $kepercayaan = isset($metrics['accuracy'])
                    ? round($metrics['accuracy'], 1)
                    : (isset($metrics['mape']) ? round(100 - $metrics['mape'], 1) : null);

                // 7. Data historis 30 hari untuk chart
                $histories = PriceHistory::where('commodity_id', $selectedId)
                    ->orderBy('date', 'asc')
                    ->get(['date', 'harga_sekarang'])
                    ->takeLast(30);

                $histTanggal = $histories->map(fn($h) => Carbon::parse($h->date)->format('Y-m-d'))->toArray();
                $histHarga   = $histories->map(fn($h) => (int) $h->harga_sekarang)->toArray();

                // 8. Data forecast 14 hari untuk chart
                $pred14     = array_slice($results, 0, 14);
                $predTanggal = array_column($pred14, 'date');
                $predHarga   = array_map('intval', array_column($pred14, 'predicted_price'));
                $predLower   = array_map(fn($r) => isset($r['lower']) ? (int)$r['lower'] : null, $pred14);
                $predUpper   = array_map(fn($r) => isset($r['upper']) ? (int)$r['upper'] : null, $pred14);

                $chartData = [
                    'hist_tanggal'  => $histTanggal,
                    'hist_harga'    => $histHarga,
                    'pred_tanggal'  => $predTanggal,
                    'pred_harga'    => $predHarga,
                    'ci_lower'      => $predLower,
                    'ci_upper'      => $predUpper,
                    'harga_kini'    => (int) $hargaKini,
                    'hist_avg'      => count($histHarga) > 0 ? (int)(array_sum($histHarga) / count($histHarga)) : 0,
                ];

                // 9. Buat tabel mingguan dari results (mirror Flask)
                $prediksiMingguan = $this->buildWeeklyTable($results, $hargaKini);

                $prediksiData = [
                    'commodity_name'    => $prediction->commodity_name,
                    'predicted_at'      => $prediction->predicted_at,
                    'horizon_days'      => $prediction->horizon_days,
                    'current_price'     => $hargaKini,
                    'satuan'            => $satuan,
                    'metrics'           => $metrics,
                    'recommendation'    => $metrics['recommendation']       ?? null,
                    'recommendation_score' => $metrics['recommendation_score'] ?? null,
                    'mae'               => $metrics['mae']                  ?? null,
                    'rmse'              => $metrics['rmse']                 ?? null,
                    'mape'              => $metrics['mape']                 ?? null,
                    'accuracy'          => $kepercayaan,
                ];
            }
        }

        return view('user.prediksi', compact(
            'komoditasList',
            'selectedId',
            'selectedKomoditas',
            'prediction',
            'prediksiData',
            'chartData',
            'prediksiMingguan',
            'estimasiHarga',
            'trenPersen',
            'kepercayaan'
        ));
    }

    /**
     * Buat tabel mingguan dari array results forecast.
     * Sinkron dengan Flask: Flask mengelompokkan per 7 hari.
     */
    private function buildWeeklyTable(array $results, float $hargaKini): array
    {
        $weeks  = [];
        $chunks = array_chunk($results, 7);

        foreach ($chunks as $i => $chunk) {
            $prices = array_column($chunk, 'predicted_price');
            $lower  = array_column($chunk, 'lower');
            $upper  = array_column($chunk, 'upper');

            $avgPrice = count($prices) > 0 ? array_sum($prices) / count($prices) : $hargaKini;
            $variasi  = (count($upper) > 0 && $upper[0] !== null && count($lower) > 0 && $lower[0] !== null)
                ? round(($upper[count($upper)-1] - $lower[0]) / 2)
                : round($avgPrice * 0.01);   // default ±1% jika tidak ada CI

            $firstDate = Carbon::parse($chunk[0]['date'] ?? now());
            $lastDate  = Carbon::parse($chunk[count($chunk)-1]['date'] ?? now());

            // Label bulan
            $bulan = $firstDate->locale('id')->translatedFormat('M');
            $weeks[] = [
                'minggu'   => 'W' . ($i + 1) . ' - ' . $bulan,
                'periode'  => $firstDate->format('d M') . ' – ' . $lastDate->format('d M Y'),
                'estimasi' => (int) round($avgPrice),
                'variasi'  => (int) $variasi,
                'delta_pct'=> $hargaKini > 0 ? round(($avgPrice - $hargaKini) / $hargaKini * 100, 1) : 0,
            ];
        }

        return $weeks;
    }
}
