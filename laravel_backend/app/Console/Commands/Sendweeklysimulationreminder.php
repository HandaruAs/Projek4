<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;

class SendWeeklySimulationReminder extends Command
{
    protected $signature   = 'notifications:weekly-simulation';
    protected $description = 'Kirim pengingat simulasi anggaran mingguan ke semua user';

    public function handle(): void
    {
        Notification::create([
            'user_id'    => null, // broadcast ke semua user
            'title'      => 'Simulasi Anggaran Minggu Ini',
            'body'       => 'Coba simulasikan anggaran belanja minggu ini berdasarkan harga komoditas terbaru.',
            'type'       => 'simulation',
            'commodity'  => null,
            'meta'       => ['tabIndex' => 3],
            'is_read_by' => [],
        ]);

        $this->info('Notifikasi simulasi mingguan berhasil dikirim.');
    }
}