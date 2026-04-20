<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Commodity;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class PrediksiService
{
    /**
     * Generate prediction for commodity using Holt-Winter script.
     * 
     * @param string $commodityId MongoDB _id
     * @param int $steps Forecast horizon (default 30)
     * @return array Data ready for Prediction::create()
     */
    public function generate(string $commodityId, int $steps = 30): array
    {
        // Validate commodity exists & has data
        $commodity = Commodity::findOrFail($commodityId);
        
        $latestPrice = PriceHistory::where('commodity_id', $commodityId)
            ->orderBy('date', 'desc')
            ->first(['harga_sekarang', 'satuan', 'commodity_name', 'category']);

        if (!$latestPrice) {
            throw new \Exception("No price history data for {$commodity->name}");
        }

        // Delete old predictions for same commodity/steps (cache clear)
        Prediction::where('commodity_id', $commodityId)
            ->where('horizon_days', $steps)
            ->delete();

        // Temp files
        $tempJson = storage_path("app/temp/holt_output_{$commodityId}.json");
        $tempScriptLog = storage_path("app/temp/holt_log_{$commodityId}.txt");

        File::ensureDirectoryExists(dirname($tempJson));

        // Default Holt-Winters params (tune later)
        $trend = 'add';
        $seasonal = 'add';
        $seasonalPeriods = 7; // weekly
        $damped = 0;

        // Build command: python3 scripts/Holt_Winter.py <args>
        $scriptPath = base_path('scripts/Holt_Winter.py');
        $command = sprintf(
            'cd %s && python3 %s %s %d %s %s %d %d %s 2>&1',
            escapeshellarg(base_path()),
            escapeshellarg($scriptPath),
            escapeshellarg($commodityId),
            $steps,
            escapeshellarg($trend),
            escapeshellarg($seasonal),
            $seasonalPeriods,
            $damped,
            escapeshellarg($tempJson)
        );

        Log::info('Running Holt-Winter: ' . $command);

        // Exec script
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        // Check script log
        $scriptLog = implode(PHP_EOL, $output);
        Log::info('Holt-Winter output: ' . $scriptLog);

        if ($returnVar !== 0) {
            throw new \Exception("Holt-Winter script failed. Code: {$returnVar}. Log: {$scriptLog}");
        }

        // Read JSON result
        if (!File::exists($tempJson)) {
            throw new \Exception("Holt-Winter output file not created: {$tempJson}");
        }

        $result = json_decode(File::get($tempJson), true);
        File::delete($tempJson);

        if (!$result || !isset($result['forecast'])) {
            throw new \Exception('Invalid Holt-Winter JSON output');
        }

        // Format for Prediction model (sync with fillable & Holt_Winter.py)
        $predictionData = [
            'commodity_id' => $commodityId,
            'commodity_name' => $latestPrice->commodity_name ?? $commodity->name,
            'predicted_at' => Carbon::now(),
            'horizon_days' => $steps,
            'current_price' => $latestPrice->harga_sekarang,
            'satuan' => $latestPrice->satuan ?? 'kg',
            'kategori' => $latestPrice->category ?? $commodity->category ?? 'Pangan',
            
            // Direct from script
            'mae' => $result['mae'] ?? null,
            'rmse' => $result['rmse'] ?? null,
            'mape' => $result['mape'] ?? null,
            
            // Build arrays sync Flask
            'tanggal_pred' => array_column($result['forecast'], 'date'),
            'forecast' => array_column($result['forecast'], 'predicted_price'),
            'ci_lower' => array_column($result['forecast'], 'lower'),
            'ci_upper' => array_column($result['forecast'], 'upper'),
            
            // Detailed results array (for show/export)
            'results' => $result['forecast'],
            
            // Metrics object
            'metrics' => [
                'mae' => $result['mae'] ?? 0,
                'rmse' => $result['rmse'] ?? 0,
                'mape' => $result['mape'] ?? 0,
                'accuracy' => 100 - ($result['mape'] ?? 0),
                'alpha' => $result['alpha'] ?? 0,
                'beta' => $result['beta'] ?? 0,
                'gamma' => $result['gamma'] ?? 0,
            ],
        ];

        Log::info('Prediction generated', ['commodity' => $commodity->name, 'steps' => $steps]);

        return $predictionData;
    }

    /**
     * Get latest predictions for admin index view.
     */
    public static function getLatestPredictions(int $limit = 10, int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Prediction::orderBy('predicted_at', 'desc')
            ->with('commodity')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get all commodities with price data for dropdown.
     */
    public static function getCommodities(): \Illuminate\Database\Eloquent\Collection
    {
        return Commodity::whereHas('priceHistories')->get(['_id', 'name']);
    }
}

