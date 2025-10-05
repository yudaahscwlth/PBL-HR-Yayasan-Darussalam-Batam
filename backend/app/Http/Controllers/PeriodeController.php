<?php

namespace App\Http\Controllers;

use App\Models\Kuisioner;
use Illuminate\Http\Request;
use App\Models\PeriodePenilaian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PeriodeController extends Controller
{
    /**
     * Helper method untuk mengambil data pegawai dan departemen
     */
    private function getPegawaiData()
    {
        $user = Auth::user();
        $pegawai = null;
        $nama_departemen = '';
        
        if ($user) {
            try {
                $pegawai = $user->pegawai ?? null;
                if ($pegawai && $pegawai->departemen) {
                    $nama_departemen = $pegawai->departemen->nama_departemen ?? '';
                }
            } catch (\Exception $e) {
                Log::warning('Error loading pegawai/departemen data: ' . $e->getMessage());
                // Continue without pegawai data
            }
        }
        
        return compact('pegawai', 'nama_departemen');
    }

    /**
     * Tampilkan halaman daftar periode penilaian
     */
    public function index()
    {
        try {
            // Ambil semua periode dengan urutan terbaru
            $periodes = PeriodePenilaian::orderBy('tahun', 'desc')
                ->orderBy('semester', 'desc')
                ->get();
            
            // Tambahkan property status_badge untuk setiap periode
            $periodes->each(function($periode) {
                switch($periode->status) {
                    case 'aktif':
                        $periode->status_badge = 'success';
                        break;
                    case 'belum_dibuka':
                        $periode->status_badge = 'secondary';
                        break;
                    case 'selesai':
                        $periode->status_badge = 'primary';
                        break;
                    default:
                        $periode->status_badge = 'secondary';
                }
                
                // Format tanggal untuk tampilan - perbaiki handling null
                if ($periode->tanggal_mulai) {
                    try {
                        $periode->tanggal_mulai_formatted = Carbon::parse($periode->tanggal_mulai)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $periode->tanggal_mulai_formatted = '-';
                    }
                } else {
                    $periode->tanggal_mulai_formatted = '-';
                }
                
                if ($periode->tanggal_selesai) {
                    try {
                        $periode->tanggal_selesai_formatted = Carbon::parse($periode->tanggal_selesai)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $periode->tanggal_selesai_formatted = '-';
                    }
                } else {
                    $periode->tanggal_selesai_formatted = '-';
                }
                
                // Cek apakah periode sudah expired
                if ($periode->tanggal_selesai) {
                    try {
                        $periode->is_expired = Carbon::now()->gt(Carbon::parse($periode->tanggal_selesai));
                    } catch (\Exception $e) {
                        $periode->is_expired = false;
                    }
                } else {
                    $periode->is_expired = false;
                }
            });
            
            // Ambil data pegawai dan departemen
            $pegawaiData = $this->getPegawaiData();
            
            return view('admin.periode.index', array_merge(compact('periodes'), $pegawaiData));
            
        } catch (\Exception $e) {
            Log::error('Error loading periode index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return view dengan data kosong jika terjadi error
            return view('admin.periode.index', [
                'periodes' => collect([]),
                'nama_departemen' => '',
                'pegawai' => null
            ])->with('error', 'Gagal memuat data periode: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan form create periode
     */
    public function create()
    {
        try {
            // Ambil data pegawai dan departemen
            $pegawaiData = $this->getPegawaiData();
            
            return view('admin.periode.create', $pegawaiData);
        } catch (\Exception $e) {
            Log::error('Error loading create periode form: ' . $e->getMessage());
            return redirect()->route('periode.index')
                ->with('error', 'Gagal memuat form create periode.');
        }
    }

    /**
     * Simpan periode baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_periode' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2020|max:2030',
            'semester' => 'required|in:1,2',
            'status' => 'required|in:belum_dibuka,aktif,selesai',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai'
        ], [
            'nama_periode.required' => 'Nama periode harus diisi.',
            'nama_periode.string' => 'Nama periode harus berupa teks.',
            'nama_periode.max' => 'Nama periode maksimal 255 karakter.',
            'tahun.required' => 'Tahun harus diisi.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2030.',
            'semester.required' => 'Semester harus dipilih.',
            'semester.in' => 'Semester harus 1 atau 2.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status harus salah satu dari: belum_dibuka, aktif, atau selesai.',
            'tanggal_mulai.required' => 'Tanggal mulai harus diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai harus diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.'
        ]);

        DB::beginTransaction();
        
        try {
            // Cek duplikasi periode untuk tahun dan semester yang sama
            $existingPeriode = PeriodePenilaian::where('tahun', $request->tahun)
                ->where('semester', $request->semester)
                ->first();
                
            if ($existingPeriode) {
                return redirect()->back()
                    ->with('error', 'Periode untuk tahun ' . $request->tahun . ' semester ' . $request->semester . ' sudah ada.')
                    ->withInput();
            }

            PeriodePenilaian::create([
                'nama_periode' => $request->nama_periode,
                'tahun' => $request->tahun,
                'semester' => $request->semester,
                'status' => $request->status,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai
            ]);

            DB::commit();
            
            return redirect()->route('periode.index')
                ->with('success', 'Periode berhasil dibuat.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating periode: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membuat periode: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Tampilkan detail periode
     */
    public function show($id)
    {
        try {
            $periode = PeriodePenilaian::with('kuisioner')
                ->findOrFail($id);
            
            // Format tanggal
            if ($periode->tanggal_mulai) {
                $periode->tanggal_mulai_formatted = Carbon::parse($periode->tanggal_mulai)->format('d/m/Y');
            }
            if ($periode->tanggal_selesai) {
                $periode->tanggal_selesai_formatted = Carbon::parse($periode->tanggal_selesai)->format('d/m/Y');
            }
            
            // Ambil data pegawai dan departemen
            $pegawaiData = $this->getPegawaiData();
            
            return view('admin.periode.show', array_merge(compact('periode'), $pegawaiData));
            
        } catch (\Exception $e) {
            Log::error('Error loading periode detail: ' . $e->getMessage());
            return redirect()->route('periode.index')
                ->with('error', 'Periode tidak ditemukan.');
        }
    }

    /**
     * Tampilkan form edit periode
     */
    public function edit($id)
    {
        try {
            $periode = PeriodePenilaian::findOrFail($id);
            
            // Ambil data pegawai dan departemen
            $pegawaiData = $this->getPegawaiData();
            
            return view('admin.periode.edit', array_merge(compact('periode'), $pegawaiData));
            
        } catch (\Exception $e) {
            Log::error('Error loading edit periode form: ' . $e->getMessage());
            return redirect()->route('periode.index')
                ->with('error', 'Periode tidak ditemukan.');
        }
    }

    /**
     * Update periode
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_periode' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2020|max:2030',
            'semester' => 'required|in:1,2',
            'status' => 'required|in:belum_dibuka,aktif,selesai',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai'
        ], [
            'nama_periode.required' => 'Nama periode harus diisi.',
            'nama_periode.string' => 'Nama periode harus berupa teks.',
            'nama_periode.max' => 'Nama periode maksimal 255 karakter.',
            'tahun.required' => 'Tahun harus diisi.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2030.',
            'semester.required' => 'Semester harus dipilih.',
            'semester.in' => 'Semester harus 1 atau 2.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status harus salah satu dari: belum_dibuka, aktif, atau selesai.',
            'tanggal_mulai.required' => 'Tanggal mulai harus diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai harus diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.'
        ]);

        DB::beginTransaction();
        
        try {
            $periode = PeriodePenilaian::findOrFail($id);
            
            // Cek duplikasi periode untuk tahun dan semester yang sama (kecuali yang sedang diedit)
            $existingPeriode = PeriodePenilaian::where('tahun', $request->tahun)
                ->where('semester', $request->semester)
                ->where('id', '!=', $id)
                ->first();
                
            if ($existingPeriode) {
                return redirect()->back()
                    ->with('error', 'Periode untuk tahun ' . $request->tahun . ' semester ' . $request->semester . ' sudah ada.')
                    ->withInput();
            }

            $periode->update([
                'nama_periode' => $request->nama_periode,
                'tahun' => $request->tahun,
                'semester' => $request->semester,
                'status' => $request->status,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai
            ]);

            DB::commit();
            
            return redirect()->route('periode.index')
                ->with('success', 'Periode berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating periode: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui periode: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hapus periode
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $periode = PeriodePenilaian::findOrFail($id);
            
            // Cek apakah periode memiliki kuisioner yang sudah diisi
            $hasResponses = $periode->kuisioner()
                ->whereHas('responses')
                ->exists();
                
            if ($hasResponses) {
                return redirect()->back()
                    ->with('error', 'Periode tidak dapat dihapus karena sudah memiliki data penilaian.');
            }
            
            // Hapus relasi kuisioner periode terlebih dahulu
            $periode->kuisioner()->detach();
            
            // Hapus periode
            $periode->delete();
            
            DB::commit();
            
            return redirect()->route('periode.index')
                ->with('success', 'Periode berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting periode: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus periode: ' . $e->getMessage());
        }
    }

    /**
     * Aktifkan periode dan nonaktifkan periode lain
     */
    public function activate($id)
    {
        DB::beginTransaction();
        
        try {
            // Nonaktifkan semua periode yang aktif
            PeriodePenilaian::where('status', 'aktif')
                ->update(['status' => 'selesai']);
            
            // Aktifkan periode yang dipilih
            $periode = PeriodePenilaian::findOrFail($id);
            $periode->update(['status' => 'aktif']);
            
            DB::commit();
            
            return redirect()->route('periode.index')
                ->with('success', 'Periode berhasil diaktifkan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error activating periode: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengaktifkan periode: ' . $e->getMessage());
        }
    }

    /**
     * Selesaikan periode
     */
    public function complete($id)
    {
        try {
            $periode = PeriodePenilaian::findOrFail($id);
            
            $periode->update(['status' => 'selesai']);
            
            return redirect()->route('periode.index')
                ->with('success', 'Periode berhasil diselesaikan.');
                
        } catch (\Exception $e) {
            Log::error('Error completing periode: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menyelesaikan periode: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan halaman assign kuisioner ke periode
     */
    public function kuisionerIndex($periodeId)
    {
        try {
            $periode = PeriodePenilaian::findOrFail($periodeId);
            
            // Ambil semua kuisioner
            $kuisioners = Kuisioner::all();
            
            // Ambil kuisioner yang sudah di-assign ke periode ini
            $assignedKuisioners = $periode->kuisioner()->pluck('kuisioner_id')->toArray();
            
            // Ambil data pegawai dan departemen
            $pegawaiData = $this->getPegawaiData();
            
            return view('admin.periode.kuisioner.index', array_merge(compact(
                'periode', 
                'kuisioners', 
                'assignedKuisioners'
            ), $pegawaiData));
            
        } catch (\Exception $e) {
            Log::error('Error loading periode kuisioner: ' . $e->getMessage());
            return redirect()->route('periode.index')
                ->with('error', 'Periode tidak ditemukan.');
        }
    }

    /**
     * Assign kuisioner ke periode
     */
    public function assignKuisioner(Request $request, $periodeId)
    {
        $request->validate([
            'kuisioner_ids' => 'required|array',
            'kuisioner_ids.*' => 'exists:kuisioners,id'
        ], [
            'kuisioner_ids.required' => 'Pilih minimal satu kuisioner.',
            'kuisioner_ids.array' => 'Format kuisioner tidak valid.',
            'kuisioner_ids.*.exists' => 'Kuisioner tidak ditemukan.'
        ]);

        DB::beginTransaction();
        
        try {
            $periode = PeriodePenilaian::findOrFail($periodeId);
            
            // Sync kuisioner dengan periode
            $periode->kuisioner()->sync($request->kuisioner_ids);
            
            DB::commit();
            
            return redirect()->route('periode.kuisioner.index', $periodeId)
                ->with('success', 'Kuisioner berhasil di-assign ke periode.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning kuisioner to periode: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal meng-assign kuisioner: ' . $e->getMessage());
        }
    }

    /**
     * Get periode aktif
     */
    public static function getActivePeriode()
    {
        return PeriodePenilaian::where('status', 'aktif')->first();
    }

    /**
     * Cek apakah periode masih aktif berdasarkan tanggal
     */
    public function checkPeriodeStatus()
    {
        try {
            $now = Carbon::now();
            
            // Update status periode yang sudah expired
            PeriodePenilaian::where('status', 'aktif')
                ->where('tanggal_selesai', '<', $now)
                ->update(['status' => 'selesai']);
            
            // Update status periode yang sudah mulai
            PeriodePenilaian::where('status', 'belum_dibuka')
                ->where('tanggal_mulai', '<=', $now)
                ->where('tanggal_selesai', '>=', $now)
                ->update(['status' => 'aktif']);
            
            return response()->json(['message' => 'Status periode berhasil diperbarui.']);
            
        } catch (\Exception $e) {
            Log::error('Error checking periode status: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memperbarui status periode.'], 500);
        }
    }
}