<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Services\PrediksiService;
use App\Models\Prediction;
use App\Models\PriceHistory;
use App\Models\Commodity;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrediksiController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    /**
     * GET /admin/prediksi
     * Sinkron Flask: GET /api/admin/prediction_logs
     */
    public function index(Request $request)
    {
        $user        = session('user');
        $predictions = $this->prediksiService->getLatestPredictions(10, $request->get('page', 1));
        $commodities = PrediksiService::getCommodities();

        return view('admin.prediksi', compact('user', 'predictions', 'commodities'));
    }

    /**
     * POST /admin/prediksi/generate
     * Sinkron Flask: POST /api/admin/run_prediksi
     *
     * - Hapus prediksi lama untuk commodity+steps yang sama
     * - Generate via Holt-Winters
     * - Simpan fields: tanggal_pred, forecast, ci_lower, ci_upper, satuan, kategori, metrics
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

            // Simpan ke MongoDB (PrediksiService sudah delete cache lama)
            Prediction::create($predictionData);

            return redirect()->route('prediksi.index')
                ->with('success', "Prediksi untuk {$predictionData['commodity_name']} berhasil digenerate!");

        } catch (\Exception $e) {
            Log::error('Prediction generate failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal generate prediksi: ' . $e->getMessage());
        }
    }

    /**
     * POST /admin/prediksi/upload
     * Import data harga dari CSV/XLSX ke price_histories.
     * Sinkron Flask: langsung insert ke col_price (price_histories).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            $rows = $ext === 'csv'
                ? $this->parseCsv($file->getRealPath())
                : $this->parseXlsx($file->getRealPath());

            $inserted = 0;
            $skipped  = 0;
            $errors   = [];

            foreach ($rows as $i => $row) {
                $lineNum = $i + 2;

                // Kolom wajib — sinkron Flask: commodity_name, harga_sekarang, date
                if (empty($row['commodity_name']) || empty($row['harga_sekarang']) || empty($row['date'])) {
                    $errors[] = "Baris {$lineNum}: kolom wajib (commodity_name, harga_sekarang, date) tidak lengkap.";
                    $skipped++;
                    continue;
                }

                $commodity = Commodity::where('name', trim($row['commodity_name']))->first();

                try {
                    $date = Carbon::parse($row['date']);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$lineNum}: format tanggal tidak valid ({$row['date']}).";
                    $skipped++;
                    continue;
                }

                $hargaSekarang = (float) str_replace([','], ['.'], preg_replace('/[^0-9,]/', '', $row['harga_sekarang']));
                $hargaLama     = isset($row['harga_lama'])
                    ? (float) str_replace([','], ['.'], preg_replace('/[^0-9,]/', '', $row['harga_lama']))
                    : 0;
                $selisih = $hargaSekarang - $hargaLama;
                $persen  = $hargaLama > 0 ? round(($selisih / $hargaLama) * 100, 2) : 0;

                PriceHistory::create([
                    'commodity_id'   => $commodity?->_id,
                    'commodity_name' => trim($row['commodity_name']),
                    'category'       => $commodity?->category ?? ($row['category'] ?? null),
                    'date'           => $date,
                    'satuan'         => $row['satuan'] ?? 'kg',
                    'harga_lama'     => $hargaLama,
                    'harga_sekarang' => $hargaSekarang,
                    'selisih'        => $selisih,
                    'persen'         => $persen,
                    'source'         => 'import_csv',
                ]);

                $inserted++;
            }

            $message = "Import selesai: {$inserted} data berhasil dimasukkan.";
            if ($skipped > 0) {
                $message .= " {$skipped} baris dilewati.";
            }

            return redirect()->route('prediksi.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            Log::error('CSV Upload failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    /**
     * GET /admin/prediksi/{id}
     * Tampilkan detail prediksi dengan payload lengkap.
     */
    public function show(string $id)
    {
        $user       = session('user');
        $prediction = Prediction::findOrFail($id);

        return view('admin.prediksi-detail', compact('user', 'prediction'));
    }

    /**
     * DELETE /admin/prediksi/{id}
     */
    public function destroy(string $id)
    {
        Prediction::findOrFail($id)->delete();

        return redirect()->route('prediksi.index')
            ->with('success', 'Prediksi berhasil dihapus.');
    }

    /**
     * GET /admin/prediksi/{id}/export
     * Export hasil prediksi ke CSV.
     * Sinkron Flask: format kolom tanggal, predicted_price, lower, upper.
     */
    public function export(string $id)
    {
        $prediction = Prediction::findOrFail($id);

        // Gunakan results[] jika ada, fallback ke tanggal_pred + forecast
        $results = $prediction->results ?? [];
        if (empty($results) && !empty($prediction->tanggal_pred)) {
            $tanggal = $prediction->tanggal_pred;
            $forecast = $prediction->forecast;
            $lower    = $prediction->ci_lower;
            $upper    = $prediction->ci_upper;
            foreach ($tanggal as $i => $tgl) {
                $results[] = [
                    'date'            => $tgl,
                    'predicted_price' => $forecast[$i] ?? null,
                    'lower'           => $lower[$i]    ?? null,
                    'upper'           => $upper[$i]    ?? null,
                ];
            }
        }

        $filename = 'prediksi_' . str_replace(' ', '_', $prediction->commodity_name)
                  . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($results, $prediction) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));  // BOM UTF-8
            fputcsv($handle, ['Tanggal', 'Harga Prediksi (Rp)', 'Batas Bawah (Rp)', 'Batas Atas (Rp)']);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['predicted_price'],
                    $row['lower'] ?? '',
                    $row['upper'] ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $handle  = fopen($path, 'r');
        $headers = null;

        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                $headers = array_map(fn($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $line);
                continue;
            }
            if (count($line) !== count($headers)) continue;
            $rows[] = array_combine($headers, $line);
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \Exception('PhpSpreadsheet tidak tersedia. Jalankan: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        if (empty($rows)) return [];

        $headers = array_map('trim', array_shift($rows));
        $result  = [];

        foreach ($rows as $row) {
            if (count($row) < count($headers)) continue;
            $result[] = array_combine($headers, array_slice($row, 0, count($headers)));
        }

        return $result;
    }
}
=======

class PrediksiController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user');
        if (!$user) return redirect('/login');
        if ($user->role !== 'admin') return redirect('/dashboard');
        return $user;
    }

    /** GET /admin/prediksi */
    public function index()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $predictions = Prediction::with('commodity')
        //                  ->orderBy('created_at','desc')->paginate(10);

        return view('admin.prediksi', compact('user'));
    }

    /** POST /admin/prediksi/generate */
    public function generate(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'commodity_id' => 'required',
            'region'       => 'required|string',
            'period'       => 'required|string',
            'model'        => 'required|string',
        ]);

        // $result = PredictionService::generate($request->all());
        // Prediction::create($result);

        return redirect('/admin/prediksi')->with('success', 'Prediksi berhasil digenerate.');
    }

    /** GET /admin/prediksi/{id} */
    public function show(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // $prediction = Prediction::with('commodity')->findOrFail($id);

        return view('admin.prediksi-detail', compact('user'));
    }

    /** DELETE /admin/prediksi/{id} */
    public function destroy(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // Prediction::findOrFail($id)->delete();

        return redirect('/admin/prediksi')->with('success', 'Prediksi berhasil dihapus.');
    }
}
>>>>>>> fd823bc0833f5e144f68f61a74f5531fc4687a14
