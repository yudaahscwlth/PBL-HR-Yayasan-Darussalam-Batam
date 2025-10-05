<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisCuti = [
            [
                'nama_jenis_cuti' => 'Cuti Tahunan',
                'deskripsi' => 'Cuti yang diberikan setiap tahun kepada pegawai',
                'max_hari_cuti' => 12,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jenis_cuti' => 'Cuti Sakit',
                'deskripsi' => 'Cuti yang diberikan ketika pegawai sakit',
                'max_hari_cuti' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jenis_cuti' => 'Cuti Melahirkan',
                'deskripsi' => 'Cuti yang diberikan untuk pegawai wanita yang melahirkan',
                'max_hari_cuti' => 90,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jenis_cuti' => 'Cuti Menikah',
                'deskripsi' => 'Cuti yang diberikan ketika pegawai menikah',
                'max_hari_cuti' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jenis_cuti' => 'Cuti Kematian Keluarga',
                'deskripsi' => 'Cuti yang diberikan ketika ada anggota keluarga yang meninggal',
                'max_hari_cuti' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jenis_cuti' => 'Cuti Darurat',
                'deskripsi' => 'Cuti yang diberikan untuk keperluan mendesak/darurat',
                'max_hari_cuti' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('jenis_cuti')->insert($jenisCuti);
    }
}