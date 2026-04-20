<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PrediksiService;
use App\Models\Prediction;
use App\Models\PriceHistory;
use App\Models\Commodity;
use Illuminate\Http\Request;
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
     * GET /admin/prediksi - List predictions
     * Sync with Flask: GET /api/admin/prediksi_logs
     */
    public function index(Request $request)
    {
        $user = session('user');
        $predictions = $this->prediksiService->getLatestPredictions(10, $request->get('page', 1));
        $commodities = PrediksiService::getCommodities();

        return view('admin.prediksi', compact('user', 'predictions', 'commodities'));
    }

    /**
     * POST /admin/prediksi/generate - Run Holt-Winters prediction
     */
    public function generate(Request $request)
    {
        $request->validate([
            'commodity_id' => 'required|string',
            'steps' => 'required|integer|min:1|max:90',
        ]);

        try {
            $predictionData = $this->prediksiService->generate(
                $request->commodity_id,
                (int) $request->steps
            );

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
     * POST /admin/prediksi/upload - Import CSV/XLSX price data
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            $rows = $ext === 'csv'
                ? $this->parseCsv($file->getRealPath())
                : $this->parseXlsx($file->getRealPath());

            $inserted = 0;
            $skipped = 0;
            $errors = [];

            foreach ($rows as $i => $row) {
                $lineNum = $i + 2;

                if (empty($row['commodity_name']) || empty($row['harga_sekarang']) || empty($row['date'])) {
                    $errors[] = "Baris {$lineNum}: kolom wajib tidak lengkap.";
                    $skipped++;
                    continue;
                }

                $commodity = Commodity::where('name', trim($row['commodity_name']))->first();

                try {
                    $date = Carbon::parse($row['date']);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$lineNum}: tanggal invalid ({$row['date']}).";
                    $skipped++;
                    continue;
                }

                $hargaSekarang = (float) str_replace(',', '.', preg_replace('/[^0-9,]/', '', $row['harga_sekarang']));
                $hargaLama = isset($row['harga_lama']) 
                    ? (float) str_replace(',', '.', preg_replace('/[^0-9,]/', '', $row['harga_lama'])) 
                    : 0;

                PriceHistory::create([
                    'commodity_id' => $commodity?->_id,
                    'commodity_name' => trim($row['commodity_name']),
                    'category' => $commodity?->category ?? ($row['category'] ?? null),
                    'date' => $date,
                    'satuan' => $row['satuan'] ?? 'kg',
                    'harga_lama' => $hargaLama,
                    'harga_sekarang' => $hargaSekarang,
                    'source' => 'import_csv',
                ]);

                $inserted++;
            }

            $message = "Import selesai: {$inserted} berhasil, {$skipped} dilewati.";

            return redirect()->route('prediksi.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            Log::error('CSV Upload failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * GET /admin/prediksi/{id} - Detail view
     */
    public function show(string $id)
    {
        $user = session('user');
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
            ->with('success', 'Prediksi dihapus.');
    }

    /**
     * GET /admin/prediksi/{id}/export - CSV export
     */
    public function export(string $id)
    {
        $prediction = Prediction::findOrFail($id);

        $results = $prediction->results ?? [];
        if (empty($results) && !empty($prediction->tanggal_pred)) {
            foreach ($prediction->tanggal_pred as $i => $tgl) {
                $results[] = [
                    'date' => $tgl,
                    'predicted_price' => $prediction->forecast[$i] ?? null,
                    'lower' => $prediction->ci_lower[$i] ?? null,
                    'upper' => $prediction->ci_upper[$i] ?? null,
                ];
            }
        }

        $filename = 'prediksi_' . str_replace(' ', '_', $prediction->commodity_name) . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($results) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['Tanggal', 'Prediksi (Rp)', 'Bawah (Rp)', 'Atas (Rp)']);
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

    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $headers = array_map(fn($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $headers ?? []);

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, $line);
            }
        }
        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \Exception('Install: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        
        $headers = array_map('trim', array_shift($rows) ?? []);
        $result = [];
        
        foreach ($rows as $row) {
            if (count($row) >= count($headers)) {
                $result[] = array_combine($headers, array_slice($row, 0, count($headers)));
            }
        }
        
        return $result;
    }
}

