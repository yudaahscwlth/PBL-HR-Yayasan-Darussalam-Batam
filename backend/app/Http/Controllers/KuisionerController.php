<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kuisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class KuisionerController extends Controller
{
    /**
     * Menampilkan daftar kuisioner dengan fitur pencarian dan filter.
     */
    public function index(Request $request)
    {
        $query = Kuisioner::query();

        // Fitur pencarian berdasarkan pertanyaan
        if ($request->filled('search')) {
            $query->cariPertanyaan($request->search);
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->kategori($request->kategori);
        }

        // Filter berdasarkan status aktif/non-aktif
        if ($request->filled('status')) {
            if ($request->status == '1') {
                $query->aktif();
            } elseif ($request->status == '0') {
                $query->nonAktif();
            }
        }

        // Urutkan berdasarkan waktu pembuatan terbaru
        $query->orderBy('created_at', 'desc');

        // Paginasi dengan mempertahankan parameter filter
        $kuisioners = $query->paginate(10)->withQueryString();

        // Ambil statistik kuisioner
        $totalKuisioner = Kuisioner::count();
        $kuisionerAktif = Kuisioner::aktif()->count();
        $kuisionerNonAktif = Kuisioner::nonAktif()->count();
        $kategoris = Kuisioner::getAllKategori();

        // Ambil data user dan pegawai
        $user = Auth::user();
        $pegawai = $user->pegawai ?? null;
        $nama_departemen = $pegawai ? $pegawai->departemen->nama_departemen : null;

        return view('admin.kuisioner.index', compact(
            'kuisioners',
            'totalKuisioner',
            'kuisionerAktif',
            'kuisionerNonAktif',
            'kategoris',
            'pegawai',
            'nama_departemen'
        ));
    }

    /**
     * Menampilkan form untuk membuat kuisioner baru.
     */
    public function create()
    {
        $kategoris = Kuisioner::getAllKategori();
        return view('admin.kuisioner.create', compact('kategoris'));
    }

    /**
     * Menyimpan kuisioner baru ke database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori' => 'required|string|max:100',
            'pertanyaan' => 'required|string|max:1000',
            'aktif' => 'nullable|boolean'
        ], [
            'kategori.required' => 'Kategori wajib diisi',
            'kategori.max' => 'Kategori maksimal 100 karakter',
            'pertanyaan.required' => 'Pertanyaan wajib diisi',
            'pertanyaan.max' => 'Pertanyaan maksimal 1000 karakter'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Kuisioner::create([
                'kategori' => $request->kategori,
                'pertanyaan' => $request->pertanyaan,
                'aktif' => $request->has('aktif') ? true : false
            ]);

            return redirect()->route('admin.kuisioner.index')
                ->with('success', 'Kuisioner berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail kuisioner beserta statistiknya.
     */
    public function show(Kuisioner $kuisioner)
    {
        // Ambil statistik untuk kuisioner ini
        $totalJawaban = $kuisioner->getJumlahJawaban();
        $rataRataSkor = $kuisioner->getRataRataSkor();
        $distribusiSkor = $kuisioner->getDistribusiSkor();

        return view('admin.kuisioner.show', compact(
            'kuisioner',
            'totalJawaban',
            'rataRataSkor',
            'distribusiSkor'
        ));
    }

    /**
     * Menampilkan form edit kuisioner dalam tampilan index.
     */
    public function edit(Request $request, Kuisioner $kuisioner)
    {
        $query = Kuisioner::query();

        // Terapkan filter yang sama seperti di index
        if ($request->filled('search')) {
            $query->cariPertanyaan($request->search);
        }

        if ($request->filled('kategori')) {
            $query->kategori($request->kategori);
        }

        if ($request->filled('status')) {
            if ($request->status == '1') {
                $query->aktif();
            } elseif ($request->status == '0') {
                $query->nonAktif();
            }
        }

        $query->orderBy('created_at', 'desc');
        
        // Pastikan halaman saat ini tetap dipertahankan
        $kuisioners = $query->paginate(10)->withQueryString();
        
        // Ambil data statistik
        $totalKuisioner = Kuisioner::count();
        $kuisionerAktif = Kuisioner::aktif()->count();
        $kuisionerNonAktif = Kuisioner::nonAktif()->count();
        $kategoris = Kuisioner::getAllKategori();
        
        // Set kuisioner yang akan diedit
        $editKuisioner = $kuisioner;

        // Ambil data user dan pegawai
        $user = Auth::user();
        $pegawai = $user->pegawai ?? null;
        $nama_departemen = $pegawai ? $pegawai->departemen->nama_departemen : null;

        return view('admin.kuisioner.index', compact(
            'kuisioners',
            'totalKuisioner',
            'kuisionerAktif',
            'kuisionerNonAktif',
            'kategoris',
            'editKuisioner',
            'pegawai',
            'nama_departemen'
        ));
    }

    /**
     * Memperbarui data kuisioner yang sudah ada.
     */
    public function update(Request $request, Kuisioner $kuisioner)
    {
        $validator = Validator::make($request->all(), [
            'kategori' => 'required|string|max:100',
            'pertanyaan' => 'required|string|max:1000',
            'aktif' => 'nullable|boolean'
        ], [
            'kategori.required' => 'Kategori wajib diisi',
            'kategori.max' => 'Kategori maksimal 100 karakter',
            'pertanyaan.required' => 'Pertanyaan wajib diisi',
            'pertanyaan.max' => 'Pertanyaan maksimal 1000 karakter'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $kuisioner->update([
                'kategori' => $request->kategori,
                'pertanyaan' => $request->pertanyaan,
                'aktif' => $request->has('aktif') ? true : false
            ]);

            return redirect()->route('admin.kuisioner.index')
                ->with('success', 'Kuisioner berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menghapus kuisioner dari database.
     */
    public function destroy(Kuisioner $kuisioner)
    {
        try {
            // Cek apakah kuisioner memiliki jawaban terkait
            $hasAnswers = $kuisioner->jawabanKuisioner()->exists();
            
            if ($hasAnswers) {
                return redirect()->back()
                    ->with('error', 'Kuisioner tidak dapat dihapus karena sudah memiliki jawaban!');
            }

            // Cek apakah kuisioner digunakan dalam periode penilaian
            $hasPeriodesUsed = $kuisioner->periodePenilaian()->exists();
            
            if ($hasPeriodesUsed) {
                return redirect()->back()
                    ->with('error', 'Kuisioner tidak dapat dihapus karena sedang digunakan dalam periode penilaian!');
            }

            $kuisioner->delete();

            return redirect()->route('admin.kuisioner.index')
                ->with('success', 'Kuisioner berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah status aktif/non-aktif kuisioner.
     */
    public function toggle(Kuisioner $kuisioner)
    {
        try {
            $kuisioner->toggleAktif();
            
            $status = $kuisioner->aktif ? 'diaktifkan' : 'dinonaktifkan';
            
            return redirect()->back()
                ->with('success', "Kuisioner berhasil {$status}!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mengambil statistik kuisioner melalui AJAX.
     */
    public function getStatistics(Request $request)
    {
        try {
            $statistics = [
                'total' => Kuisioner::count(),
                'aktif' => Kuisioner::aktif()->count(),
                'nonAktif' => Kuisioner::nonAktif()->count(),
                'kategoris' => Kuisioner::getAllKategori()
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aksi massal untuk beberapa kuisioner sekaligus.
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:kuisioner,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid'
            ], 422);
        }

        try {
            $kuisioners = Kuisioner::whereIn('id', $request->ids);
            $count = $kuisioners->count();

            switch ($request->action) {
                case 'activate':
                    $kuisioners->update(['aktif' => true]);
                    $message = "{$count} kuisioner berhasil diaktifkan";
                    break;

                case 'deactivate':
                    $kuisioners->update(['aktif' => false]);
                    $message = "{$count} kuisioner berhasil dinonaktifkan";
                    break;

                case 'delete':
                    // Cek apakah ada yang memiliki jawaban atau digunakan dalam periode
                    $hasRelations = $kuisioners->whereHas('jawabanKuisioner')
                        ->orWhereHas('periodePenilaian')
                        ->exists();

                    if ($hasRelations) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Beberapa kuisioner tidak dapat dihapus karena memiliki relasi dengan data lain'
                        ], 422);
                    }

                    $kuisioners->delete();
                    $message = "{$count} kuisioner berhasil dihapus";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengekspor data kuisioner ke Excel/CSV.
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'excel'); // excel atau csv
            
            $kuisioners = Kuisioner::with(['periodePenilaian', 'jawabanKuisioner'])
                ->orderBy('kategori')
                ->orderBy('created_at', 'desc')
                ->get();

            // Implementasi tergantung library export yang digunakan (misalnya Laravel Excel)
            // Ini hanya struktur placeholder

            return response()->json([
                'success' => true,
                'message' => 'Ekspor sedang diproses',
                'download_url' => route('admin.kuisioner.download', ['format' => $format])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}