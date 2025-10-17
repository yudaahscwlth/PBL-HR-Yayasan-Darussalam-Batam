<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index(){
        $tahunAjaran = TahunAjaran::latest()->get();

        return view('admin.kelola-tahun-ajaran',[
            'dataTahunAjaran' => $tahunAjaran,
        ]);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'nama' => 'required|regex:/^[0-9]{4}\/[0-9]{4}$/',
            'semester' => 'required|in:ganjil,genap',
            'status' => 'required|in:0,1',
        ], [
            'nama.required' => 'Tahun ajaran wajib diisi.',
            'nama.regex' => 'Format tahun ajaran harus seperti 2025/2026.',

            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester harus bernilai ganjil atau genap.',

            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus bernilai 0 (Tidak Aktif) atau 1 (Aktif).',
        ]);

        $cekDuplikat = TahunAjaran::where('nama', $request->nama)
            ->where('semester', $request->semester)
            ->exists();

        if ($cekDuplikat) {
            return back()->withErrors(['nama' => 'Tahun ajaran dan semester ini sudah ada.'])->withInput();
        }

        $save = TahunAjaran::create([
            'nama' => $request->nama,
            'semester' => $request->semester,
            'is_aktif' => $request->status,
        ]);

        if ($save) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menambahkan data!',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menambahkan data!',
                'type' => 'error',
            ]);
        }
    }

    public function update(Request $request,$id_tahun_ajaran)
    {
        $validatedData = $request->validate([
            'nama' => 'required|regex:/^[0-9]{4}\/[0-9]{4}$/',
            'semester' => 'required|in:ganjil,genap',
            'status' => 'required|in:0,1',
        ], [
            'nama.required' => 'Tahun ajaran wajib diisi.',
            'nama.regex' => 'Format tahun ajaran harus seperti 2025/2026.',


            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester harus bernilai ganjil atau genap.',

            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus bernilai 0 (Tidak Aktif) atau 1 (Aktif).',
        ]);

        // Cek kombinasi nama dan semester, kecuali id saat ini
        $cekDuplikat = TahunAjaran::where('nama', $request->nama)
            ->where('semester', $request->semester)
            ->whereNot('id',$id_tahun_ajaran)
            ->exists();

        if ($cekDuplikat) {
            return back()->withErrors(['nama' => 'Tahun ajaran dan semester ini sudah ada.'])->withInput();
        }

        $TahunAjaran = TahunAjaran::findOrFail($id_tahun_ajaran);

        $save = $TahunAjaran->update([
            'nama' => $request->nama,
            'semester' => $request->semester,
            'is_aktif' => $request->status,
        ]);

        if ($save) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengubah data!',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengubah data!',
                'type' => 'error',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_tahun_ajaran)
    {
        $TahunAjaran = TahunAjaran::findOrFail($id_tahun_ajaran);

        if (!$TahunAjaran) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($TahunAjaran->delete()) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menghapus data!',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menghapus data!',
                'type' => 'error',
            ]);
        }
    }
}
