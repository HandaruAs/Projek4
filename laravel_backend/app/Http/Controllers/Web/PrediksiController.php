<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PrediksiService;
use App\Models\Prediction;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrediksiController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    /**
     * Hanya komoditas dengan ≥ 20 data historis yang layak diprediksi.
     */
    private function getEligibleCommodities(): array
    {
        try {
            $results = PriceHistory::raw(function ($collection) {
                return $collection->aggregate([
                    ['$group' => [
                        '_id'   => '$commodity_name',
                        'count' => ['$sum' => 1],
                    ]],
                    ['$match' => ['count' => ['$gte' => 20]]],
                    ['$sort'  => ['_id' => 1]],
                    ['$project' => ['_id' => 0, 'name' => '$_id']],
                ])->toArray();
            });
            return array_column($results, 'name');
        } catch (\Exception $e) {
            Log::error('getEligibleCommodities: ' . $e->getMessage());
            return [];
        }
    }

    // GET /admin/prediksi
    public function index(Request $request)
    {
        $komoditasList = $this->getEligibleCommodities();
        $selectedNama  = $request->get('komoditas', null);

        if ($selectedNama && $selectedNama !== 'all' && !in_array($selectedNama, $komoditasList)) {
            return redirect()->route('prediksi.index')
                ->with('error', 'Komoditas tidak memiliki cukup data (min 20 titik).');
        }

        $prediksiData = null;
        $chartData    = null;

        if ($selectedNama && $selectedNama !== 'all') {
            try {
                $prediksiData = $this->prediksiService->generate($selectedNama, 30);

                $payload   = $prediksiData;
                $forecast  = $payload['forecast']       ?? [];
                $tanggal   = $payload['tanggal_pred']   ?? [];
                $ciLower   = $payload['ci_lower']       ?? [];
                $ciUpper   = $payload['ci_upper']       ?? [];
                $hargaKini = $payload['harga_terakhir'] ?? 0;

                $chartData = [
                    'pred_tanggal' => array_slice($tanggal, 0, 14),
                    'pred_harga'   => array_slice($forecast, 0, 14),
                    'ci_lower'     => array_slice(is_array($ciLower) ? $ciLower : [], 0, 14),
                    'ci_upper'     => array_slice(is_array($ciUpper) ? $ciUpper : [], 0, 14),
                    'harga_kini'   => $hargaKini,
                ];
            } catch (\Exception $e) {
                $prediksiData = ['error' => $e->getMessage()];
            }
        }

        $predictions = Prediction::whereNotNull('accuracy_mape')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $commodities = array_map(fn($nama) => (object)[
            'id'   => $nama,
            'name' => $nama,
        ], $komoditasList);

        return view('admin.prediksi', compact(
            'komoditasList',
            'selectedNama',
            'prediksiData',
            'chartData',
            'commodities',
            'predictions'
        ));
    }

    // POST /admin/prediksi/generate
    public function generate(Request $request)
    {
        $request->validate([
            'komoditas' => 'required|string',
            'steps'     => 'nullable|integer|min:1|max:90',
        ]);

        $komoditas = $request->komoditas;
        $steps     = (int) ($request->steps ?? 30);
        $eligible  = $this->getEligibleCommodities();

        // ── ALL COMMODITY ──────────────────────────────────────────────
        if ($komoditas === 'all') {
            if (empty($eligible)) {
                return back()->with('error', 'Tidak ada komoditas yang memiliki cukup data historis (min 20 data).');
            }

            $berhasil = [];
            $gagal    = [];

            foreach ($eligible as $nama) {
                try {
                    $payload = $this->prediksiService->generate($nama, $steps);
                    $acc     = $payload['accuracy'] ?? [];

                    Prediction::where('commodity_name', $nama)
                        ->where('steps', $steps)
                        ->delete();

                    Prediction::create([
                        'commodity_name' => $nama,
                        'steps'          => $steps,
                        'created_at'     => now(),
                        'created_by'     => auth()->user()->name ?? 'laravel_web',
                        'status'         => 'completed',
                        'accuracy_mae'   => $acc['mae']  ?? null,
                        'accuracy_rmse'  => $acc['rmse'] ?? null,
                        'accuracy_mape'  => $acc['mape'] ?? null,
                        'payload'        => $payload,
                    ]);

                    $berhasil[] = $nama;
                } catch (\Exception $e) {
                    Log::error("Generate all - gagal untuk [{$nama}]: " . $e->getMessage());
                    $gagal[] = $nama;
                }
            }

            $totalBerhasil = count($berhasil);
            $totalGagal    = count($gagal);

            if ($totalBerhasil === 0) {
                return redirect()->route('prediksi.index')
                    ->with('error', 'Semua komoditas gagal di-generate. Periksa log untuk detail.');
            }

            $pesan = "Prediksi selesai: {$totalBerhasil} komoditas berhasil.";
            if ($totalGagal > 0) {
                $pesan .= " {$totalGagal} komoditas gagal: " . implode(', ', $gagal) . ".";
                return redirect()->route('prediksi.index')->with('success_modal', $pesan);
            }

            return redirect()->route('prediksi.index')->with('success', $pesan);
        }

        // ── SINGLE COMMODITY ───────────────────────────────────────────
        if (!in_array($komoditas, $eligible)) {
            return back()->with('error', 'Komoditas tidak memiliki cukup data historis (min 20 data).');
        }

        try {
            $payload = $this->prediksiService->generate($komoditas, $steps);
            $acc     = $payload['accuracy'] ?? [];

            $warning = null;
            if (empty($acc['mae'])) {
                $warning = "Metrik akurasi tidak tersedia. Data historis mungkin kurang dari 60 hari.";
            }

            Prediction::where('commodity_name', $komoditas)
                ->where('steps', $steps)
                ->delete();

            Prediction::create([
                'commodity_name' => $komoditas,
                'steps'          => $steps,
                'created_at'     => now(),
                'created_by'     => auth()->user()->name ?? 'laravel_web',
                'status'         => 'completed',
                'accuracy_mae'   => $acc['mae']  ?? null,
                'accuracy_rmse'  => $acc['rmse'] ?? null,
                'accuracy_mape'  => $acc['mape'] ?? null,
                'payload'        => $payload,
            ]);

            if ($warning) {
                return redirect()->route('prediksi.index', ['komoditas' => $komoditas])
                    ->with('warning', $warning);
            }

            return redirect()->route('prediksi.index', ['komoditas' => $komoditas])
                ->with('success', "Prediksi {$komoditas} berhasil digenerate.");
        } catch (\Exception $e) {
            Log::error("Generate prediksi error: " . $e->getMessage());
            return back()->with('error', 'Gagal generate prediksi: ' . $e->getMessage());
        }
    }

    // GET /admin/prediksi/export/{id}
    public function export(string $id)
    {
        $prediction = Prediction::find($id);
        if (!$prediction) {
            return back()->with('error', 'Data prediksi tidak ditemukan.');
        }

        $filename = 'prediksi_'
            . str_replace(['/', '\\'], '_', $prediction->commodity_name)
            . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($prediction) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal', 'Prediksi Harga', 'CI Lower', 'CI Upper']);

            $payload  = $prediction->payload ?? [];
            $tanggal  = $payload['tanggal_pred'] ?? [];
            $forecast = $payload['forecast']     ?? [];
            $ciLower  = $payload['ci_lower']     ?? [];
            $ciUpper  = $payload['ci_upper']     ?? [];

            foreach ($tanggal as $i => $tgl) {
                fputcsv($file, [
                    $tgl,
                    $forecast[$i] ?? '',
                    is_array($ciLower) ? ($ciLower[$i] ?? '') : '',
                    is_array($ciUpper) ? ($ciUpper[$i] ?? '') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // GET /admin/prediksi/{id}
    public function show(string $id)
    {
        $prediction = Prediction::find($id);
        if (!$prediction) {
            return redirect()->route('prediksi.index')
                ->with('error', 'Data prediksi tidak ditemukan.');
        }
        return view('admin.prediksi-detail', compact('prediction'));
    }

    // DELETE /admin/prediksi/{id}
    public function destroy(string $id)
    {
        $prediction = Prediction::find($id);
        if ($prediction) {
            $prediction->delete();
            return redirect()->route('prediksi.index')
                ->with('success', 'Data prediksi berhasil dihapus.');
        }
        return redirect()->route('prediksi.index')
            ->with('error', 'Data prediksi tidak ditemukan.');
    }
}
