<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KepalaDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Statistik kehadiran hari ini
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        $totalKaryawan = Pegawai::count();
        
        $kehadiranHariIni = Absensi::where('tanggal', $today)->get();
        
        $masukHariIni = $kehadiranHariIni->where('waktu_masuk', '!=', null)->count();
        $terlambat = $kehadiranHariIni->where('status_kehadiran', 'Terlambat')->count();
        
        $cutiHariIni = 0;
        
        $tidakMasuk = $totalKaryawan - $masukHariIni - $cutiHariIni;

        // Data pegawai berdasarkan jabatan dan departemen
        $jabatanData = $this->getPegawaiByJabatan();
        $departemenData = $this->getPegawaiByDepartemen();

        return view('kepala.index', compact(
            'totalKaryawan',
            'masukHariIni',
            'cutiHariIni',
            'terlambat',
            'tidakMasuk',
            'jabatanData',
            'departemenData',
            'pegawai',
            'nama_departemen'
        ));
    }

    /**
     * Mendapatkan data pegawai berdasarkan jabatan
     */
    private function getPegawaiByJabatan()
    {
        $jabatanData = Pegawai::with('jabatan')
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

        // Hitung persentase
        $totalKaryawan = Pegawai::count();
        foreach ($jabatanData as &$item) {
            $item['percentage'] = $totalKaryawan > 0 ? round(($item['total'] / $totalKaryawan) * 100, 1) : 0;
        }

        return $jabatanData;
    }

    /**
     * Mendapatkan data pegawai berdasarkan departemen
     */
    private function getPegawaiByDepartemen()
    {
        $departemenData = Pegawai::with('departemen')
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

        // Hitung persentase
        $totalKaryawan = Pegawai::count();
        foreach ($departemenData as &$item) {
            $item['percentage'] = $totalKaryawan > 0 ? round(($item['total'] / $totalKaryawan) * 100, 1) : 0;
        }

        return $departemenData;
    }

    /**
     * Laporan jam kerja pegawai
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
                'tahun' => $tahun
            ]
        ]);
    }
}