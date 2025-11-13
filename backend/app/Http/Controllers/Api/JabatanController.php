<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $jabatan = Jabatan::latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jabatan berhasil diambil',
                'data' => $jabatan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jabatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_jabatan' => 'required|string|max:255',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi',
            'nama_jabatan.string' => 'Nama jabatan harus berupa teks',
            'nama_jabatan.max' => 'Nama jabatan maksimal 255 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jabatan = Jabatan::create([
                'nama_jabatan' => $request->nama_jabatan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data jabatan',
                'data' => $jabatan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data jabatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $jabatan = Jabatan::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data jabatan berhasil diambil',
                'data' => $jabatan
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data jabatan tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jabatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_jabatan' => 'required|string|max:255',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi',
            'nama_jabatan.string' => 'Nama jabatan harus berupa teks',
            'nama_jabatan.max' => 'Nama jabatan maksimal 255 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jabatan = Jabatan::findOrFail($id);
            
            $jabatan->update([
                'nama_jabatan' => $request->nama_jabatan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah data jabatan',
                'data' => $jabatan
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data jabatan tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah data jabatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $jabatan = Jabatan::findOrFail($id);
            
            $jabatan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus data jabatan',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data jabatan tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data jabatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}