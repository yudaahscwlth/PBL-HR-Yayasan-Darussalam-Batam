<?php
// File: app/Http/Controllers/PenilaianController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Penilaian;
use App\Models\Kuisioner;
use App\Models\Departemen;
use Illuminate\Http\Request;
use App\Models\PeriodePenilaian;
use App\Models\JawabanKuisioner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    /**
     * Dashboard Penilaian untuk Admin
     */
    public function index()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        // Jika admin, tampilkan semua penilaian
        if ($user->role === 'admin') {
            $penilaian = Penilaian::with([
                'periode', 
                'penilai.pegawai.departemen', 
                'dinilai.pegawai.departemen',
                'jawabanKuisioner'
            ])->get();
        } else {
            // Jika bukan admin, hanya tampilkan penilaian departemen sendiri
            $penilaian = Penilaian::with([
                'periode', 
                'penilai.pegawai.departemen', 
                'dinilai.pegawai.departemen',
                'jawabanKuisioner'
            ])
            ->whereHas('dinilai.pegawai', function($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen);
            })
            ->get();
        }
        
        $nama_departemen = $pegawai->departemen->nama_departemen;
        
        return view('admin.penilaian', compact('penilaian', 'pegawai', 'nama_departemen'));
    }

    /**
     * Dashboard Penilaian untuk Karyawan
     */
    public function index2()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        // Penilaian yang harus diisi oleh user ini
        $penilaianPenilai = Penilaian::with([
            'periode', 
            'dinilai.pegawai.departemen',
            'jawabanKuisioner'
        ])
        ->where('penilai_id_user', $user->id_user)
        ->get();
        
        // Penilaian yang diterima oleh user ini
        $penilaianDiterima = Penilaian::with([
            'periode', 
            'penilai.pegawai.departemen',
            'jawabanKuisioner'
        ])
        ->where('dinilai_id_user', $user->id_user)
        ->get();
        
        $nama_departemen = $pegawai->departemen->nama_departemen;
        
        return view('admin.penilaian-karyawan', compact(
            'penilaianPenilai', 
            'penilaianDiterima', 
            'pegawai', 
            'nama_departemen'
        ));
    }

    /**
     * Mendapatkan rekan se-departemen yang bisa dinilai
     */
    public function getRekanSeDepartemen($periodeId = null)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        $rekanQuery = User::with(['pegawai.departemen', 'pegawai.jabatan'])
            ->whereHas('pegawai', function($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen)
                  ->where('id_pegawai', '!=', $pegawai->id_pegawai);
            });
        
        // Jika ada periode tertentu, filter yang belum dinilai
        if ($periodeId) {
            $rekanQuery->whereDoesntHave('penilaianSebagaiDinilai', function($q) use ($user, $periodeId) {
                $q->where('penilai_id_user', $user->id_user)
                  ->where('periode_id', $periodeId);
            });
        }
        
        return $rekanQuery->get();
    }

    /**
     * Menampilkan form untuk membuat penilaian peer evaluation
     */
    public function create($periodeId = null)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        // Ambil periode aktif jika tidak ada parameter
        if (!$periodeId) {
            $periode = PeriodePenilaian::aktif()->first();
            if (!$periode) {
                return redirect()->back()->with('error', 'Tidak ada periode penilaian yang aktif');
            }
            $periodeId = $periode->id;
        } else {
            $periode = PeriodePenilaian::findOrFail($periodeId);
        }
        
        // Ambil rekan se-departemen
        $rekanSeDepartemen = $this->getRekanSeDepartemen($periodeId);
        
        // Ambil kuisioner sesuai departemen dan golongan
        $kuisioner = $this->getKuisionerForUser($user->id_user, $periodeId);
        
        $nama_departemen = $pegawai->departemen->nama_departemen;
        
        return view('penilaian.create', compact(
            'periode', 
            'rekanSeDepartemen', 
            'kuisioner', 
            'pegawai',
            'nama_departemen'
        ));
    }

    /**
     * Menampilkan form penilaian untuk rekan tertentu
     */
    public function evaluate($periodeId, $dinilaiUserId)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $periode = PeriodePenilaian::findOrFail($periodeId);
        $dinilaiUser = User::with('pegawai.departemen')->findOrFail($dinilaiUserId);
        
        // Validasi: harus se-departemen
        if ($pegawai->id_departemen !== $dinilaiUser->pegawai->id_departemen) {
            return redirect()->back()->with('error', 'Anda hanya boleh menilai rekan se-departemen');
        }
        
        // Validasi: tidak boleh menilai diri sendiri
        if ($user->id_user === $dinilaiUserId) {
            return redirect()->back()->with('error', 'Tidak boleh menilai diri sendiri');
        }
        
        // Cek apakah sudah ada penilaian
        $penilaian = Penilaian::where([
            'periode_id' => $periodeId,
            'penilai_id_user' => $user->id_user,
            'dinilai_id_user' => $dinilaiUserId
        ])->first();
        
        // Jika belum ada, buat record penilaian baru
        if (!$penilaian) {
            $penilaian = Penilaian::create([
                'periode_id' => $periodeId,
                'penilai_id_user' => $user->id_user,
                'dinilai_id_user' => $dinilaiUserId,
                'status' => 'belum_diisi',
                'total_nilai' => 0
            ]);
        }
        
        // Ambil kuisioner dan jawaban yang sudah ada
        $kuisioner = $this->getKuisionerForUser($user->id_user, $periodeId);
        $jawabanExisting = $penilaian->jawabanKuisioner()->pluck('skor', 'kuisioner_id');
        $komentarExisting = $penilaian->jawabanKuisioner()->pluck('komentar', 'kuisioner_id');
        
        return view('penilaian.evaluate', compact(
            'penilaian',
            'periode',
            'dinilaiUser',
            'kuisioner',
            'jawabanExisting',
            'komentarExisting',
            'pegawai'
        ));
    }

    /**
     * Menyimpan jawaban penilaian
     */
    public function store(Request $request)
    {
        $request->validate([
            'penilaian_id' => 'required|exists:penilaian,id',
            'jawaban' => 'required|array',
            'jawaban.*.kuisioner_id' => 'required|exists:kuisioner,id',
            'jawaban.*.skor' => 'required|integer|min:1|max:5',
            'jawaban.*.komentar' => 'nullable|string|max:1000',
            'komentar_umum' => 'nullable|string|max:2000'
        ]);

        $penilaian = Penilaian::findOrFail($request->penilaian_id);
        
        // Validasi ownership
        if ($penilaian->penilai_id_user !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk penilaian ini');
        }

        DB::transaction(function() use ($request, $penilaian) {
            // Hapus jawaban lama
            $penilaian->jawabanKuisioner()->delete();
            
            // Simpan jawaban baru
            foreach ($request->jawaban as $jawaban) {
                JawabanKuisioner::create([
                    'penilaian_id' => $penilaian->id,
                    'kuisioner_id' => $jawaban['kuisioner_id'],
                    'skor' => $jawaban['skor'],
                    'komentar' => $jawaban['komentar'] ?? null
                ]);
            }
            
            // Update komentar umum dan status
            $penilaian->update([
                'komentar' => $request->komentar_umum,
                'status' => $request->has('submit_final') ? 'selesai' : 'draft',
                'tanggal_penilaian' => $request->has('submit_final') ? now() : null
            ]);
            
            // Update total nilai
            $penilaian->updateTotalNilai();
        });

        $message = $request->has('submit_final') 
            ? 'Penilaian berhasil diselesaikan dan dikirim' 
            : 'Draft penilaian berhasil disimpan';
            
        return redirect()->route('penilaian.karyawan')->with('success', $message);
    }

    /**
     * Menampilkan detail hasil penilaian
     */
    public function show($id)
    {
        $penilaian = Penilaian::with([
            'periode',
            'penilai.pegawai.departemen',
            'dinilai.pegawai.departemen',
            'jawabanKuisioner.kuisioner'
        ])->findOrFail($id);
        
        $user = Auth::user();
        
        // Validasi akses
        if ($user->role !== 'admin' && 
            $penilaian->penilai_id_user !== $user->id_user && 
            $penilaian->dinilai_id_user !== $user->id_user) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat penilaian ini');
        }
        
        return view('penilaian.show', compact('penilaian'));
    }

    /**
     * Generate penilaian untuk periode tertentu
     * (Biasanya dijalankan oleh admin)
     */
    public function generatePenilaian($periodeId)
    {
        $periode = PeriodePenilaian::findOrFail($periodeId);
        $departemen = Departemen::with('pegawai.user')->get();
        
        $totalGenerated = 0;
        
        DB::transaction(function() use ($departemen, $periodeId, &$totalGenerated) {
            foreach ($departemen as $dept) {
                $pegawaiDepartemen = $dept->pegawai->whereNotNull('user');
                
                foreach ($pegawaiDepartemen as $penilai) {
                    foreach ($pegawaiDepartemen as $dinilai) {
                        // Skip jika menilai diri sendiri
                        if ($penilai->id_pegawai === $dinilai->id_pegawai) {
                            continue;
                        }
                        
                        // Cek apakah sudah ada penilaian
                        $exists = Penilaian::where([
                            'periode_id' => $periodeId,
                            'penilai_id_user' => $penilai->user->id_user,
                            'dinilai_id_user' => $dinilai->user->id_user
                        ])->exists();
                        
                        if (!$exists) {
                            Penilaian::create([
                                'periode_id' => $periodeId,
                                'penilai_id_user' => $penilai->user->id_user,
                                'dinilai_id_user' => $dinilai->user->id_user,
                                'status' => 'belum_diisi',
                                'total_nilai' => 0
                            ]);
                            $totalGenerated++;
                        }
                    }
                }
            }
        });
        
        return redirect()->back()->with('success', "Berhasil generate {$totalGenerated} penilaian untuk periode {$periode->nama_periode}");
    }

    /**
     * Mendapatkan kuisioner sesuai user (departemen & golongan)
     */
    private function getKuisionerForUser($userId, $periodeId)
    {
        $user = User::with('pegawai')->find($userId);
        
        return Kuisioner::aktif()
            ->whereHas('periodePenilaian', function($q) use ($periodeId) {
                $q->where('periode_penilaian.id', $periodeId);
            })
            ->where(function($q) use ($user) {
                $q->whereNull('departemen_id')
                  ->orWhere('departemen_id', $user->pegawai->id_departemen);
            })
            ->where(function($q) use ($user) {
                $q->whereNull('golongan')
                  ->orWhere('golongan', $user->pegawai->golongan);
            })
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();
    }

    /**
     * API: Mendapatkan progress penilaian untuk dashboard
     */
    public function getProgressPenilaian($periodeId)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        $query = Penilaian::where('periode_id', $periodeId);
        
        if ($user->role !== 'admin') {
            $query->whereHas('dinilai.pegawai', function($q) use ($pegawai) {
                $q->where('id_departemen', $pegawai->id_departemen);
            });
        }
        
        $total = $query->count();
        $selesai = $query->where('status', 'selesai')->count();
        $belumDiisi = $query->where('status', 'belum_diisi')->count();
        
        return response()->json([
            'total' => $total,
            'selesai' => $selesai,
            'belum_diisi' => $belumDiisi,
            'progress_percentage' => $total > 0 ? round(($selesai / $total) * 100, 2) : 0
        ]);
    }
}