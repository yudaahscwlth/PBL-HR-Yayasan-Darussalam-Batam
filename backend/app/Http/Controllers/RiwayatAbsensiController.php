<?php 
namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->pegawai) {
            return redirect()->back()->with('error', 'Anda tidak memiliki data pegawai. Silakan hubungi administrator.');
        }

        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';
        $departemen = Departemen::orderBy('nama_departemen')->get();

        // Query dasar untuk absensi
        $query = Absensi::with(['pegawai', 'pegawai.departemen'])
                        ->where('id_pegawai', $pegawai->id_pegawai)
                        ->orderBy('tanggal', 'desc');

        // Filter berdasarkan bulan jika ada request
        if ($request->filled('bulan')) {
            $bulan = $request->bulan;
            $query->whereMonth('tanggal', $bulan);
        }

        // Filter berdasarkan tahun jika ada request  
        if ($request->filled('tahun')) {
            $tahun = $request->tahun;
            $query->whereYear('tanggal', $tahun);
        }

        // Filter berdasarkan status kehadiran
        if ($request->filled('status')) {
            $status = $request->status;
            $query->where('status_kehadiran', $status);
        }

        $absensi = $query->paginate(10)->withQueryString();

        // Data untuk filter dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $tahunList = range(date('Y'), date('Y') - 5); // 6 tahun terakhir

        $statusList = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tidak Hadir'];

        // Pilih view berdasarkan role user
        $viewName = $user->role === 'pegawai' ? 'karyawan.absensi' : 'admin.RiwayatAbsensi';

        return view($viewName, compact(
            'absensi', 
            'pegawai', 
            'nama_departemen', 
            'departemen',
            'bulanList',
            'tahunList', 
            'statusList'
        ));
    }

    // Method untuk export data (opsional)
    public function export(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        $query = Absensi::with(['pegawai', 'pegawai.departemen'])
                        ->where('id_pegawai', $pegawai->id_pegawai)
                        ->orderBy('tanggal', 'desc');

        // Apply same filters as index method
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('status')) {
            $query->where('status_kehadiran', $request->status);
        }

        $absensi = $query->get();

        // Logic untuk export (Excel, PDF, dll)
        // return Excel::download(new AbsensiExport($absensi), 'riwayat-absensi.xlsx');
    }
}