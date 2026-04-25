<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PrediksiService;
use Illuminate\Http\Request;

class PrediksiController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    // GET /admin/prediksi
    public function index(Request $request)
    {
        $komoditasList = PrediksiService::getCommodities();
        $selectedNama  = $request->get('komoditas', $komoditasList[0] ?? null);

        $prediksiData = null;
        $chartData    = null;

        if ($selectedNama) {
            try {
                $prediksiData = $this->prediksiService->generate($selectedNama, 30);

                $forecast  = $prediksiData['forecast']      ?? [];
                $tanggal   = $prediksiData['tanggal_pred']  ?? [];
                $ciLower   = $prediksiData['ci_lower']      ?? [];
                $ciUpper   = $prediksiData['ci_upper']      ?? [];
                $hargaKini = $prediksiData['harga_terakhir'] ?? 0;

                $chartData = [
                    'pred_tanggal' => array_slice($tanggal, 0, 14),
                    'pred_harga'   => array_slice($forecast, 0, 14),
                    'ci_lower'     => array_slice($ciLower, 0, 14),
                    'ci_upper'     => array_slice($ciUpper, 0, 14),
                    'harga_kini'   => $hargaKini,
                ];
            } catch (\Exception $e) {
                $prediksiData = ['error' => $e->getMessage()];
            }
        }
        // Ambil prediction history dari MongoDB
        $predictions = \App\Models\Prediction::orderBy('predicted_at', 'desc')
            ->paginate(10);

        // Konversi komoditasList (array string) ke format object yang diharapkan view
        $commodities = collect($komoditasList)->map(fn($nama) => (object)[
            'id'   => $nama,
            'name' => $nama,
        ])->toArray();

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
    // POST /admin/prediksi/generate
    public function generate(Request $request)
    {
        $request->validate([
            'komoditas' => 'required|string',
            'steps'     => 'nullable|integer|min:1|max:90',
        ]);

        $komoditas = $request->komoditas;
        $steps     = (int) ($request->steps ?? 30);

        try {
            // Ambil data dari Flask
            $data = $this->prediksiService->generate($komoditas, $steps);

            // Bangun results array (untuk tabel detail)
            $results = [];
            $tanggal  = $data['tanggal_pred'] ?? [];
            $forecast = $data['forecast']     ?? [];
            $ciLower  = $data['ci_lower']     ?? [];
            $ciUpper  = $data['ci_upper']     ?? [];

            foreach ($tanggal as $i => $tgl) {
                $results[] = [
                    'date'            => $tgl,
                    'predicted_price' => $forecast[$i] ?? 0,
                    'lower'           => $ciLower[$i]  ?? null,
                    'upper'           => $ciUpper[$i]  ?? null,
                ];
            }

            // Bangun metrics dari accuracy Flask
            $acc     = $data['accuracy'] ?? [];
            $metrics = [
                'mae'      => $acc['mae']   ?? null,
                'rmse'     => $acc['rmse']  ?? null,
                'mape'     => $acc['mape']  ?? null,
                'accuracy' => $acc['accuracy'] ?? null,
            ];

            // Hapus prediksi lama untuk komoditas + steps yang sama
            \App\Models\Prediction::where('commodity_name', $komoditas)
                ->where('horizon_days', $steps)
                ->delete();

            // Simpan ke MongoDB
            \App\Models\Prediction::create([
                'commodity_id'   => $komoditas, // pakai nama sebagai id karena tidak ada relasi
                'commodity_name' => $komoditas,
                'predicted_at'   => now(),
                'horizon_days'   => $steps,
                'current_price'  => $data['harga_terakhir'] ?? 0,
                'satuan'         => $data['satuan']   ?? 'kg',
                'kategori'       => $data['kategori'] ?? '',
                'tanggal_pred'   => $tanggal,
                'forecast'       => $forecast,
                'ci_lower'       => $ciLower,
                'ci_upper'       => $ciUpper,
                'results'        => $results,
                'metrics'        => $metrics,
            ]);

            return redirect()->route('prediksi.index', ['komoditas' => $komoditas])
                ->with('success', "Prediksi {$komoditas} berhasil digenerate dan disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate prediksi: ' . $e->getMessage());
        }
    }
    // POST /admin/prediksi/upload
    // Upload tidak lagi relevan — data dikelola Flask/MongoDB langsung
    // GET /admin/prediksi/export/{id}
    public function export(string $id)
    {
        $prediction = \App\Models\Prediction::find($id);

        if (!$prediction) {
            return back()->with('error', 'Data prediksi tidak ditemukan.');
        }

        $filename = 'prediksi_' . str_replace(' ', '_', $prediction->commodity_name) . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($prediction) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal', 'Prediksi Harga', 'CI Lower', 'CI Upper']);

            $tanggal  = $prediction->tanggal_pred ?? [];
            $forecast = $prediction->forecast     ?? [];
            $ciLower  = $prediction->ci_lower     ?? [];
            $ciUpper  = $prediction->ci_upper     ?? [];

            foreach ($tanggal as $i => $tgl) {
                fputcsv($file, [
                    $tgl,
                    $forecast[$i] ?? '',
                    $ciLower[$i]  ?? '',
                    $ciUpper[$i]  ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // GET /admin/prediksi/{id}
    // id di sini adalah nama komoditas (karena tidak pakai DB lokal)
    public function show(string $id, Request $request)
    {
        $komoditas = urldecode($id);
        $steps     = (int) $request->get('steps', 30);

        try {
            $prediksiData = $this->prediksiService->generate($komoditas, $steps);

            $forecast  = $prediksiData['forecast']      ?? [];
            $tanggal   = $prediksiData['tanggal_pred']  ?? [];
            $ciLower   = $prediksiData['ci_lower']      ?? [];
            $ciUpper   = $prediksiData['ci_upper']      ?? [];
            $hargaKini = $prediksiData['harga_terakhir'] ?? 0;

            $chartData = [
                'pred_tanggal' => array_slice($tanggal, 0, 14),
                'pred_harga'   => array_slice($forecast, 0, 14),
                'ci_lower'     => array_slice($ciLower, 0, 14),
                'ci_upper'     => array_slice($ciUpper, 0, 14),
                'harga_kini'   => $hargaKini,
            ];
        } catch (\Exception $e) {
            $prediksiData = ['error' => $e->getMessage()];
            $chartData    = null;
        }

        return view('admin.prediksi-detail', compact(
            'komoditas',
            'prediksiData',
            'chartData'
        ));
    }

    // DELETE /admin/prediksi/{id}
    // Tidak relevan lagi karena tidak ada DB lokal — redirect saja
    public function destroy(string $id)
    {
        return redirect()->route('prediksi.index')
            ->with('info', 'Data prediksi dikelola langsung oleh Flask ML.');
    }
}
