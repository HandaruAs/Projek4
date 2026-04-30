<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PrediksiService;
use Illuminate\Http\Request;

class UserSimulasiController extends Controller
{
    private PrediksiService $prediksiService;

    public function __construct(PrediksiService $prediksiService)
    {
        $this->prediksiService = $prediksiService;
    }

    public function simulasi(Request $request)
    {
        // 1. Ambil daftar komoditas dari Flask
        $komoditasRaw = PrediksiService::getCommodities();

        // Format untuk dropdown view (view pakai $item->id dan $item->nama)
        $komoditas = collect($komoditasRaw)->map(fn($nama) => (object)[
            'id'   => $nama,
            'nama' => $nama,
        ])->toArray();

        // 2. Tentukan komoditas yang dipilih
        $selectedKomoditas = $request->get('komoditas', $komoditasRaw[0] ?? 'Beras Premium');
        $konsumsi          = (float) $request->get('konsumsi', 0.5);

        // Default values
        $hargaTerbaru  = 0;
        $hargaPrediksi = 0;
        $totalSekarang = 0;
        $totalPrediksi = 0;
        $selisih       = 0;
        $changePercent = 0;
        $wawasanAI     = null;

        try {
            // 3. Ambil rekomendasi dari Flask (sudah ada harga kini + prediksi 30 hari)
            $rek = $this->prediksiService->rekomendasi($selectedKomoditas, $konsumsi);

            $hargaTerbaru  = $rek['harga_kini']       ?? 0;
            $hargaPrediksi = $rek['harga_30hari_avg']  ?? $rek['harga_7hari'] ?? 0;

            // Total per bulan = konsumsi per minggu × 4 minggu × harga
            $totalSekarang = round($konsumsi * 4 * $hargaTerbaru);
            $totalPrediksi = round($konsumsi * 4 * $hargaPrediksi);
            $selisih       = $totalPrediksi - $totalSekarang;
            $changePercent = $totalSekarang > 0
                ? round(($selisih / $totalSekarang) * 100, 1)
                : 0;

            // Wawasan AI dari alasan Flask
            $alasan    = $rek['alasan'] ?? [];
            $rekomText = $rek['rekomendasi'] ?? '';
            $wawasanAI = !empty($alasan)
                ? "AI merekomendasikan: {$rekomText}. " . implode(' ', array_slice($alasan, 0, 1))
                : null;

        } catch (\Exception $e) {
            $wawasanAI = 'Gagal memuat data prediksi: ' . $e->getMessage();
        }

        return view('user.simulasi', compact(
            'komoditas',
            'selectedKomoditas',
            'konsumsi',
            'hargaTerbaru',
            'hargaPrediksi',
            'totalSekarang',
            'totalPrediksi',
            'selisih',
            'changePercent',
            'wawasanAI'
        ));
    }
}