<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TempatKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TempatKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tempatKerja = TempatKerja::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $tempatKerja
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $tempatKerja = TempatKerja::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan data!',
            'data' => $tempatKerja
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tempatKerja = TempatKerja::find($id);

        if (!$tempatKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tempatKerja
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tempatKerja = TempatKerja::find($id);

        if (!$tempatKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $tempatKerja->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengubah data!',
            'data' => $tempatKerja
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tempatKerja = TempatKerja::find($id);

        if (!$tempatKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $tempatKerja->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus data!'
        ], 200);
    }
}
