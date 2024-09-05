<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tahun;

class TahunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tahun::create([
            'tahun' => '2024',
            'is_active' => true,
        ]);

        Tahun::create([
            'tahun' => '2026',
            'is_active' => false,
        ]);
    }
}
