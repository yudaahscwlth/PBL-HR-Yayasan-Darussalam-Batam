<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jabatan')->insert([
            ['nama_jabatan' => 'Staff IT'],
            ['nama_jabatan' => 'Staff HRD'],
            ['nama_jabatan' => 'Kepala HRD'],
            ['nama_jabatan' => 'Kepala Departemen'],
        ]);
    }
}
