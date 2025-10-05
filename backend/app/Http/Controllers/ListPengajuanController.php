<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Departemen;
use App\Models\JenisCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ListPengajuanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil user yang login (kepala yayasan)
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai && $pegawai->departemen ? $pegawai->departemen->nama_departemen : 'Tidak Diketahui';


         if (
        $user->pegawai->jabatan->nama_jabatan != 'Kepala' ||
        $user->pegawai->departemen->nama_departemen != 'Sumber Daya Manusia'
    ) {
        abort(403, 'Unauthorized');
    }
        // Query data cuti - HANYA TAMPILKAN YANG MENUNGGU
        // Kepala yayasan dapat melihat semua pengajuan dari semua departemen
        $query = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
            ->where('status_cuti', 'Menunggu') // HANYA AMBIL YANG MENUNGGU
            ->orderBy('tanggal_pengajuan', 'ASC'); // Urutkan berdasarkan tanggal pengajuan (dapat diproses dalam urutan apapun)

        // Filter berdasarkan departemen (opsional)
        if ($request->filled('departemen')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('id_departemen', $request->departemen);
            });
        }

        // Filter berdasarkan jenis cuti (opsional)
        if ($request->filled('jenis_cuti')) {
            $query->where('id_jenis_cuti', $request->jenis_cuti);
        }

        // Filter berdasarkan tanggal pengajuan (opsional)
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal_pengajuan', [
                $request->tanggal_mulai,
                $request->tanggal_selesai
            ]);
        }

        $pengajuan_cuti = $query->get();

        // Hitung jumlah hari cuti dan nomor antrian FIFO
        $nomor_antrian = 1;
        foreach ($pengajuan_cuti as $index => $cuti) {
            if ($cuti->tanggal_mulai && $cuti->tanggal_selesai) {
                $cuti->jumlah_hari = Carbon::parse($cuti->tanggal_mulai)
                    ->diffInDays(Carbon::parse($cuti->tanggal_selesai)) + 1;
            } else {
                $cuti->jumlah_hari = 0;
            }
            
            // Semua pengajuan yang ditampilkan adalah "Menunggu", jadi semua dapat nomor antrian
            $cuti->nomor_antrian = $nomor_antrian++;
        }

        // Statistik - Hitung dari semua data per departemen dan keseluruhan
        $all_cuti = Cuti::with('pegawai.departemen')->get();
        $pending = $all_cuti->where('status_cuti', 'Menunggu')->count();
        $approved = $all_cuti->where('status_cuti', 'Disetujui')->count();
        $rejected = $all_cuti->where('status_cuti', 'Ditolak')->count();
        $total = $all_cuti->count();

        // Statistik per departemen
        $stats_per_departemen = [];
        $departemen_list = Departemen::all();
        foreach ($departemen_list as $dept) {
            $dept_cuti = $all_cuti->where('pegawai.id_departemen', $dept->id_departemen);
            $stats_per_departemen[$dept->nama_departemen] = [
                'pending' => $dept_cuti->where('status_cuti', 'Menunggu')->count(),
                'approved' => $dept_cuti->where('status_cuti', 'Disetujui')->count(),
                'rejected' => $dept_cuti->where('status_cuti', 'Ditolak')->count(),
                'total' => $dept_cuti->count()
            ];
        }

        // Dropdown data
        $departemen = Departemen::all();
        $jenis_cuti_options = JenisCuti::pluck('nama_jenis_cuti', 'id_jenis_cuti');

        return view('admin.listPengajuan', compact(
            'pegawai',
            'nama_departemen',
            'pengajuan_cuti',
            'departemen',
            'jenis_cuti_options',
            'pending',
            'approved',
            'rejected',
            'total',
            'stats_per_departemen'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Disetujui,Ditolak,Menunggu',
                'keterangan' => 'nullable|string|max:500',
            ]);

            $cuti = Cuti::findOrFail($id);
            
            // Check if cuti is still pending
            if ($cuti->status_cuti !== 'Menunggu') {
                return redirect()->back()->with('error', 'Pengajuan cuti sudah diproses sebelumnya.');
            }

            // Kepala Yayasan dan Kepala Departemen dapat menyetujui pengajuan tanpa batasan urutan tanggal

            $cuti->status_cuti = $request->status;
            $cuti->disetujui_oleh = Auth::id(); // Catat siapa yang menyetujui
            
            // Add keterangan if provided
            if ($request->filled('keterangan')) {
                $cuti->keterangan = $request->keterangan;
            }
            
            $cuti->save();

            $statusText = $request->status === 'Disetujui' ? 'disetujui' : 'ditolak';
            return redirect()->back()->with('success', "Pengajuan cuti berhasil {$statusText}. Pengajuan akan dihapus dari antrian.");
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating cuti status by HRD: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pengajuan cuti. Silakan coba lagi.');
        }
    }

    // Method untuk melihat riwayat pengajuan yang sudah diproses
    public function riwayat(Request $request)
    {
        $query = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
            ->whereIn('status_cuti', ['Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'DESC'); // Urutkan berdasarkan tanggal update terbaru

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status_cuti', $request->status);
        }

        // Filter berdasarkan departemen
        if ($request->filled('departemen')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('id_departemen', $request->departemen);
            });
        }

        // Filter berdasarkan jenis cuti
        if ($request->filled('jenis_cuti')) {
            $query->where('id_jenis_cuti', $request->jenis_cuti);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('updated_at', [
                $request->tanggal_mulai . ' 00:00:00',
                $request->tanggal_selesai . ' 23:59:59'
            ]);
        }

        $riwayat_cuti = $query->get();

        // Hitung jumlah hari cuti
        foreach ($riwayat_cuti as $cuti) {
            if ($cuti->tanggal_mulai && $cuti->tanggal_selesai) {
                $cuti->jumlah_hari = Carbon::parse($cuti->tanggal_mulai)
                    ->diffInDays(Carbon::parse($cuti->tanggal_selesai)) + 1;
            } else {
                $cuti->jumlah_hari = 0;
            }
        }

        $departemen = Departemen::all();
        $jenis_cuti_options = JenisCuti::pluck('nama_jenis_cuti', 'id_jenis_cuti');
        
        return view('admin.riwayatPengajuan', compact('riwayat_cuti', 'departemen', 'jenis_cuti_options'));
    }

    public function show($id)
    {
        try {
            $cuti = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
                        ->findOrFail($id);
            
            // Calculate duration
            if ($cuti->tanggal_mulai && $cuti->tanggal_selesai) {
                $cuti->jumlah_hari = Carbon::parse($cuti->tanggal_mulai)
                    ->diffInDays(Carbon::parse($cuti->tanggal_selesai)) + 1;
            } else {
                $cuti->jumlah_hari = 0;
            }

            return response()->json($cuti);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    /**
     * Get next pengajuan in FIFO queue
     */
    public function getNextFifo()
    {
        $next_cuti = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
            ->where('status_cuti', 'Menunggu')
            ->orderBy('tanggal_pengajuan', 'ASC')
            ->first();

        if (!$next_cuti) {
            return response()->json(['message' => 'Tidak ada pengajuan cuti dalam antrian']);
        }

        return response()->json($next_cuti);
    }

    /**
     * Get chronological position for a specific cuti (informational only)
     */
    public function getChronologicalPosition($id)
    {
        $cuti = Cuti::findOrFail($id);
        
        if ($cuti->status_cuti !== 'Menunggu') {
            return response()->json(['message' => 'Pengajuan cuti tidak dalam daftar menunggu']);
        }

        $position = Cuti::where('status_cuti', 'Menunggu')
            ->where('tanggal_pengajuan', '<', $cuti->tanggal_pengajuan)
            ->count() + 1;

        return response()->json([
            'position' => $position,
            'note' => 'Posisi berdasarkan urutan tanggal pengajuan (dapat diproses dalam urutan apapun)'
        ]);
    }

    /**
     * Dashboard summary for kepala yayasan
     */
    public function dashboard()
    {
        // Total pengajuan per status
        $total_menunggu = Cuti::where('status_cuti', 'Menunggu')->count();
        $total_disetujui = Cuti::where('status_cuti', 'Disetujui')->count();
        $total_ditolak = Cuti::where('status_cuti', 'Ditolak')->count();

        // Pengajuan menunggu terlama
        $pengajuan_terlama = Cuti::with(['pegawai.departemen'])
            ->where('status_cuti', 'Menunggu')
            ->orderBy('tanggal_pengajuan', 'ASC')
            ->first();

        // Statistik per departemen
        $stats_departemen = Departemen::withCount([
            'pegawai as total_pegawai',
            'pegawai as cuti_menunggu' => function ($query) {
                $query->whereHas('cuti', function ($q) {
                    $q->where('status_cuti', 'Menunggu');
                });
            }
        ])->get();

        // Pengajuan hari ini
        $pengajuan_hari_ini = Cuti::whereDate('tanggal_pengajuan', today())->count();

        // Pengajuan minggu ini
        $pengajuan_minggu_ini = Cuti::whereBetween('tanggal_pengajuan', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        return view('kepala.dashboard', compact(
            'total_menunggu',
            'total_disetujui',
            'total_ditolak',
            'pengajuan_terlama',
            'stats_departemen',
            'pengajuan_hari_ini',
            'pengajuan_minggu_ini'
        ));
    }

    /**
     * Bulk approve/reject multiple cuti applications
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $request->validate([
                'selected_cuti' => 'required|array',
                'selected_cuti.*' => 'exists:cuti,id_cuti',
                'bulk_action' => 'required|in:Disetujui,Ditolak',
                'bulk_keterangan' => 'nullable|string|max:500'
            ]);

            $updated_count = 0;
            $errors = [];

            foreach ($request->selected_cuti as $cuti_id) {
                $cuti = Cuti::find($cuti_id);
                
                if (!$cuti || $cuti->status_cuti !== 'Menunggu') {
                    $errors[] = "Pengajuan ID {$cuti_id} sudah diproses sebelumnya.";
                    continue;
                }

                // Kepala Yayasan dapat menyetujui pengajuan tanpa batasan urutan tanggal

                $cuti->status_cuti = $request->bulk_action;
                $cuti->disetujui_oleh = Auth::id();
                
                if ($request->filled('bulk_keterangan')) {
                    $cuti->keterangan = $request->bulk_keterangan;
                }
                
                $cuti->save();
                $updated_count++;
            }

            $message = "Berhasil memproses {$updated_count} pengajuan cuti.";
            if (!empty($errors)) {
                $message .= " Beberapa pengajuan gagal diproses: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error in bulk update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pengajuan cuti.');
        }
    }
}