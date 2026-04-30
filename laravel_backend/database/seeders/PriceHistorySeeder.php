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

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            $this->command->error('Empty or invalid CSV');
            return;
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue; // Skip invalid rows

            $tanggal = trim($row[0]);
            $komoditas = trim($row[1]);
            $satuan = trim($row[2]);
            $harga_lama_str = isset($row[3]) ? trim($row[3]) : '0';
            $harga_sekarang_str = isset($row[4]) ? trim($row[4]) : '0';

            if (empty($tanggal) || empty($komoditas)) continue;

            $harga_sekarang = (float) str_replace(',', '', $harga_sekarang_str);
            $harga_lama = (float) str_replace(',', '', $harga_lama_str);
            
            $commodity = Commodity::firstOrCreate(
                ['name' => $komoditas],
                ['unit' => $satuan ?: 'kg', 'stok_unit' => $satuan ?: 'kg']
            );

            PriceHistory::updateOrCreate(
                [
                    'commodity_id' => $commodity->_id,
                    'date' => $tanggal,
                ],
                [
                    'harga_sekarang' => $harga_sekarang,
                    'harga_lama' => $harga_lama,
                    'satuan' => $satuan ?: 'kg',
                    'commodity_name' => $komoditas,
                    'category' => 'Pangan',
                ]
            );
        }
        fclose($handle);
        $this->command->info('✅ PriceHistory seeded successfully!');

        $this->command->info('Data harga dari sample CSV berhasil di-import!');
    }
}

