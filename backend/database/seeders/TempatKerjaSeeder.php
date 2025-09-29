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
        $tempatKerjaList = [
            [
                'nama_tempat' => 'Kantor Yayasan',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
            ],
            [
                'nama_tempat' => 'Unit TK Islam',
                'latitude' => -6.200100,
                'longitude' => 106.816700,
            ],
            [
                'nama_tempat' => 'Unit SD Islam',
                'latitude' => -6.200500,
                'longitude' => 106.816800,
            ],
            [
                'nama_tempat' => 'Unit SMP Islam',
                'latitude' => -6.200900,
                'longitude' => 106.817000,
            ],
            [
                'nama_tempat' => 'Unit SMA Islam',
                'latitude' => -6.201300,
                'longitude' => 106.817200,
            ],
            [
                'nama_tempat' => 'Unit SMK Teknologi',
                'latitude' => -6.201700,
                'longitude' => 106.817400,
            ],
            [
                'nama_tempat' => 'Poltek',
                'latitude' => 1.1189457625979018,
                'longitude' => 104.0483922269825,
            ],
            [
                'nama_tempat' => 'Xplay',
                'latitude' => 1.1434830726392244,
                'longitude' => 104.00289268015779,
            ],
        ];

        foreach ($tempatKerjaList as $tempat) {
            TempatKerja::create($tempat);
        }
    }
}
