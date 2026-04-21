<?php

namespace App\Services;

use App\Models\Prediksi;
use App\Models\Harga;
use Illuminate\Support\Facades\Cache;

class PrediksiService
{
    public function getPrediksi(int $komoditasId, int $daerahId): array
    {
        return Cache::remember("prediksi.{$komoditasId}.{$daerahId}", 1800, function () use ($komoditasId, $daerahId) {
            $rows = Prediksi::where('komoditas_id', $komoditasId)
                ->where('daerah_id', $daerahId)
                ->where('tanggal_prediksi', '>=', now()->toDateString())
                ->orderBy('tanggal_prediksi')
                ->limit(7)
                ->get();

            $hargaBase = Harga::where('komoditas_id', $komoditasId)
                ->where('daerah_id', $daerahId)
                ->orderByDesc('tanggal')
                ->value('harga') ?? 0;

            // Fallback ke estimasi linear jika data prediksi belum ada
            if ($rows->isEmpty()) {
                return $this->estimasiLinear($komoditasId, $daerahId);
            }

            $besok    = $rows->get(0);
            $tigaHari = $rows->get(2) ?? $rows->last();
            $tujuhHari = $rows->get(6) ?? $rows->last();

            return [
                'besok'  => $this->formatPrediksi($besok?->harga_prediksi,    $hargaBase),
                '3hari'  => $this->formatPrediksi($tigaHari?->harga_prediksi,  $hargaBase),
                '7hari'  => $this->formatPrediksi($tujuhHari?->harga_prediksi, $hargaBase),
                'tren'   => $this->getTren($hargaBase, $tujuhHari?->harga_prediksi),
            ];
        });
    }

    private function formatPrediksi(?float $hargaPrediksi, float $hargaBase): array
    {
        if (!$hargaPrediksi || $hargaBase === 0.0) {
            return ['harga' => 'Rp —', 'persentase' => '—'];
        }

        $selisih    = $hargaPrediksi - $hargaBase;
        $persentase = round(($selisih / $hargaBase) * 100, 1);

        return [
            'harga'      => 'Rp ' . number_format($hargaPrediksi, 0, ',', '.'),
            'persentase' => ($persentase >= 0 ? '+' : '') . $persentase . '%',
            'raw'        => $hargaPrediksi,
        ];
    }

    private function getTren(float $hargaBase, ?float $hargaPrediksi): string
    {
        if (!$hargaPrediksi) return 'Data Tidak Tersedia';
        return $hargaPrediksi > $hargaBase ? 'Tren Naik Terdeteksi' : 'Tren Turun Terdeteksi';
    }

    private function estimasiLinear(int $komoditasId, int $daerahId): array
    {
        $data = Harga::where('komoditas_id', $komoditasId)
            ->where('daerah_id', $daerahId)
            ->orderByDesc('tanggal')
            ->limit(7)
            ->pluck('harga');

        if ($data->count() < 2) {
            return [
                'besok'  => ['harga' => 'Rp —', 'persentase' => '—'],
                '3hari'  => ['harga' => 'Rp —', 'persentase' => '—'],
                '7hari'  => ['harga' => 'Rp —', 'persentase' => '—'],
                'tren'   => 'Data Tidak Tersedia',
            ];
        }

        $hargaBase          = $data->first();
        $rataPerubahan      = ($data->first() - $data->last()) / max($data->count() - 1, 1);

        return [
            'besok'  => $this->formatPrediksi($hargaBase + $rataPerubahan * 1, $hargaBase),
            '3hari'  => $this->formatPrediksi($hargaBase + $rataPerubahan * 3, $hargaBase),
            '7hari'  => $this->formatPrediksi($hargaBase + $rataPerubahan * 7, $hargaBase),
            'tren'   => $this->getTren($hargaBase, $hargaBase + $rataPerubahan * 7),
        ];
    }
}