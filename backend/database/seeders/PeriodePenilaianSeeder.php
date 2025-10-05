<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PeriodePenilaian;
use Carbon\Carbon;

class PeriodePenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_periode' => 'Semester 2 2025',
                'semester' => 2,
                'tahun' => 2025,
                'tanggal_mulai' => '2025-07-01',
                'tanggal_selesai' => '2025-12-31',
                'status' => 'belum_dibuka',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_periode' => 'Semester 1 2026',
                'semester' => 1,
                'tahun' => 2026,
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-06-30',
                'status' => 'belum_dibuka',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($data as $periode) {
            PeriodePenilaian::create($periode);
        }
    }
}