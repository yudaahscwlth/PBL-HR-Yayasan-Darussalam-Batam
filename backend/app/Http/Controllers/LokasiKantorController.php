<?php

namespace App\Http\Controllers;

use App\Models\LokasiKantor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LokasiKantorController extends Controller
{
    public function index()
    {
        $lokasiKantor = LokasiKantor::all();
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        return view('admin.LokasiKantor.index', compact('lokasiKantor','pegawai' ,'nama_departemen'));
    }

    public function create()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        return view('admin.LokasiKantor.tambah', compact('pegawai', 'nama_departemen'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        DB::statement("SET @current_user_id = " . $user->id_user);
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:1000'
        ]);

        LokasiKantor::create($request->all());

        return redirect()->route('admin.LokasiKantor.index')
                        ->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    public function show(LokasiKantor $lokasiKantor)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        return view('admin.LokasiKantor.show', compact('lokasiKantor', 'pegawai', 'nama_departemen'));
    }

    public function edit(LokasiKantor $lokasiKantor)
    {
        // Definisikan pegawai, departemen, jabatan seperti di index
        $user = Auth::user();
        
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen;
        
        return view('admin.LokasiKantor.edit', compact('lokasiKantor', 'pegawai', 'nama_departemen'));
    }

    public function update(Request $request, LokasiKantor $lokasiKantor)
    {
        $user = Auth::user();
         DB::statement("SET @current_user_id = " . $user->id_user);
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:1000'
        ]);

        $lokasiKantor->update($request->all());

        return redirect()->route('admin.LokasiKantor.index')
                        ->with('success', 'Lokasi kantor berhasil diperbarui.');
    }

    public function destroy(LokasiKantor $lokasiKantor)
    {
        $lokasiKantor->delete();

        return redirect()->route('admin.LokasiKantor.index')
                        ->with('success', 'Lokasi kantor berhasil dihapus.');
    }
}