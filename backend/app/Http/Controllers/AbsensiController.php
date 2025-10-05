<?php 
namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        // Pastikan pegawai ada
        if (!$pegawai) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan. Silakan hubungi administrator.');
        }
        
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';
        $departemen = Departemen::orderBy('nama_departemen')->get();
        
        // Query builder untuk absensi
        $query = Absensi::with(['pegawai', 'pegawai.departemen'])
                        ->orderBy('tanggal', 'desc');
        
        // Cek role user
        if ($user->role === 'pegawai') {
            // Jika role pegawai, hanya tampilkan absensi mereka sendiri
            $query->where('id_pegawai', $pegawai->id);
        }
        
        // Filter berdasarkan request
        if ($request->filled('status')) {
            $query->where('status_kehadiran', $request->status);
        }
        
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }
        
        // Pagination dengan Laravel (15 data per halaman)
        $absensi= $query->paginate(10)->withQueryString();
        
        
        // Return view berdasarkan role
        if ($user->role === 'pegawai') {
            return view('karyawan.absensi', compact('absensi', 'pegawai', 'nama_departemen', 'departemen'));
        } else {
            return view('admin.absensi', compact('absensi', 'pegawai', 'nama_departemen', 'departemen'));
        }
    }
    
    // public function create()
    // {
    //     $user = Auth::user();
        
    //     if ($user->role === 'pegawai') {
    //         // Jika role pegawai, hanya bisa membuat absensi untuk diri sendiri
    //         $pegawai = Pegawai::where('id', $user->pegawai->id)->get();
    //         return view('karyawan.absensi.create', compact('pegawai'));
    //     } else {
    //         // Jika role admin, bisa membuat absensi untuk semua pegawai
    //         $pegawai = Pegawai::all();
    //         return view('admin.absensi.create', compact('pegawai'));
    //     }
    // }
    
    // public function store(Request $request)
    // {
    //     $user = Auth::user();
        
    //     $request->validate([
    //         'id_pegawai' => 'required|exists:pegawai,id',
    //         'tanggal' => 'required|date',
    //         'status_kehadiran' => 'required|in:Hadir,Izin,Sakit,Tidak Hadir',
    //         'waktu_masuk' => 'nullable|date_format:H:i',
    //         'waktu_keluar' => 'nullable|date_format:H:i',
    //         'keterangan' => 'nullable|string|max:255'
    //     ]);

    //     // Jika role pegawai, pastikan hanya bisa membuat absensi untuk diri sendiri
    //     if ($user->role === 'pegawai' && $request->id_pegawai != $user->pegawai->id) {
    //         return redirect()->back()->with('error', 'Anda hanya bisa membuat absensi untuk diri sendiri.');
    //     }

    //     // Cek apakah sudah ada absensi untuk tanggal yang sama
    //     $existingAbsensi = Absensi::where('id_pegawai', $request->id_pegawai)
    //                               ->whereDate('tanggal', $request->tanggal)
    //                               ->first();
                                  
    //     if ($existingAbsensi) {
    //         return redirect()->back()->with('error', 'Absensi untuk tanggal ini sudah ada.');
    //     }

    //     Absensi::create([
    //         'id_pegawai' => $request->id_pegawai,
    //         'tanggal' => $request->tanggal,
    //         'status_kehadiran' => $request->status_kehadiran,
    //         'waktu_masuk' => $request->waktu_masuk,
    //         'waktu_pulang' => $request->waktu_keluar,
    //         'keterangan' => $request->keterangan
    //     ]);
        
    //     // Return berdasarkan role
    //     if ($user->role === 'pegawai') {
    //         return redirect()->route('karyawan.absensi')
    //                         ->with('success', 'Data absensi berhasil ditambahkan.');
    //     } else {
    //         return redirect()->route('admin.absensi')
    //                         ->with('success', 'Data absensi berhasil ditambahkan.');
    //     }
    // }
    
public function destroy($id)
    {
        try {
            $user = Auth::user();
            $absensi = Absensi::findOrFail($id);
            
            // Jika role pegawai, cek apakah absensi milik mereka
            if ($user->role === 'pegawai' && $absensi->id_pegawai != $user->pegawai->id) {
                return redirect()->back()
                                ->with('error', 'Anda tidak memiliki akses untuk menghapus absensi ini.');
            }
            
            $absensi->delete();
            
            // Redirect berdasarkan role user
            $redirectRoute = $user->role === 'pegawai' ? 'karyawan.absensi' : 'admin.absensi';
            return redirect()->route($redirectRoute)
                            ->with('success', 'Data absensi berhasil dihapus.');
                            
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat menghapus data absensi.');
        }
    }
    
    public function show($id)
    {
        $user = Auth::user();
        $absensi = Absensi::with(['pegawai', 'pegawai.departemen'])->findOrFail($id);
        
        // Jika role pegawai, cek apakah absensi milik mereka
        if ($user->role === 'pegawai' && $absensi->id_pegawai != $user->pegawai->id) {
            return redirect()->route('karyawan.absensi')
                            ->with('error', 'Anda tidak memiliki akses untuk melihat absensi ini.');
        }
        
        return view('admin.absensi.show', compact('absensi'));
    }
    
    // Method untuk export data (opsional)
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $query = Absensi::with(['pegawai', 'pegawai.departemen'])
                        ->orderBy('tanggal', 'desc');
        
        if ($user->role === 'pegawai') {
            $query->where('id_pegawai', $user->pegawai->id);
        }
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_kehadiran', $request->status);
        }
        
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }
        
        $absensi = $query->get();
        
        // Create CSV
        $filename = 'data_absensi_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // CSV headers
        fputcsv($handle, [
            'No',
            'Nama Pegawai',
            'Departemen',
            'Tanggal',
            'Status',
            'Waktu Masuk',
            'Waktu Keluar',
            'Keterangan'
        ]);
        
        // CSV data
        foreach ($absensi as $index => $item) {
            fputcsv($handle, [
                $index + 1,
                $item->pegawai->nama ?? 'N/A',
                $item->pegawai->departemen->nama_departemen ?? 'N/A',
                Carbon::parse($item->tanggal)->format('d/m/Y'),
                $item->status_kehadiran,
                $item->waktu_masuk ? Carbon::parse($item->waktu_masuk)->format('H:i') : '-',
                $item->waktu_pulang ? Carbon::parse($item->waktu_pulang)->format('H:i') : '-',
                $item->keterangan ?? ''
            ]);
        }
        
        fclose($handle);
        exit;
    }
    
    // Untuk AJAX request
    public function getDepartemen()
    {
        $departemen = Departemen::select('id_departemen', 'nama_departemen', 'kepala_departemen')
                                ->orderBy('nama_departemen')
                                ->get();
        
        return response()->json($departemen);
    }
}