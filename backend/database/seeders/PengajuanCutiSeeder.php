<?php

namespace Database\Seeders;

use App\Models\PengajuanCuti;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PengajuanCutiSeeder extends Seeder
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

        // Buat data pengajuan cuti untuk setiap user
        foreach ($users as $user) {
            // Menggunakan Carbon untuk mendapatkan tanggal dan waktu
            $tanggalMulai = Carbon::now()->toDateString(); // Tanggal mulai cuti (misalnya hari ini)
            $tanggalSelesai = Carbon::now()->addDays(5)->toDateString(); // Tanggal selesai cuti (misalnya 5 hari dari sekarang)

            PengajuanCuti::create([
                'id_user' => $user->id,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'tipe_cuti' => 'cuti tahunan', // Tipe cuti bisa disesuaikan
                'status_pengajuan' => 'ditinjau kepala sekolah', // Status cuti awalnya menunggu
                'alasan_pendukung' => 'Untuk keperluan pribadi', // Alasan bisa disesuaikan
                'file_pendukung' => null, // Jika tidak ada file pendukung, bisa dibiarkan null
                'komentar' => 'Pengajuan cuti tahunan', // Komentar bisa disesuaikan
            ]);
        }
    }
}
