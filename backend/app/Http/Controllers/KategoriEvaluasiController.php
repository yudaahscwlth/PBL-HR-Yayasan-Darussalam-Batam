<?php

namespace App\Http\Controllers;

use App\Models\KategoriEvaluasi;
use Illuminate\Http\Request;

class KategoriEvaluasiController extends Controller
{
    public function index()
    {
        $kategoriEvaluasi = KategoriEvaluasi::orderBy('nama','asc')->get();

        return view('admin.kelola-kategori-evaluasi',[
            'dataKategori' => $kategoriEvaluasi,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required',
        ]);

        $save = KategoriEvaluasi::create([
            'nama' => $request->nama
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

    public function update(Request $request,$id_kategori)
    {
        $validatedData = $request->validate([
            'nama' => 'required',
        ]);

        $kategoriEvaluasi = KategoriEvaluasi::findOrFail($id_kategori);

        $save = $kategoriEvaluasi->update([
            'nama' => $request->nama
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
    public function destroy($id_kategori)
    {
        $kategoriEvaluasi = KategoriEvaluasi::findOrFail($id_kategori);

        if (!$kategoriEvaluasi) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($kategoriEvaluasi->delete()) {
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
