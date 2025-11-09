<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SosialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SosialMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sosialMedia = SosialMedia::orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $sosialMedia
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_platform' => 'required|string|max:255',
        ], [
            'nama_platform.required' => 'Nama platform wajib diisi.',
            'nama_platform.string'   => 'Nama platform harus berupa teks.',
            'nama_platform.max'      => 'Nama platform tidak boleh lebih dari 255 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $sosialMedia = SosialMedia::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan data!',
            'data' => $sosialMedia
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sosialMedia = SosialMedia::find($id);

        if (!$sosialMedia) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sosialMedia
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sosialMedia = SosialMedia::find($id);

        if (!$sosialMedia) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_platform' => 'required|string|max:255',
        ], [
            'nama_platform.required' => 'Nama platform wajib diisi.',
            'nama_platform.string'   => 'Nama platform harus berupa teks.',
            'nama_platform.max'      => 'Nama platform tidak boleh lebih dari 255 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $sosialMedia->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengubah data!',
            'data' => $sosialMedia
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sosialMedia = SosialMedia::find($id);

        if (!$sosialMedia) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $sosialMedia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus data!'
        ], 200);
    }
}

