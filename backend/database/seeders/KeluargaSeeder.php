<?php

namespace Database\Seeders;

use App\Models\Keluarga;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KeluargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (User::with('profilePribadi', 'keluarga')->get() as $user) {
            if (!$user->profilePribadi) {
                continue;
            }

            // Idempotent: skip if keluarga already exists
            if ($user->keluarga && $user->keluarga->count() > 0) {
                continue;
            }

            $statusPernikahan = $user->profilePribadi->status_pernikahan;
            $jenisKelamin = $user->profilePribadi->jenis_kelamin; // 'pria' atau 'wanita'

            if ($statusPernikahan !== 'sudah nikah') {
                // Do not create family data for unmarried users
                continue;
            }

            // Tentukan hubungan pasangan berdasarkan gender user
            $hubunganPasangan = ($jenisKelamin === 'pria') ? 'istri' : 'suami';

            Keluarga::create([
                'id_user' => $user->id,
                'nama' => $hubunganPasangan === 'istri' ? 'Ayu' : 'Ahmad',
                'hubungan' => $hubunganPasangan,
                'tanggal_lahir' => now()->subYears(rand(25, 45))->subDays(rand(0, 365)),
                'pekerjaan' => $hubunganPasangan === 'istri' ? 'Ibu Rumah Tangga' : 'Wiraswasta',
            ]);

            // Tambahkan 1-2 anak
            $jumlahAnak = rand(1, 2);
            for ($i = 1; $i <= $jumlahAnak; $i++) {
                Keluarga::create([
                    'id_user' => $user->id,
                    'nama' => 'Anak ke' . $i,
                    'hubungan' => 'anak',
                    'tanggal_lahir' => now()->subYears(rand(5, 18))->subDays(rand(0, 365)),
                    'pekerjaan' => 'Pelajar',
                ]);
            }
        }
    }
}
