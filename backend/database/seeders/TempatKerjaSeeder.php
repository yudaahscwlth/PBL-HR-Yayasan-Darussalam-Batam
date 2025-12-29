<?php

namespace Database\Seeders;

use App\Models\TempatKerja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TempatKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya satu tempat kerja: Kantor Yayasan
        TempatKerja::create([
            'nama_tempat' => 'Kantor Yayasan',
            'latitude' => 1.050628779000409,
            'longitude' => 103.977905867479860,
        ]);
    }
}
