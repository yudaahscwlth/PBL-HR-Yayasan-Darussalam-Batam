<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KaryawanController extends Controller
{
    /**
     * Menampilkan daftar semua karyawan dengan filter dan pagination
     */
     public function index(Request $request)
{
    try {
        $pegawai = Auth::user()->pegawai;
        $nama_departemen = $pegawai?->departemen?->nama_departemen ?? '';

        // Query builder untuk pegawai
        $query = Pegawai::with(['jabatan', 'departemen']);

        // Filter berdasarkan nama
        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%' . $request->nama . '%');
        }

        // Filter berdasarkan departemen
        if ($request->filled('departemen')) {
            $query->where('id_departemen', $request->departemen);
        }

        // Filter berdasarkan jabatan
        if ($request->filled('jabatan')) {
            $query->where('id_jabatan', $request->jabatan);
        }

        // Filter berdasarkan jenis kelamin
        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter berdasarkan tanggal masuk
        if ($request->filled('tanggal_masuk_dari')) {
            $query->where('tanggal_masuk', '>=', $request->tanggal_masuk_dari);
        }

        if ($request->filled('tanggal_masuk_sampai')) {
            $query->where('tanggal_masuk', '<=', $request->tanggal_masuk_sampai);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');

        if (in_array($sortBy, ['nama', 'jenis_kelamin', 'tanggal_masuk'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('nama', 'asc');
        }

        $karyawan = $query->paginate(10)->withQueryString();
        return view('admin.karyawan', [
            'karyawan' => $karyawan,
            'pegawai' => $pegawai,
            'nama_departemen' => $nama_departemen,
            'departemen' => Departemen::all(),
            'jabatan' => Jabatan::all(),
        ]);
    } catch (\Exception $e) {
        Log::error('Error saat memuat halaman karyawan: ' . $e->getMessage());
        
        return back()->with([
            'notifikasi' => 'Terjadi kesalahan saat memuat data karyawan.',
            'type' => 'error'
        ]);
    }
}
    /**
     * Menampilkan form untuk menambah karyawan baru
     */
    public function create()
    {
        try {
            return view('data_karyawan', [
                'jabatan' => Jabatan::all(),
                'departemen' => Departemen::all(),
                'pegawai' => Pegawai::all(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat memuat form tambah karyawan: ' . $e->getMessage());
            
            return redirect()->route('admin.karyawan')->with([
                'notifikasi' => 'Terjadi kesalahan saat memuat form.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Menyimpan data karyawan baru ke database
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validasiDataPegawai($request);
            
            // Proses upload foto jika ada, jika tidak ada gunakan default
            if ($request->hasFile('foto')) {
                $validated['foto'] = $this->uploadFoto($request->file('foto'));
            } else {
                // Gunakan foto default
                $validated['foto'] = 'avatar-1.jpg';
            }

            $user = Auth::user();
            // Simpan data pegawai
            DB::statement("SET @current_user_id = " . $user->id_user);

            $pegawai = Pegawai::create($validated);
               
            Log::info('Pegawai berhasil dibuat dengan ID: ' . $pegawai->id_pegawai);

            return redirect()->back()->with('success', 'Data pegawai berhasil ditambahkan.');


        } catch (ValidationException $e) {
            Log::error('Error validasi: ', $e->errors());
            
            // Kumpulkan semua pesan error validasi
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = $message;
                }
            }
            
            return back()->withErrors($e->validator)
                         ->withInput()
                         ->with([
                             'alert' => [
                                 'type' => 'error',
                                 'title' => 'Validasi Gagal!',
                                 'message' => 'Terdapat kesalahan dalam pengisian data:',
                                 'errors' => $errorMessages
                             ]
                         ]);
                         
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pegawai: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Hapus foto yang sudah diupload jika terjadi error (kecuali foto default)
            if (isset($validated['foto']) && $validated['foto'] !== 'avatar-1.jpg') {
                $this->hapusFoto($validated['foto']);
            }

            return back()->withInput()->with([
                'alert' => [
                    'type' => 'error',
                    'title' => 'Gagal Menyimpan!',
                    'message' => 'Terjadi kesalahan saat menyimpan data pegawai. Silakan coba lagi.',
                    'technical_error' => config('app.debug') ? $e->getMessage() : null
                ]
            ]);
        }
    }

    /**
     * Menampilkan detail karyawan
     */
    public function show($id)
    {
        try {
            $pegawai = Auth::user()->pegawai;
            $pegawai2 = Pegawai::with(['jabatan', 'departemen'])->findOrFail($id);
            
            // Hitung masa kerja dan umur
            $masaKerja = Carbon::parse($pegawai2->tanggal_masuk)->diffForHumans(null, true);
            $umur = Carbon::parse($pegawai2->tanggal_lahir)->age;
            $nama_departemen = $pegawai->departemen->nama_departemen ?? '';
            
            return view('admin.detail-pegawai', [
                'pegawai2' => $pegawai2,
                'pegawai' => $pegawai,
                'masaKerja' => $masaKerja,
                'umur' => $umur,
                'nama_departemen' => $nama_departemen,
                'jabatan' => Jabatan::all(),
                'departemen' => Departemen::all(),
            ]);
            
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.karyawan')->with([
                'notifikasi' => 'Data pegawai tidak ditemukan!',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan detail pegawai: ' . $e->getMessage());
            
            return redirect()->route('admin.karyawan')->with([
                'notifikasi' => 'Terjadi kesalahan saat menampilkan data pegawai!',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Menampilkan form untuk edit karyawan
     */
    public function edit($id)
    {
        try {
            $pegawai = Auth::user()->pegawai;
            $pegawai2 = Pegawai::with(['jabatan', 'departemen'])->findOrFail($id);
            
            return view('admin.edit-pegawai', [
                'pegawai' => $pegawai,
                'pegawai2' => $pegawai2,
                'jabatan' => Jabatan::all(),
                'departemen' => Departemen::all(),
                'nama_departemen' => $pegawai->departemen->nama_departemen ?? '',
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.karyawan')->with([
                'notifikasi' => 'Data pegawai tidak ditemukan!',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat memuat form edit: ' . $e->getMessage());
            
            return redirect()->route('admin.karyawan')->with([
                'notifikasi' => 'Terjadi kesalahan saat memuat form edit!',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Update data karyawan
     */
    public function update(Request $request, $id)
    {
        try {
            $pegawai = Pegawai::findOrFail($id);
            $validated = $this->validasiDataPegawai($request, $id);

            // Proses upload foto baru jika ada
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika bukan foto default
                if ($pegawai->foto && $pegawai->foto !== 'avatar-1.jpg') {
                    $this->hapusFoto($pegawai->foto);
                }
                
                // Upload foto baru
                $validated['foto'] = $this->uploadFoto($request->file('foto'));
            }

            // Update data pegawai
            $pegawai->update($validated);

            return redirect()->route('admin.karyawan')->with('success', 'Data pegawai berhasil diperbarui.');


        } catch (ValidationException $e) {
            // Kumpulkan semua pesan error validasi
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = $message;
                }
            }
            
            return back()->withErrors($e->validator)
                         ->withInput()
                         ->with([
                             'alert' => [
                                 'type' => 'error',
                                 'title' => 'Validasi Gagal!',
                                 'message' => 'Terdapat kesalahan dalam pengisian data:',
                                 'errors' => $errorMessages
                             ]
                         ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.karyawan')->with([
                'alert' => [
                    'type' => 'error',
                    'title' => 'Data Tidak Ditemukan!',
                    'message' => 'Data pegawai yang ingin diubah tidak ditemukan.'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate pegawai: ' . $e->getMessage());
            
            // Hapus foto baru jika ada error
            if (isset($validated['foto']) && $validated['foto'] !== 'avatar-1.jpg') {
                $this->hapusFoto($validated['foto']);
            }

            return back()->withInput()->with([
                'alert' => [
                    'type' => 'error',
                    'title' => 'Gagal Mengupdate!',
                    'message' => 'Terjadi kesalahan saat mengupdate data pegawai. Silakan coba lagi.',
                    'technical_error' => config('app.debug') ? $e->getMessage() : null
                ]
            ]);
        }
    }

    /**
     * Hapus data karyawan
     */
   public function destroy($id)
{
    try {
        $user = Auth::user();
        DB::statement("SET @current_user_id = " . $user->id_user);

        // Cari pegawai dengan relasi yang benar
        $pegawai = Pegawai::findOrFail($id);

        DB::beginTransaction();

        // 1. Hapus data kehadiran (pastikan nama relasi benar)
        // Sesuaikan dengan nama relasi yang ada di Model Pegawai
        DB::table('kehadiran')->where('id_pegawai', $id)->delete();

        // 2. Hapus data cuti
        DB::table('cuti')->where('id_pegawai', $id)->delete();


        // 4. Hapus user terkait (jika ada kolom id_pegawai di tabel users)
        $user_terkait = DB::table('user')->where('id_pegawai', $id)->first();
        if ($user_terkait) {
            DB::table('user')->where('id_pegawai', $id)->delete();
        }

        // 5. Hapus foto jika bukan default
        if ($pegawai->foto && $pegawai->foto !== 'avatar-1.jpg') {
            $this->hapusFoto($pegawai->foto);
        }

        // 6. Terakhir, hapus pegawai
        $pegawai->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Data pegawai dan seluruh data terkait berhasil dihapus.');

    } catch (ModelNotFoundException $e) {
        DB::rollback();
        return redirect()->route('admin.karyawan')->with([
            'notifikasi' => 'Data pegawai tidak ditemukan!',
            'type' => 'error'
        ]);
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error saat menghapus pegawai: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return redirect()->route('admin.karyawan')->with([
            'notifikasi' => 'Terjadi kesalahan saat menghapus data pegawai: ' . $e->getMessage(),
            'type' => 'error'
        ]);
    }
}

    /**
     * Reset/Clear semua filter
     */
    public function clearFilters()
    {
        return redirect()->route('admin.karyawan');
    }

    /**
     * Validasi data pegawai
     */
    private function validasiDataPegawai(Request $request, $id = null)
    {
        $emailRule = 'required|email|max:255|unique:pegawai,email';
        if ($id) {
            $emailRule .= ',' . $id . ',id_pegawai';
        }

        return $request->validate([
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'email' => $emailRule,
            'id_jabatan' => 'required|exists:jabatan,id_jabatan',
            'id_departemen' => 'required|exists:departemen,id_departemen',
            'tanggal_masuk' => 'required|date',
            'jatahtahunan' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    }

    /**
     * Upload foto pegawai
     */
    private function uploadFoto($file)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $uploadPath = public_path('uploads/pegawai');
        
        // Pastikan folder exists
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        // Upload file
        $file->move($uploadPath, $fileName);
        
        return $fileName;
    }

    /**
     * Hapus foto pegawai
     */
    private function hapusFoto($fileName)
    {
        $fotoPath = public_path('uploads/pegawai/' . $fileName);
        if (file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }

    /**
     * Get foto path untuk display
     */
    public function getFotoPath($fileName)
    {
        if (!$fileName || $fileName === 'avatar-1.jpg') {
            return asset('assets/images/user/avatar-1.jpg');
        }
        
        return asset('uploads/pegawai/' . $fileName);
    }
}