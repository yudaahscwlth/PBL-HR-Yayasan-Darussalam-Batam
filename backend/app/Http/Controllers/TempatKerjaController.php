<?php

namespace App\Http\Controllers;

use App\Models\TempatKerja;
use Illuminate\Http\Request;

class TempatKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tempatKerja = TempatKerja::latest()->get();

        return view('admin.kelola-tempat-kerja',[
            'dataTempatKerja' => $tempatKerja
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_tempat' => 'required|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
        ], [
            'nama_tempat.required' => 'Nama tempat wajib diisi.',
            'nama_tempat.string'   => 'Nama tempat harus berupa teks.',
            'nama_tempat.max'      => 'Nama tempat tidak boleh lebih dari 255 karakter.',

            'latitude.required'    => 'Latitude wajib diisi.',
            'latitude.numeric'     => 'Latitude harus berupa angka.',
            'latitude.between'     => 'Latitude harus di antara -90 dan 90.',

            'longitude.required'   => 'Longitude wajib diisi.',
            'longitude.numeric'    => 'Longitude harus berupa angka.',
            'longitude.between'    => 'Longitude harus di antara -180 dan 180.',
        ]);

        $save = TempatKerja::create($validatedData);

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

    /**
     * Display the specified resource.
     */
    public function show($id_tempat_kerja)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_tempat_kerja)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_tempat_kerja)
    {
        $validated = $request->validate([
            'nama_tempat' => 'required|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
        ], [
            'nama_tempat.required' => 'Nama tempat wajib diisi.',
            'nama_tempat.string'   => 'Nama tempat harus berupa teks.',
            'nama_tempat.max'      => 'Nama tempat tidak boleh lebih dari 255 karakter.',

            'latitude.required'    => 'Latitude wajib diisi.',
            'latitude.numeric'     => 'Latitude harus berupa angka.',
            'latitude.between'     => 'Latitude harus di antara -90 dan 90.',

            'longitude.required'   => 'Longitude wajib diisi.',
            'longitude.numeric'    => 'Longitude harus berupa angka.',
            'longitude.between'    => 'Longitude harus di antara -180 dan 180.',
        ]);

        $tempatKerja = TempatKerja::findOrFail($id_tempat_kerja);

        $save = $tempatKerja->update($validated);

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
    public function destroy($id_tempat_kerja)
    {
        $tempatKerja = TempatKerja::findOrFail($id_tempat_kerja);

        if (!$tempatKerja) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($tempatKerja->delete()) {
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
