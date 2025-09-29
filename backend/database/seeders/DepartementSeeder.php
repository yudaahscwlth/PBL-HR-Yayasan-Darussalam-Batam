<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat departemen tanpa kepala departemen dulu
        // Kepala departemen akan diassign di StructuredAccountSeeder
        Departemen::create([
            'id_kepala_departemen' => null,
            'nama_departemen' => 'Teknologi Informasi',
        ]);

        Departemen::create([
            'id_kepala_departemen' => null,
            'nama_departemen' => 'Keuangan',
        ]);

        Departemen::create([
            'id_kepala_departemen' => null,
            'nama_departemen' => 'Sumber Daya Manusia',
        ]);
    }
}
