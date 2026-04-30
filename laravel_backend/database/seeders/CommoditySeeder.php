<?php

namespace Database\Seeders;

use App\Models\Commodity;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommoditySeeder extends Seeder
{
    public function run(): void
    {
        $komoditas = [
            ['name' => 'Beras Merah', 'unit' => 'Kg', 'stok_unit' => 'kuintal', 'description' => 'Beras organik Jember'],
            ['name' => 'Gula Pasir', 'unit' => 'Kg', 'stok_unit' => 'sak', 'description' => 'Gula pasir putih'],
            ['name' => 'Minyak Goreng 1L', 'unit' => 'Liter', 'stok_unit' => 'drum', 'description' => 'Minyak goreng kemasan'],
            ['name' => 'Daging Ayam', 'unit' => 'Kg', 'stok_unit' => 'kg', 'description' => 'Daging ayam potong'],
            ['name' => 'Bawang Merah', 'unit' => 'Kg', 'stok_unit' => 'karung', 'description' => 'Bawang merah lokal Jember'],
        ];

        foreach ($komoditas as $item) {
            Commodity::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        $this->command->info('5 komoditas seeded!');
    }
}

