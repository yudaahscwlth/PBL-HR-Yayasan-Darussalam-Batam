<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TahunAjaran::create([
        'nama' => '2025/2026',
        'semester' => 'ganjil',
        'is_aktif' => '0',
    ]);
        TahunAjaran::create([
        'nama' => '2025/2026',
        'semester' => 'genap',
        'is_aktif' => '1',
    ]);

    }
}
