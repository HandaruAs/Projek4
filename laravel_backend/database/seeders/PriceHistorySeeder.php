<?php

namespace Database\Seeders;

use App\Models\PriceHistory;
use App\Models\Commodity;
use Illuminate\Database\Seeder;

class PriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('../flask_ml/data/raw/sample_pangan_makanan_jember_sample.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error('CSV file not found: ' . $csvPath);
            return;
        }

        $csv = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csv);

        foreach ($csv as $row) {
            $tanggal = $row[0];
            $komoditas = $row[1];
            $satuan = $row[2];
            $harga_sekarang = (float) str_replace(',', '', $row[4]);
            
            $commodity = Commodity::firstOrCreate(
                ['name' => $komoditas],
                ['unit' => $satuan, 'stok_unit' => $satuan]
            );

            PriceHistory::updateOrCreate(
                [
                    'commodity_id' => $commodity->_id,
                    'date' => $tanggal,
                ],
                [
                    'harga_sekarang' => $harga_sekarang,
                    'harga_lama' => (float) str_replace(',', '', $row[3]),
                    'satuan' => $satuan,
                    'commodity_name' => $komoditas,
                    'category' => 'Pangan',
                ]
            );
        }

        $this->command->info('Data harga dari sample CSV berhasil di-import!');
    }
}

