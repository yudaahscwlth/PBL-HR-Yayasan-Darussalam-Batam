<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartemenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departemen')->insert([
            ['nama_departemen' => 'Teknologi Informasi', 'kepala_departemen' => null],
            ['nama_departemen' => 'Keuangan', 'kepala_departemen' => null],
            ['nama_departemen' => 'Sumber Daya Manusia', 'kepala_departemen' => null],
        ]);
    }
}
