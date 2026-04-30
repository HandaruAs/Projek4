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

        $komoditas = collect($komoditasRaw)->map(fn($nama) => (object)[
            'id'   => $nama,
            'nama' => $nama,
        ])->toArray();

        // 2. Komoditas & konsumsi yang dipilih
        $selectedKomoditas = $request->get('komoditas', $komoditasRaw[0] ?? 'Beras Premium');
        $konsumsi          = (float) $request->get('konsumsi', 0.5);

        // Default values
        $hargaTerbaru   = 0;
        $hargaPrediksi  = 0;
        $harga7Hari     = 0;
        $totalSekarang  = 0;
        $totalPrediksi  = 0;
        $selisih        = 0;
        $changePercent  = 0;
        $wawasanAI      = null;
        $rekData        = null; // seluruh response Flask untuk blade

        try {
            // 3. Ambil rekomendasi dari Flask
            // Response Flask (buat_rekomendasi):
            //   rekomendasi, warna, emoji, headline, alasan,
            //   skor, harga_kini, harga_7hari, harga_30hari_avg,
            //   volatilitas, budget_sekarang, budget_7hari,
            //   konsumsi, satuan, delta_pct_7, delta_pct_30, chart
            $rek = $this->prediksiService->rekomendasi($selectedKomoditas, $konsumsi);

            $rekData = $rek;

            // Field names PERSIS sesuai Flask buat_rekomendasi():
            $hargaTerbaru  = $rek['harga_kini']        ?? 0;
            $harga7Hari    = $rek['harga_7hari']        ?? 0;
            // harga_30hari_avg = rata-rata historis 30 hari (bukan forecast)
            $hargaPrediksi = $rek['harga_30hari_avg']   ?? $harga7Hari;

            // Budget dari Flask langsung (sudah dihitung: konsumsi × harga)
            $totalSekarang = $rek['budget_sekarang']    ?? round($konsumsi * $hargaTerbaru);
            $totalPrediksi = $rek['budget_7hari']       ?? round($konsumsi * $harga7Hari);

            $selisih       = $totalPrediksi - $totalSekarang;
            $changePercent = $totalSekarang > 0
                ? round(($selisih / $totalSekarang) * 100, 1)
                : 0;

            // Wawasan AI dari headline + alasan Flask
            $headline  = $rek['headline']     ?? '';
            $alasan    = $rek['alasan']        ?? [];
            $rekomText = $rek['rekomendasi']   ?? '';
            $emoji     = $rek['emoji']         ?? '';

            $wawasanAI = $headline
                ?: ($rekomText ? "{$emoji} {$rekomText}." : null);

            if (!empty($alasan)) {
                $wawasanAI .= ' ' . implode(' ', array_slice($alasan, 0, 2));
            }

        } catch (\Exception $e) {
            $wawasanAI = 'Gagal memuat data prediksi: ' . $e->getMessage();
        }

        return view('user.simulasi', compact(
            'komoditas',
            'selectedKomoditas',
            'konsumsi',
            'hargaTerbaru',
            'hargaPrediksi',
            'harga7Hari',
            'totalSekarang',
            'totalPrediksi',
            'selisih',
            'changePercent',
            'wawasanAI',
            'rekData'       // seluruh response Flask — untuk badge rekomendasi, chart, dll
        ));
    }
}
