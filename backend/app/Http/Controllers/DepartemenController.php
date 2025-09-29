<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\User;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Departemen = Departemen::latest()->get();
        $user = User::all();

        return view('admin.kelola-departemen',[
            'dataDepartemen' => $Departemen,
            'dataUser' => $user,
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
            'nama_departemen' => 'required',
            'id_kepala_departemen' => 'nullable',
        ]);

        $save = Departemen::create([
            'id_kepala_departemen' => $request->id_kepala_departemen,
            'nama_departemen' => $request->nama_departemen,
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

    /**
     * Display the specified resource.
     */
    public function show(Departemen $Departemen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departemen $Departemen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id_Departemen)
    {
        $validatedData = $request->validate([
            'nama_departemen' => 'required',
            'id_kepala_departemen' => 'nullable',
        ]);

        $Departemen = Departemen::findOrFail($id_Departemen);

        $save = $Departemen->update([
            'id_kepala_departemen' => $request->id_kepala_departemen,
            'nama_departemen' => $request->nama_departemen,
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
    public function destroy($id_Departemen)
    {
        $Departemen = Departemen::findOrFail($id_Departemen);

        if (!$Departemen) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($Departemen->delete()) {
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
