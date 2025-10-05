<?php

namespace App\Http\Controllers;

use App\Models\Kuisioner;
use Illuminate\Http\Request;
use App\Models\PeriodePenilaian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeriodeKuisionerController extends Controller
{
    /**
     * Tampilkan halaman pengaturan kuisioner untuk periode tertentu
     */
    public function index($periodeId)
    {
        try {
            // Ambil periode berdasarkan ID dengan eager loading
            $periode = PeriodePenilaian::with(['kuisioner' => function($query) {
                $query->select('kuisioner.id', 'kuisioner.kategori', 'kuisioner.pertanyaan');
            }])->findOrFail($periodeId);
            
            // Ambil semua kuisioner aktif
            $semuaKuisioner = Kuisioner::where('aktif', 1)
                ->select('id', 'kategori', 'pertanyaan', 'aktif')
                ->orderBy('kategori')
                ->orderBy('id')
                ->get();
            
            // Ambil kuisioner yang sudah dipilih untuk periode ini
            $kuisionerTerpilih = $periode->kuisioner->pluck('id')->toArray();
            
            // Group kuisioner berdasarkan kategori
            $kuisionerByKategori = $semuaKuisioner->groupBy('kategori');

            // Hitung statistik
            $totalKuisioner = $semuaKuisioner->count();
            $kuisionerDipilih = count($kuisionerTerpilih);
            
            // Ambil data user dan departemen
            $user = Auth::user();
            $pegawai = $user->pegawai ?? null;
            $nama_departemen = $pegawai->departemen->nama_departemen ?? '';
            
            return view('admin.periode.kuisioner', compact(
                'periode', 
                'kuisionerByKategori', 
                'kuisionerTerpilih',
                'totalKuisioner',
                'kuisionerDipilih',
                'pegawai',
                'nama_departemen'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading kuisioner index: ' . $e->getMessage());
            return redirect()->route('admin.periode.index')
                ->with('error', 'Periode tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update kuisioner yang dipilih untuk periode tertentu
     */
    public function update(Request $request, $periodeId)
    {
        DB::beginTransaction();
        
        try {
            // Ambil periode berdasarkan ID
            $periode = PeriodePenilaian::findOrFail($periodeId);
            
            // Check if period is still editable
            if ($periode->status === 'selesai') {
                return redirect()->back()->with('error', 'Periode sudah selesai, tidak dapat diubah.');
            }
            
            // Validasi input
            $request->validate([
                'kuisioner_ids' => 'nullable|array',
                'kuisioner_ids.*' => 'exists:kuisioner,id'
            ]);
            
            $kuisionerIds = $request->input('kuisioner_ids', []);
            
            // Validate that all selected questionnaires are active
            if (!empty($kuisionerIds)) {
                $activeKuisionerIds = Kuisioner::whereIn('id', $kuisionerIds)
                    ->where('aktif', 1)
                    ->pluck('id')
                    ->toArray();
                
                if (count($activeKuisionerIds) !== count($kuisionerIds)) {
                    return redirect()->back()->with('error', 'Beberapa kuisioner yang dipilih tidak aktif.');
                }
            }
            
            // Sync kuisioner dengan periode
            $periode->kuisioner()->sync($kuisionerIds);
            
            DB::commit();
            
            return redirect()->back()->with('success', 
                'Kuisioner berhasil diperbarui. Total ' . count($kuisionerIds) . ' kuisioner dipilih.'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating kuisioner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui kuisioner: ' . $e->getMessage());
        }
    }

    /**
     * Reset semua kuisioner dari periode
     */
    public function reset($periodeId)
    {
        DB::beginTransaction();
        
        try {
            // Ambil periode berdasarkan ID
            $periode = PeriodePenilaian::findOrFail($periodeId);
            
            // Check if period is still editable
            if ($periode->status === 'selesai') {
                return redirect()->back()->with('error', 'Periode sudah selesai, tidak dapat diubah.');
            }
            
            $jumlahKuisioner = $periode->kuisioner()->count();
            
            if ($jumlahKuisioner === 0) {
                return redirect()->back()->with('info', 'Tidak ada kuisioner untuk dihapus.');
            }
            
            // Hapus semua relasi kuisioner
            $periode->kuisioner()->detach();

            DB::commit();

            return redirect()->back()->with('success', 
                $jumlahKuisioner . ' kuisioner berhasil dihapus dari periode.'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resetting kuisioner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kuisioner: ' . $e->getMessage());
        }
    }

    /**
     * Auto select kuisioner berdasarkan kategori tertentu
     */
    public function autoSelect(Request $request, $periodeId)
    {
        DB::beginTransaction();
        
        try {
            // Ambil periode berdasarkan ID
            $periode = PeriodePenilaian::findOrFail($periodeId);
            
            // Check if period is still editable
            if ($periode->status === 'selesai') {
                return redirect()->back()->with('error', 'Periode sudah selesai, tidak dapat diubah.');
            }
            
            // Validasi input
            $request->validate([
                'kategori' => 'required|string',
                'action' => 'in:add,replace'
            ]);
            
            $kategori = $request->input('kategori');
            $action = $request->input('action', 'add');

            // Ambil kuisioner berdasarkan kategori
            $kuisionerKategori = Kuisioner::where('aktif', 1)
                ->where('kategori', $kategori)
                ->pluck('id')
                ->toArray();

            if (empty($kuisionerKategori)) {
                return redirect()->back()->with('error', 
                    'Tidak ada kuisioner aktif untuk kategori ' . ucwords($kategori) . '.'
                );
            }

            if ($action === 'replace') {
                // Ganti semua dengan kuisioner kategori ini
                $periode->kuisioner()->sync($kuisionerKategori);
                $message = 'Kuisioner diganti dengan kategori ' . ucwords($kategori) . '. Total ' . count($kuisionerKategori) . ' kuisioner dipilih.';
            } else {
                // Tambah kuisioner kategori ini
                $existingIds = $periode->kuisioner()->pluck('kuisioner_id')->toArray();
                $newIds = array_diff($kuisionerKategori, $existingIds);
                $allIds = array_unique(array_merge($existingIds, $kuisionerKategori));
                $periode->kuisioner()->sync($allIds);
                $newlyAdded = count($newIds);
                $message = 'Kuisioner kategori ' . ucwords($kategori) . ' berhasil ditambahkan. ' . $newlyAdded . ' kuisioner baru ditambahkan.';
            }

            DB::commit();

            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error auto selecting kuisioner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses kuisioner: ' . $e->getMessage());
        }
    }
}