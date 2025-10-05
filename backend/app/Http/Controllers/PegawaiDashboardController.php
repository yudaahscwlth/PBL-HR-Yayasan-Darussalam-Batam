<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\LokasiKantor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PegawaiDashboardController extends Controller
{
    // TESTING MODE - Set ke true untuk mengaktifkan mode testing
    const TESTING_MODE = false;
    
    // Waktu testing yang bisa diubah-ubah
    const TESTING_TIME = [
        'date' => '2025-07-02',  // Format: Y-m-d
        'time' => '07:30:00'     // Format: H:i:s (contoh: 07:30 untuk test terlambat)
    ];
    
    const JAM_MASUK_STANDAR = 8;  // 08:00

    /**
     * Helper function untuk mendapatkan waktu sekarang
     * Gunakan waktu testing jika TESTING_MODE = true
     */
    private function getCurrentTime()
    {
        if (self::TESTING_MODE) {
            return Carbon::parse(self::TESTING_TIME['date'] . ' ' . self::TESTING_TIME['time']);
        }
        return Carbon::now();
    }

    /**
     * Helper function untuk mendapatkan tanggal hari ini
     */
    private function getToday()
    {
        if (self::TESTING_MODE) {
            return Carbon::parse(self::TESTING_TIME['date'])->startOfDay();
        }
        return Carbon::today();
    }

    public function index()
    {
        $today = $this->getToday();
        
        // Statistik kehadiran hari ini
        $user = Auth::user();
        $pegawai = $user->pegawai; 
        $nama_jabatan = $pegawai->jabatan->nama_jabatan;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        
        $kehadiranHariIni = Absensi::where('tanggal', $today)->get();

        // Semua lokasi kantor (untuk dropdown selection)
        $lokasiKantorList = LokasiKantor::where('status', 'aktif')
            ->orderBy('nama_lokasi')
            ->get();

        // Tambahkan info testing mode ke view
        $testingInfo = null;
        if (self::TESTING_MODE) {
            $testingInfo = [
                'mode' => 'TESTING MODE AKTIF',
                'waktu_testing' => $this->getCurrentTime()->format('Y-m-d H:i:s'),
                'peringatan' => 'Sistem menggunakan waktu testing, bukan waktu real!'
            ];
        }

        return view('karyawan.index', compact(
            'lokasiKantorList',
            'pegawai',
            'nama_departemen',
            'nama_jabatan',
            'testingInfo'
        ));
    }

    /**
     * Proses absensi dengan waktu testing
     */
    public function absen(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'action' => 'required|in:masuk,pulang',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'lokasi_kantor_id' => 'required|exists:lokasi_kantor,id'
            ]);

            $user = Auth::user();
            
            if (!$user->pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pegawai tidak ditemukan'
                ], 404);
            }
            
            $pegawai = $user->pegawai;

            // Ambil lokasi kantor yang dipilih
            $lokasiKantor = LokasiKantor::where('id', $request->lokasi_kantor_id)
                ->where('status', 'aktif')
                ->first();
            
            if (!$lokasiKantor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi kantor tidak ditemukan atau tidak aktif'
                ], 404);
            }

            // Cek apakah user berada dalam radius kantor
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $lokasiKantor->latitude,
                $lokasiKantor->longitude
            );

            if ($distance > $lokasiKantor->radius_meter) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda berada di luar radius kantor {$lokasiKantor->nama_lokasi}. Jarak: " . round($distance) . " meter (maksimal: {$lokasiKantor->radius_meter} meter)"
                ], 400);
            }

            // Gunakan waktu testing atau waktu real
            $today = $this->getToday();
            $now = $this->getCurrentTime();

            // Mulai database transaction
            DB::beginTransaction();

            // Cari atau buat record kehadiran
            $kehadiran = Absensi::where('id_pegawai', $pegawai->id_pegawai)
                               ->where('tanggal', $today)
                               ->first();

            if ($request->action === 'masuk') {
                if ($kehadiran && $kehadiran->waktu_masuk) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan absen masuk hari ini'
                    ], 400);
                }

                // Jika belum ada record, buat baru
                if (!$kehadiran) {
                    $kehadiran = new Absensi();
                    $kehadiran->id_pegawai = $pegawai->id_pegawai;
                    $kehadiran->tanggal = $today;
                    $kehadiran->lokasi_kantor_id = $lokasiKantor->id;
                }

                $kehadiran->waktu_masuk = $now;
                
                // Cek keterlambatan (jam masuk standar 08:00)
                $jamMasukStandar = $today->copy()->setTime(self::JAM_MASUK_STANDAR, 0, 0);
                if ($now->gt($jamMasukStandar)) {
                    $kehadiran->status_kehadiran = 'Terlambat';
                } else {
                    $kehadiran->status_kehadiran = 'Hadir';
                }
                
                $kehadiran->save();
                DB::commit();

                $responseMessage = 'Absen masuk berhasil dicatat pada ' . 
                                 $kehadiran->waktu_masuk->format('H:i:s') . 
                                 ' di ' . $lokasiKantor->nama_lokasi;
                
                // Tambahkan info testing jika aktif
                if (self::TESTING_MODE) {
                    $responseMessage .= ' [TESTING MODE]';
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $responseMessage,
                    'data' => [
                        'waktu_masuk' => $kehadiran->waktu_masuk->format('H:i:s'),
                        'status' => $kehadiran->status_kehadiran,
                        'lokasi_kantor' => $lokasiKantor->nama_lokasi,
                        'testing_mode' => self::TESTING_MODE,
                        'waktu_testing' => self::TESTING_MODE ? $now->format('Y-m-d H:i:s') : null
                    ]
                ]);
                
            } elseif ($request->action === 'pulang') {
                if (!$kehadiran) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda belum melakukan absen masuk hari ini'
                    ], 400);
                }

                if (!$kehadiran->waktu_masuk) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda belum melakukan absen masuk'
                    ], 400);
                }
                
                if ($kehadiran->waktu_pulang) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan absen pulang hari ini'
                    ], 400);
                }

                // Hitung jam kerja tanpa validasi minimum
                $jamKerja = $this->hitungJamKerja($kehadiran->waktu_masuk, $now);
                
                $kehadiran->waktu_pulang = $now;
                $kehadiran->total_jam_kerja = $jamKerja['total_jam'];
                $kehadiran->durasi_kerja = $jamKerja['jam_kerja_formatted'];
                
                // Set status jam kerja
                if ($jamKerja['total_jam'] >= 8) {
                    $kehadiran->status_jam_kerja = 'Memenuhi';
                } elseif ($jamKerja['total_jam'] >= 4) {
                    $kehadiran->status_jam_kerja = 'Setengah Hari';
                } else {
                    $kehadiran->status_jam_kerja = 'Kurang';
                }
                
                $kehadiran->save();
                DB::commit();

                $responseMessage = 'Absen pulang berhasil dicatat pada ' . 
                                 $kehadiran->waktu_pulang->format('H:i:s') . 
                                 ' di ' . $lokasiKantor->nama_lokasi;
                
                // Tambahkan info testing jika aktif
                if (self::TESTING_MODE) {
                    $responseMessage .= ' [TESTING MODE]';
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $responseMessage,
                    'data' => [
                        'waktu_masuk' => $kehadiran->waktu_masuk->format('H:i:s'),
                        'waktu_pulang' => $kehadiran->waktu_pulang->format('H:i:s'),
                        'total_jam_kerja' => $jamKerja['jam_kerja_formatted'],
                        'total_jam_decimal' => $jamKerja['total_jam'],
                        'status_jam_kerja' => $kehadiran->status_jam_kerja,
                        'lokasi_kantor' => $lokasiKantor->nama_lokasi,
                        'appreciation' => 'Terima kasih atas kerja keras Anda hari ini!',
                        'testing_mode' => self::TESTING_MODE,
                        'waktu_testing' => self::TESTING_MODE ? $now->format('Y-m-d H:i:s') : null
                    ]
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Absen error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data absen'
            ], 500);
        }
    }

    /**
     * Cek status jam kerja pegawai hari ini
     */
    public function getStatusJamKerja()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $today = $this->getToday();
        
        $kehadiran = Absensi::where('id_pegawai', $pegawai->id_pegawai)
            ->where('tanggal', $today)
            ->first();
        
        if (!$kehadiran || !$kehadiran->waktu_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data absen masuk hari ini'
            ]);
        }
        
        $now = $this->getCurrentTime();
        $jamKerja = $this->hitungJamKerja($kehadiran->waktu_masuk, $now);
        
        return response()->json([
            'success' => true,
            'data' => [
                'waktu_masuk' => $kehadiran->waktu_masuk->format('H:i:s'),
                'waktu_sekarang' => $now->format('H:i:s'),
                'jam_kerja_sekarang' => $jamKerja['jam_kerja_formatted'],
                'total_jam_decimal' => $jamKerja['total_jam'],
                'status' => 'Sedang Bekerja',
                'testing_mode' => self::TESTING_MODE,
                'waktu_testing' => self::TESTING_MODE ? $now->format('Y-m-d H:i:s') : null
            ]
        ]);
    }

    /**
     * Hitung jam kerja antara waktu masuk dan waktu pulang
     */
    private function hitungJamKerja($waktuMasuk, $waktuPulang)
    {
        $masuk = Carbon::parse($waktuMasuk);
        $pulang = Carbon::parse($waktuPulang);
        
        // Hitung selisih dalam menit
        $totalMenit = $masuk->diffInMinutes($pulang);
        
        // Kurangi waktu istirahat (misal 1 jam = 60 menit)
        $waktuIstirahat = 60; // 1 jam istirahat
        $totalMenitKerja = $totalMenit - $waktuIstirahat;
        
        // Pastikan tidak negatif
        $totalMenitKerja = max(0, $totalMenitKerja);
        
        // Konversi ke jam
        $totalJam = $totalMenitKerja / 60;
        
        // Format jam:menit
        $jam = floor($totalJam);
        $menit = $totalMenitKerja % 60;
        
        return [
            'total_menit' => $totalMenitKerja,
            'total_jam' => round($totalJam, 2),
            'jam_kerja_formatted' => sprintf('%02d:%02d', $jam, $menit),
            'jam' => $jam,
            'menit' => $menit
        ];
    }

    /**
     * Menghitung jarak antara dua koordinat menggunakan formula Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        
        // Konversi derajat ke radian
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);
        
        // Hitung selisih
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;
        
        // Formula Haversine
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Hitung jarak dalam meter
        $distance = $earthRadius * $c;
        
        return $distance;
    }
}