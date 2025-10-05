<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Departemen;
use App\Models\JenisCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KepalaListPengajuanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil user yang login
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        if (!$pegawai || !$pegawai->jabatan) {
            return redirect()->back()->with('error', 'Data pegawai atau jabatan tidak ditemukan.');
        }

        $nama_departemen = $pegawai->departemen ? $pegawai->departemen->nama_departemen : 'Kepala Yayasan';
        $jabatan = $pegawai->jabatan->nama_jabatan;

        // Query data cuti - HANYA TAMPILKAN YANG MENUNGGU
        $query = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
            ->where('status_cuti', 'Menunggu')
            ->orderBy('tanggal_pengajuan', 'ASC'); // Urutkan berdasarkan tanggal pengajuan (dapat diproses dalam urutan apapun)

        // Filter berdasarkan jabatan yang login
        if ($jabatan === 'Kepala Yayasan') {
            // Kepala Yayasan melihat pengajuan dari role hrd dan jabatan Kepala
            $query->whereHas('pegawai.user', function ($q) {
                $q->where('role', 'hrd');
            })->orWhereHas('pegawai.jabatan', function ($q) {
                $q->where('nama_jabatan', 'like', '%Kepala%');
            });
        } elseif ($jabatan === 'Kepala Departemen' || strpos($jabatan, 'Kepala') !== false) {
            // Kepala Departemen melihat pengajuan dari departemen yang sama
            $query->whereHas('pegawai', function ($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen);
            });
        } else {
            // Jika bukan kepala, tidak ada akses
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat pengajuan cuti.');
        }

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

        // Statistik berdasarkan akses jabatan
        if ($jabatan === 'Kepala Yayasan') {
            // Statistik untuk Kepala Yayasan - hrd dan Kepala
            $all_cuti = Cuti::with('pegawai.departemen', 'pegawai.jabatan', 'pegawai.user')
                ->where(function ($query) {
                    $query->whereHas('pegawai.user', function ($q) {
                        $q->where('role', 'hrd');
                    })->orWhereHas('pegawai.jabatan', function ($q) {
                        $q->where('nama_jabatan', 'like', '%Kepala%');
                    });
                })->get();
        } else {
            // Statistik untuk Kepala Departemen - departemen yang sama
            $all_cuti = Cuti::with('pegawai.departemen')
                ->whereHas('pegawai', function ($q) use ($pegawai) {
                    $q->where('id_departemen', $pegawai->id_departemen);
                })->get();
        }

        $pending = $all_cuti->where('status_cuti', 'Menunggu')->count();
        $approved = $all_cuti->where('status_cuti', 'Disetujui')->count();
        $rejected = $all_cuti->where('status_cuti', 'Ditolak')->count();
        $total = $all_cuti->count();

        // Statistik per departemen
        $stats_per_departemen = [];
        if ($jabatan === 'Kepala Yayasan') {
            // Kepala Yayasan: statistik semua departemen untuk hrd dan Kepala
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
        } else {
            // Kepala Departemen: statistik departemen sendiri
            $dept = $pegawai->departemen;
            if ($dept) {
                $stats_per_departemen[$dept->nama_departemen] = [
                    'pending' => $pending,
                    'approved' => $approved,
                    'rejected' => $rejected,
                    'total' => $total
                ];
            }
        }

        // Dropdown data
        if ($jabatan === 'Kepala Yayasan') {
            $departemen = Departemen::all();
        } else {
            $departemen = collect([$pegawai->departemen])->filter();
        }
        
        $jenis_cuti_options = JenisCuti::pluck('nama_jenis_cuti', 'id_jenis_cuti');

        return view('kepala.listPengajuan', compact(
            'pegawai',
            'nama_departemen',
            'jabatan',
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
            $user = Auth::user();
            $pegawai = $user->pegawai;
            $jabatan = $pegawai->jabatan->nama_jabatan;

            // Validasi akses berdasarkan jabatan
            $hasAccess = false;
            
            if ($jabatan === 'Kepala Yayasan') {
                // Kepala Yayasan dapat memproses pengajuan dari hrd dan Kepala
                $hasAccess = $cuti->pegawai->user->role === 'hrd' || 
                           strpos($cuti->pegawai->jabatan->nama_jabatan, 'Kepala') !== false;
            } elseif ($jabatan === 'Kepala Departemen' || strpos($jabatan, 'Kepala') !== false) {
                // Kepala Departemen dapat memproses pengajuan dari departemen yang sama
                $hasAccess = $cuti->pegawai->id_departemen === $pegawai->id_departemen;
            }

            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk memproses pengajuan cuti ini.');
            }
            
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
            Log::error('Error updating cuti status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pengajuan cuti. Silakan coba lagi.');
        }
    }

    // Method untuk melihat riwayat pengajuan yang sudah diproses
    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $jabatan = $pegawai->jabatan->nama_jabatan;

        $query = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
            ->whereIn('status_cuti', ['Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'DESC');

        // Filter berdasarkan jabatan yang login
        if ($jabatan === 'Kepala Yayasan') {
            $query->where(function ($mainQuery) {
                $mainQuery->whereHas('pegawai.user', function ($q) {
                    $q->where('role', 'hrd');
                })->orWhereHas('pegawai.jabatan', function ($q) {
                    $q->where('nama_jabatan', 'like', '%Kepala%');
                });
            });
        } elseif ($jabatan === 'Kepala Departemen' || strpos($jabatan, 'Kepala') !== false) {
            $query->whereHas('pegawai', function ($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen);
            });
        }

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

        // Dropdown data berdasarkan akses
        if ($jabatan === 'Kepala Yayasan') {
            $departemen = Departemen::all();
        } else {
            $departemen = collect([$pegawai->departemen])->filter();
        }
        
        $jenis_cuti_options = JenisCuti::pluck('nama_jenis_cuti', 'id_jenis_cuti');
        
        return view('kepala.riwayatPengajuan', compact('riwayat_cuti', 'departemen', 'jenis_cuti_options'));
    }

    public function show($id)
    {
        try {
            $cuti = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
                        ->findOrFail($id);
            
            // Validasi akses
            $user = Auth::user();
            $pegawai = $user->pegawai;
            $jabatan = $pegawai->jabatan->nama_jabatan;
            
            $hasAccess = false;
            
            if ($jabatan === 'Kepala Yayasan') {
                $hasAccess = $cuti->pegawai->user->role === 'hrd' || 
                           strpos($cuti->pegawai->jabatan->nama_jabatan, 'Kepala') !== false;
            } elseif ($jabatan === 'Kepala Departemen' || strpos($jabatan, 'Kepala') !== false) {
                $hasAccess = $cuti->pegawai->id_departemen === $pegawai->id_departemen;
            }

            if (!$hasAccess) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }
            
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
     * Get next pengajuan (oldest first, but can be processed in any order)
     */
    public function getNextPengajuan()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $jabatan = $pegawai->jabatan->nama_jabatan;

        $query = Cuti::with(['pegawai.departemen', 'pegawai.jabatan', 'jenisCuti'])
            ->where('status_cuti', 'Menunggu')
            ->orderBy('tanggal_pengajuan', 'ASC');

        // Filter berdasarkan jabatan
        if ($jabatan === 'Kepala Yayasan') {
            $query->where(function ($mainQuery) {
                $mainQuery->whereHas('pegawai.user', function ($q) {
                    $q->where('role', 'hrd');
                })->orWhereHas('pegawai.jabatan', function ($q) {
                    $q->where('nama_jabatan', 'like', '%Kepala%');
                });
            });
        } elseif ($jabatan === 'Kepala Departemen' || strpos($jabatan, 'Kepala') !== false) {
            $query->whereHas('pegawai', function ($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen);
            });
        }

        $next_cuti = $query->first();

        if (!$next_cuti) {
            return response()->json(['message' => 'Tidak ada pengajuan cuti yang menunggu']);
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
     * Dashboard summary berdasarkan jabatan
     */
    public function dashboard()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $jabatan = $pegawai->jabatan->nama_jabatan;

        // Query berdasarkan jabatan
        if ($jabatan === 'Kepala Yayasan') {
            $query = Cuti::where(function ($mainQuery) {
                $mainQuery->whereHas('pegawai.user', function ($q) {
                    $q->where('role', 'hrd');
                })->orWhereHas('pegawai.jabatan', function ($q) {
                    $q->where('nama_jabatan', 'like', '%Kepala%');
                });
            });
        } else {
            $query = Cuti::whereHas('pegawai', function ($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen);
            });
        }

        // Total pengajuan per status
        $total_menunggu = $query->where('status_cuti', 'Menunggu')->count();
        $total_disetujui = $query->where('status_cuti', 'Disetujui')->count();
        $total_ditolak = $query->where('status_cuti', 'Ditolak')->count();

        // Pengajuan menunggu terlama
        $pengajuan_terlama = $query->with(['pegawai.departemen'])
            ->where('status_cuti', 'Menunggu')
            ->orderBy('tanggal_pengajuan', 'ASC')
            ->first();

        // Statistik per departemen
        if ($jabatan === 'Kepala Yayasan') {
            $stats_departemen = Departemen::withCount([
                'pegawai as total_pegawai',
                'pegawai as cuti_menunggu' => function ($query) {
                    $query->whereHas('cuti', function ($q) {
                        $q->where('status_cuti', 'Menunggu');
                    });
                }
            ])->get();
        } else {
            $stats_departemen = collect([$pegawai->departemen])->map(function ($dept) {
                return [
                    'nama_departemen' => $dept->nama_departemen,
                    'total_pegawai' => $dept->pegawai()->count(),
                    'cuti_menunggu' => $dept->pegawai()->whereHas('cuti', function ($q) {
                        $q->where('status_cuti', 'Menunggu');
                    })->count()
                ];
            });
        }

        // Pengajuan hari ini
        $pengajuan_hari_ini = $query->whereDate('tanggal_pengajuan', today())->count();

        // Pengajuan minggu ini
        $pengajuan_minggu_ini = $query->whereBetween('tanggal_pengajuan', [
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

            $user = Auth::user();
            $pegawai = $user->pegawai;
            $jabatan = $pegawai->jabatan->nama_jabatan;

            $updated_count = 0;
            $errors = [];

            foreach ($request->selected_cuti as $cuti_id) {
                $cuti = Cuti::find($cuti_id);
                
                if (!$cuti || $cuti->status_cuti !== 'Menunggu') {
                    $errors[] = "Pengajuan ID {$cuti_id} sudah diproses sebelumnya.";
                    continue;
                }

                // Validasi akses
                $hasAccess = false;
                
                if ($jabatan === 'Kepala Yayasan') {
                    $hasAccess = $cuti->pegawai->role === 'hrd' || 
                               strpos($cuti->pegawai->jabatan->nama_jabatan, 'Kepala') !== false;
                } elseif ($jabatan === 'Kepala Departemen' || strpos($jabatan, 'Kepala') !== false) {
                    $hasAccess = $cuti->pegawai->id_departemen === $pegawai->id_departemen;
                }

                if (!$hasAccess) {
                    $errors[] = "Tidak memiliki akses untuk memproses pengajuan ID {$cuti_id}.";
                    continue;
                }

                // Kepala Yayasan dan Kepala Departemen dapat menyetujui pengajuan tanpa batasan urutan tanggal

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