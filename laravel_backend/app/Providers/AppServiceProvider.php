<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use App\Models\Commodity;
use App\Models\Prediction;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Exception;

class PrediksiService
{
    private string $scriptPath;
    private string $tempDir;

    public function __construct()
    {
        $this->scriptPath = base_path('scripts/Holt_Winter.py');
        $this->tempDir    = storage_path('app/prediksi_temp');
    }

    /**
     * Generate prediction untuk satu komoditas menggunakan Holt-Winters.
     * Sinkron dengan Flask: POST /api/admin/run_prediksi
     *
     * Payload yang disimpan ke MongoDB predictions identik dengan
     * struktur yang diproduksi Flask (tanggal_pred, forecast, ci_lower,
     * ci_upper, accuracy, satuan, harga_terakhir, kategori).
     */
    public function generate(string $commodityId, int $steps = 30): array
    {
        // 1. Resolve ObjectId
        $rawId = $commodityId;
        if (preg_match('/^[0-9a-fA-F]{24}$/', $commodityId)) {
            $objectIdClass = '\\MongoDB\\BSON\\ObjectId';
            if (class_exists($objectIdClass)) {
                $rawId = new $objectIdClass($commodityId);
            }
        }

        // 2. Ambil nama komoditas & harga terakhir
        $latestPrice = PriceHistory::where('commodity_id', $rawId)
            ->orderBy('date', 'desc')
            ->first(['commodity_name', 'harga_sekarang', 'satuan', 'category']);

        if (!$latestPrice) {
            throw new Exception("Tidak ada data harga untuk commodity_id: {$commodityId}");
        }

        $commodityName = $latestPrice['commodity_name'];
        $currentPrice  = (float) $latestPrice['harga_sekarang'];
        $satuan        = $latestPrice['satuan'] ?? 'kg';
        $kategori      = $latestPrice['category'] ?? '';

        // 3. Pastikan direktori temp ada
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $outputFile = $this->tempDir . '/' . uniqid('pred_') . '.json';

        // 4. Jalankan Holt-Winters Python script
        $command = sprintf(
            'python %s %s %d add add 7 1 %s',
            escapeshellarg($this->scriptPath),
            escapeshellarg($commodityId),
            $steps,
            escapeshellarg($outputFile)
        );

        $result = Process::run($command);

        if (!$result->successful()) {
            Log::error('Holt_Winter.py failed: ' . $result->errorOutput());
            throw new Exception('Prediction model failed: ' . $result->errorOutput());
        }

        if (!file_exists($outputFile)) {
            throw new Exception('Output file tidak ditemukan setelah model selesai.');
        }

        $raw        = file_get_contents($outputFile);
        $outputData = json_decode($raw, true);
        @unlink($outputFile);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Output model tidak valid: ' . json_last_error_msg());
        }

        if (empty($outputData['forecast'])) {
            throw new Exception('Tidak ada data forecast dalam output model.');
        }

        // 5. Map results — sertakan lower/upper (mirror Flask ci_lower, ci_upper)
        $results = collect($outputData['forecast'])->map(function ($item) {
            return [
                'date'            => $item['date'],
                'predicted_price' => (int) round($item['predicted_price']),
                'lower'           => isset($item['lower']) ? (int) round($item['lower'])  : null,
                'upper'           => isset($item['upper']) ? (int) round($item['upper'])  : null,
            ];
        })->toArray();

        // 6. Hitung metrics (MAE, RMSE, MAPE) dan rekomendasi
        $metrics = $this->buildMetrics($outputData, $results, $currentPrice);

        // 7. Tanggal prediksi — mirror Flask: tanggal_pred[]
        $tanggalPred = array_column($results, 'date');

        // 8. Hapus cache lama, simpan baru (identik dengan Flask delete_many + insert_one)
        Prediction::where('commodity_id', $commodityId)
            ->where('horizon_days', $steps)
            ->delete();

        return [
            'commodity_id'   => $commodityId,
            'commodity_name' => $commodityName,
            'predicted_at'   => now(),
            'horizon_days'   => $steps,
            'current_price'  => $currentPrice,
            'satuan'         => $satuan,
            'kategori'       => $kategori,
            // tanggal_pred, forecast, ci_lower, ci_upper — sinkron Flask payload
            'tanggal_pred'   => $tanggalPred,
            'forecast'       => array_column($results, 'predicted_price'),
            'ci_lower'       => array_column($results, 'lower'),
            'ci_upper'       => array_column($results, 'upper'),
            // results array untuk Laravel (dipakai controller show/detail)
            'results'        => $results,
            'metrics'        => $metrics,
        ];
    }

    /**
     * Bangun array metrics lengkap.
     * Sinkron Flask: compute_accuracy() + buat_rekomendasi()
     */
    private function buildMetrics(array $outputData, array $results, float $currentPrice): array
    {
        $predictions = array_column($results, 'predicted_price');

        // Akurasi dari script Python (MAE, RMSE, MAPE)
        $mae   = $outputData['mae']   ?? null;
        $rmse  = $outputData['rmse']  ?? null;
        $mape  = $outputData['mape']  ?? null;
        $alpha = $outputData['alpha'] ?? null;
        $beta  = $outputData['beta']  ?? null;
        $gamma = $outputData['gamma'] ?? null;

        // accuracy = 100 - MAPE (sinkron Flask)
        $accuracy = isset($mape) ? round(max(0, 100 - $mape), 1) : null;

        // Rekomendasi sinkron Flask: buat_rekomendasi() logic
        $rec = $this->computeRecommendation($predictions, $currentPrice);

        return array_merge([
            'mae'      => $mae   !== null ? round($mae)  : null,
            'rmse'     => $rmse  !== null ? round($rmse) : null,
            'mape'     => $mape  !== null ? round($mape, 2) : null,
            'accuracy' => $accuracy,
            'alpha'    => $alpha,
            'beta'     => $beta,
            'gamma'    => $gamma,
            'note'     => 'Holt-Winters, walk-forward 80/20 split',
        ], $rec);
    }

    /**
     * Hitung rekomendasi pembelian — sinkron persis Flask buat_rekomendasi().
     *
     * Scoring:
     *  skor awal = 50
     *  d_d30 > 5%   → -25   | < -5%  → +25
     *  d_d30 > 2%   → -12   | < -2%  → +12
     *  d_d7  > 3%   → -10   | < -3%  → +10
     *  skor <= 30  → BELI SEKARANG
     *  skor <= 50  → BELI SEGERA
     *  skor <= 68  → TUNGGU DULU
     *  else        → TUNDA PEMBELIAN
     */
    private function computeRecommendation(array $prices, float $currentPrice): array
    {
        if (empty($prices) || $currentPrice <= 0) {
            return [
                'recommendation_score' => 50,
                'score'                => 50,
                'recommendation'       => 'TUNGGU DULU',
                'warna'                => 'wait',
                'emoji'                => '⏳',
                'delta_pct_30'         => 0,
                'delta_pct_7'          => 0,
                'harga_30hari'         => $currentPrice,
                'harga_7hari'          => $currentPrice,
            ];
        }

        $h_kini = $currentPrice;
        $h_d7   = isset($prices[6])  ? (float) $prices[6]  : $h_kini;
        $h_d30  = isset($prices[29]) ? (float) $prices[29] : (float) end($prices);

        $d_d7  = ($h_d7  - $h_kini) / $h_kini * 100;
        $d_d30 = ($h_d30 - $h_kini) / $h_kini * 100;

        // Skor (identik Flask)
        $skor = 50;
        if ($d_d30 > 5)      $skor -= 25;
        elseif ($d_d30 > 2)  $skor -= 12;
        elseif ($d_d30 < -5) $skor += 25;
        elseif ($d_d30 < -2) $skor += 12;

        if ($d_d7 > 3)       $skor -= 10;
        elseif ($d_d7 < -3)  $skor += 10;

        $skor = (int) max(0, min(100, $skor));

        // Label (identik Flask)
        [$rek, $warna, $emoji] = match (true) {
            $skor <= 30 => ['BELI SEKARANG',   'buy',      '🛒'],
            $skor <= 50 => ['BELI SEGERA',      'buy_soon', '⚡'],
            $skor <= 68 => ['TUNGGU DULU',      'wait',     '⏳'],
            default     => ['TUNDA PEMBELIAN',  'hold',     '📉'],
        };

        return [
            'recommendation_score' => $skor,
            'score'                => $skor,
            'recommendation'       => $rek,
            'warna'                => $warna,
            'emoji'                => $emoji,
            'delta_pct_7'          => round($d_d7,  2),
            'delta_pct_30'         => round($d_d30, 2),
            'harga_7hari'          => (int) round($h_d7),
            'harga_30hari'         => (int) round($h_d30),
        ];
    }

    /**
     * GET /api/predictions  — ambil daftar prediksi terbaru.
     * Sinkron Flask: GET /api/admin/prediction_logs
     */
    public function getLatestPredictions(int $perPage = 10, int $page = 1)
    {
        return Prediction::orderBy('predicted_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Ambil semua komoditas (untuk dropdown generate form).
     */
    public static function getCommodities(): array
    {
        return Commodity::orderBy('name', 'asc')
            ->get(['_id', 'name'])
            ->map(fn($c) => [
                'id'   => (string) $c->_id,
                'name' => $c->name,
            ])
            ->toArray();
    }

    /**
     * Ambil prediksi terbaru per komoditas — sinkron Flask GET /api/dashboard.
     * Berguna untuk summary card di halaman admin dashboard.
     */
    public function getDashboardSummary(): array
    {
        $commodities = Commodity::all(['_id', 'name', 'unit']);
        $result      = [];

        foreach ($commodities as $c) {
            $pred = Prediction::latestByCommodity((string) $c->_id)->first();

            if (!$pred) {
                continue;
            }

            $metrics  = $pred->metrics  ?? [];
            $results  = $pred->results  ?? [];
            $nextDay  = $results[0]     ?? null;

            // delta 7 hari (sinkron Flask delta_pred)
            $prices7  = array_slice(array_column($results, 'predicted_price'), 0, 7);
            $avg7     = count($prices7) > 0 ? array_sum($prices7) / count($prices7) : $pred->current_price;
            $deltaPred = $pred->current_price > 0
                ? round(($avg7 - $pred->current_price) / $pred->current_price * 100, 1)
                : 0;

            $status = $this->statusLabel(0, $deltaPred); // delta_7 tidak ada, pakai delta_pred

            $result[] = [
                'komoditas'   => $pred->commodity_name,
                'kategori'    => $pred->kategori ?? '',
                'satuan'      => $pred->satuan   ?? $c->unit ?? 'kg',
                'harga_kini'  => (int) $pred->current_price,
                'delta_pred'  => $deltaPred,
                'pred_7hari'  => (int) round($avg7),
                'status'      => $status,
                'predicted_at'=> $pred->predicted_at,
                'accuracy'    => $metrics['accuracy'] ?? null,
                'recommendation' => $metrics['recommendation'] ?? null,
            ];
        }

        return $result;
    }

    /** Sinkron Flask: _status() */
    private function statusLabel(float $d7, float $dp): string
    {
        if ($d7 > 5 || $dp > 5)   return 'naik_signifikan';
        if ($d7 > 2 || $dp > 2)   return 'naik';
        if ($d7 < -5 || $dp < -5) return 'turun_signifikan';
        if ($d7 < -2 || $dp < -2) return 'turun';
        return 'stabil';
    }
}
