<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KehadiranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil id_pegawai dari tabel users
        $pegawaiIds = [1, 2, 3, 5, 6];
        
        // Status kehadiran yang tersedia
        $statusKehadiran = ['Hadir', 'Tidak Hadir', 'Terlambat', 'Sakit'];
        
        // Durasi kerja dalam format yang berbeda
        $durasiKerja = ['8:00', '7:30', '8:30', '6:00', '9:00'];
        
        $kehadiranData = [];
        
        // Generate data kehadiran untuk 30 hari terakhir untuk semua pegawai
        for ($i = 0; $i < 30; $i++) {
            $tanggal = Carbon::now()->subDays($i)->format('Y-m-d');
            
            foreach ($pegawaiIds as $pegawaiId) {
                // Lokasi kantor yang bervariasi
                $lokasiKantor = rand(3,4);
                
                // Waktu masuk bervariasi dari 07:30 sampai 19:00
                $jamMasuk = rand(7, 19); 
                $menitMasuk = ($jamMasuk == 7) ? rand(30, 59) : rand(0, 59); 
                
                $waktuMasuk = Carbon::parse($tanggal . ' ' . sprintf('%02d:%02d:00', $jamMasuk, $menitMasuk));
                
                $waktuPulang = $waktuMasuk->copy()->addHours(8)->addMinutes(rand(-30, 60));
                
                $totalJamKerja = $waktuMasuk->diffInHours($waktuPulang, false);
                if ($totalJamKerja < 0) $totalJamKerja = 0;
                
    
                $durasi = $durasiKerja[array_rand($durasiKerja)];

                $status = 'Hadir';
                if ($waktuMasuk->hour >= 9) {
                    $status = 'Terlambat';
                } elseif (rand(1, 10) <= 1) { // 10% chance untuk tidak hadir atau sakit
                    $status = $statusKehadiran[array_rand(['Tidak Hadir', 'Sakit'])];
                }
                
                // Jika tidak hadir atau sakit, set waktu pulang null
                if (in_array($status, ['Tidak Hadir', 'Sakit'])) {
                    $waktuPulang = null;
                    $totalJamKerja = 0;
                }
                
                $kehadiranData[] = [
                    'id_pegawai' => $pegawaiId,
                    'tanggal' => $tanggal,
                    'lokasi_kantor_id' => $lokasiKantor,
                    'waktu_masuk' => $waktuMasuk->format('Y-m-d H:i:s'),
                    'waktu_pulang' => $waktuPulang ? $waktuPulang->format('Y-m-d H:i:s') : null,
                    'total_jam_kerja' => number_format($totalJamKerja, 2),
                    'durasi_kerja' => $durasi,
                    'status_jam_kerja' => 'Memenuhi',
                    'status_kehadiran' => rand(1, 100) <= 5 ? 'Sakit' : 'Hadir', // 5% chance sakit
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insert data ke tabel kehadiran
        DB::table('kehadiran')->insert($kehadiranData);
        
        $this->command->info('Kehadiran seeder berhasil dijalankan!');
    }
}