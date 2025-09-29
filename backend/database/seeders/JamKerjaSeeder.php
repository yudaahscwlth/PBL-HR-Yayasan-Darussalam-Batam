<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\JamKerja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JamKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        $jabatans = Jabatan::all();

        if ($jabatans->isEmpty() ) {
            $this->command->warn('Seeder dibatalkan: Jabatan kosong.');
            return;
        }

        foreach ($jabatans as $jabatan) {
            foreach ($hariList as $hari) {
                $isLibur = in_array($hari, ['sabtu', 'minggu']);

                JamKerja::create([
                    'id_jabatan' => $jabatan->id,
                    'hari' => $hari,
                    'jam_masuk' => $isLibur ? null : '07:30:00',
                    'jam_pulang' => $isLibur ? null : '16:00:00',
                    'is_libur' => $isLibur,
                    'keterangan' => $isLibur ? 'Hari libur' : 'Jam kerja normal',
                ]);
            }
        }
    }
}
