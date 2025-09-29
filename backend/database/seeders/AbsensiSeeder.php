<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua user yang ada
        $users = User::all();

        // Pastikan ada data user
        if ($users->isEmpty()) {
            echo "Data user kosong.\n";
            return;
        }

        // Buat data absensi untuk setiap user
        foreach ($users as $user) {
            for ($i = 0; $i < 10; $i++) {
                $tanggal = Carbon::now()->subDays($i);

                $checkIn = $tanggal->copy()->setTime(rand(6, 9), rand(0, 59));
                $checkOut = $tanggal->copy()->setTime(rand(15, 17), rand(0, 59));

                $status = $checkIn->gt($tanggal->copy()->setTime(7, 30)) ? 'terlambat' : 'hadir';

                Absensi::create([
                    'id_user' => $user->id,
                    'tanggal' => $tanggal->toDateString(),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'latitude_in' => -6.200000 + mt_rand() / mt_getrandmax() * 0.01,
                    'longitude_in' => 106.816666 + mt_rand() / mt_getrandmax() * 0.01,
                    'latitude_out' => -6.200000 + mt_rand() / mt_getrandmax() * 0.01,
                    'longitude_out' => 106.816666 + mt_rand() / mt_getrandmax() * 0.01,
                    'status' => $status,
                    'keterangan' => $status === 'terlambat' ? 'Datang terlambat karena macet' : null,
                    'file_pendukung' => null,
                ]);
            }
        }
    }
}
