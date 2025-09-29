<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            'kepala yayasan',
            'direktur pendidikan',
            'kepala hrd',
            'staff hrd',
            'kepala departemen',
            'kepala sekolah',
            'tenaga pendidik',
        ];

        foreach ($jabatans as $nama) {
            Jabatan::create([
                'nama_jabatan' => $nama,
            ]);
        }
    }
}
