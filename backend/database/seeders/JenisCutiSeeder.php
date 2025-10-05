<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisCutiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jenis_cuti')->insert([
            [
                'id_jenis_cuti' => 1,
                'nama_jenis_cuti' => 'Cuti Tahunan',
                'max_hari_cuti' => 12
            ],
            [
                'id_jenis_cuti' => 2,
                'nama_jenis_cuti' => 'Cuti Sakit',
                'max_hari_cuti' => 10
            ],
            [
                'id_jenis_cuti' => 3,
                'nama_jenis_cuti' => 'Cuti Melahirkan',
                'max_hari_cuti' => 90
            ],
            [
                'id_jenis_cuti' => 4,
                'nama_jenis_cuti' => 'Cuti Besar',
                'max_hari_cuti' => 30
            ],
            [
                'id_jenis_cuti' => 5,
                'nama_jenis_cuti' => 'Cuti Penting',
                'max_hari_cuti' => 5
            ]
        ]);
    }
}
