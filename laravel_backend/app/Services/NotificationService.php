<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    // Dipanggil saat admin simpan prediksi baru
    public static function prediksiBaruDibuat(string $commodity, string $detail): void
    {
        Notification::create([
            'user_id'   => null, // broadcast semua
            'title'     => "Prediksi {$commodity} Tersedia",
            'body'      => $detail,
            'type'      => 'prediction',
            'commodity' => $commodity,
            'meta'      => ['tabIndex' => 1],
            'is_read_by' => [],
        ]);
    }

    // Dipanggil saat harga berubah signifikan (bisa dari scheduler)
    public static function alertHarga(string $commodity, float $persen, bool $naik): void
    {
        $arah = $naik ? 'naik' : 'turun';
        Notification::create([
            'user_id'   => null,
            'title'     => "Harga {$commodity} " . ucfirst($arah) . "!",
            'body'      => "Harga {$commodity} {$arah} " . abs($persen) . "% dalam 7 hari terakhir.",
            'type'      => 'price_alert',
            'commodity' => $commodity,
            'meta'      => ['tabIndex' => 0],
            'is_read_by' => [],
        ]);
    }

    // Dipanggil manual atau scheduler mingguan
    public static function simulasiTersedia(): void
    {
        Notification::create([
            'user_id'   => null,
            'title'     => 'Simulasi Anggaran Tersedia',
            'body'      => 'Coba simulasi anggaran belanja untuk komoditas minggu ini.',
            'type'      => 'simulation',
            'commodity' => null,
            'meta'      => ['tabIndex' => 3],
            'is_read_by' => [],
        ]);
    }
}