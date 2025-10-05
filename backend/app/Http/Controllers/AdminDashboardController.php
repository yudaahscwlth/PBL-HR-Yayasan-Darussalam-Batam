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

class AdminDashboardController extends Controller
{
    // TESTING MODE - Set ke true untuk mengaktifkan mode testing
    const TESTING_MODE = true;
    
    // Waktu testing yang bisa diubah-ubah
    const TESTING_TIME = [
        'date' => '2025-07-11',  // Format: Y-m-d
        'time' => '17:30:00'     // Format: H:i:s (contoh: 07:30 untuk test terlambat)
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
        
        // Statistik kehadiran hari ini - HANYA PEGAWAI AKTIF
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        
        // Hitung hanya pegawai dengan status 'Aktif'
        $totalKaryawanAktif = Pegawai::where('status', 'Aktif')->count();
        
        // Kehadiran hari ini hanya dari pegawai aktif
        $kehadiranHariIni = Absensi::whereDate('tanggal', $today)
            ->whereHas('pegawai', function($query) {
                $query->where('status', 'Aktif');
            })
            ->get();
        
        $masukHariIni = $kehadiranHariIni->where('waktu_masuk', '!=', null)->count();
        $terlambat = $kehadiranHariIni->where('status_kehadiran', 'Terlambat')->count();
        
        // Hitung pegawai aktif yang cuti hari ini
        // Asumsi: ada model Cuti atau field cuti di tabel pegawai/absensi
        $cutiHariIni = Absensi::whereDate('tanggal', $today)
            ->where('status_kehadiran', 'Cuti')
            ->whereHas('pegawai', function($query) {
                $query->where('status', 'Aktif');
            })
            ->count();
        
        // Tidak masuk = Total pegawai aktif - yang masuk - yang cuti
        $tidakMasuk = $totalKaryawanAktif - $masukHariIni - $cutiHariIni;
        
        // Pastikan tidak negatif
        $tidakMasuk = max(0, $tidakMasuk);

        // Data pegawai berdasarkan jabatan dan departemen (hanya pegawai aktif)
        $jabatanData = $this->getPegawaiByJabatan();
        $departemenData = $this->getPegawaiByDepartemen();

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

        // Debug info untuk memastikan perhitungan benar
        $debugInfo = [
            'total_pegawai_aktif' => $totalKaryawanAktif,
            'masuk_hari_ini' => $masukHariIni,
            'cuti_hari_ini' => $cutiHariIni,
            'tidak_masuk' => $tidakMasuk,
            'terlambat' => $terlambat
        ];

        return view('admin.index', compact(
            'totalKaryawanAktif',
            'masukHariIni',
            'cutiHariIni',
            'terlambat',
            'tidakMasuk',
            'jabatanData',
            'departemenData',
            'lokasiKantorList',
            'pegawai',
            'nama_departemen',
            'testingInfo',
            'debugInfo'
        ));
    }

    /**
     * Proses absensi dengan waktu testing
     */
    public function absen(Request $request)
    {
        try {
            $user= Auth::user();
            DB::statement("SET @current_user_id = " . $user->id_user);
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
            
            // Pastikan pegawai memiliki status aktif
            if ($pegawai->status !== 'Aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pegawai dengan status aktif yang dapat melakukan absensi'
                ], 403);
            }

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
     * Mendapatkan data pegawai berdasarkan jabatan (hanya pegawai aktif)
     */
    private function getPegawaiByJabatan()
    {
        $jabatanData = Pegawai::with('jabatan')
            ->where('status', 'Aktif')  // Tambahkan filter status aktif
            ->select('id_jabatan', DB::raw('count(*) as total'))
            ->groupBy('id_jabatan')
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->jabatan ? $item->jabatan->nama_jabatan : 'Tidak Ada Jabatan',
                    'total' => $item->total,
                    'percentage' => 0
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // Hitung persentase berdasarkan total pegawai aktif
        $totalKaryawanAktif = Pegawai::where('status', 'Aktif')->count();
        foreach ($jabatanData as &$item) {
            $item['percentage'] = $totalKaryawanAktif > 0 ? round(($item['total'] / $totalKaryawanAktif) * 100, 1) : 0;
        }

        return $jabatanData;
    }

    /**
     * Mendapatkan data pegawai berdasarkan departemen (hanya pegawai aktif)
     */
    private function getPegawaiByDepartemen()
    {
        $departemenData = Pegawai::with('departemen')
            ->where('status', 'Aktif')  // Tambahkan filter status aktif
            ->select('id_departemen', DB::raw('count(*) as total'))
            ->groupBy('id_departemen')
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->departemen ? $item->departemen->nama_departemen : 'Tidak Ada Departemen',
                    'total' => $item->total,
                    'percentage' => 0
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // Hitung persentase berdasarkan total pegawai aktif
        $totalKaryawanAktif = Pegawai::where('status', 'Aktif')->count();
        foreach ($departemenData as &$item) {
            $item['percentage'] = $totalKaryawanAktif > 0 ? round(($item['total'] / $totalKaryawanAktif) * 100, 1) : 0;
        }

        return $departemenData;
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
     * Laporan jam kerja pegawai (hanya pegawai aktif)
     */
    public function laporanJamKerja(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        $laporanData = Pegawai::with(['absensi' => function ($query) use ($bulan, $tahun) {
            $query->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun)
                  ->whereNotNull('waktu_masuk')
                  ->whereNotNull('waktu_pulang');
        }, 'departemen', 'jabatan'])
        ->where('status', 'Aktif')  // Tambahkan filter status aktif
        ->get()
        ->map(function ($pegawai) {
            $absensiData = $pegawai->absensi;
            $totalHariMasuk = $absensiData->count();
            $totalJamKerja = $absensiData->sum('total_jam_kerja');
            $rataRataJamKerja = $totalHariMasuk > 0 ? $totalJamKerja / $totalHariMasuk : 0;
            
            return [
                'nama' => $pegawai->nama_pegawai,
                'departemen' => $pegawai->departemen->nama_departemen ?? '-',
                'jabatan' => $pegawai->jabatan->nama_jabatan ?? '-',
                'total_hari_masuk' => $totalHariMasuk,
                'total_jam_kerja' => round($totalJamKerja, 1),
                'rata_rata_jam_kerja' => round($rataRataJamKerja, 1)
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $laporanData,
            'summary' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_pegawai_aktif' => Pegawai::where('status', 'Aktif')->count()
            ]
        ]);
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