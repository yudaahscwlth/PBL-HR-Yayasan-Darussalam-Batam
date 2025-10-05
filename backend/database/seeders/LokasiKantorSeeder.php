<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\LokasiKantor;

class LokasiKantorSeeder extends Seeder
{
    public function run()
    {
        LokasiKantor::create([
            'nama_lokasi' => 'Kantor Pusat Yayasan Darussalam',
            'latitude' => -6.2088, // Contoh koordinat Jakarta
            'longitude' => 106.8456,
            'radius_meter' => 100
        ]);
        
        // Tambahkan lokasi kantor lain jika ada
        LokasiKantor::create([
            'nama_lokasi' => 'Kantor Cabang Batam',
            'latitude' => 1.1304, // Koordinat Batam
            'longitude' => 104.0533,
            'radius_meter' => 150
        ]);
    }
}
