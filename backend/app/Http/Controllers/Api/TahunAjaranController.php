<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tahunAjaran = TahunAjaran::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Tahun Ajaran',
            'data' => $tahunAjaran
        ]);
    public function index()
    {
        $tahunAjaran = TahunAjaran::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $tahunAjaran
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string',
            'semester' => 'required|string',
            'is_aktif' => 'boolean'
        ]);

        // If setting to active, deactivate others
        if ($request->is_aktif) {
            TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $cekDuplikat = TahunAjaran::where('nama', $request->nama)
            ->where('semester', $request->semester)
            ->exists();

        if ($cekDuplikat) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun ajaran dan semester ini sudah ada.',
                'errors' => ['nama' => ['Tahun ajaran dan semester ini sudah ada.']]
            ], 422);
        }

        $tahunAjaran = TahunAjaran::create([
            'nama' => $request->nama,
            'semester' => $request->semester,
            'is_aktif' => $request->is_aktif ?? false
            'is_aktif' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tahun Ajaran Created',
            'message' => 'Berhasil menambahkan data!',
            'data' => $tahunAjaran
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TahunAjaran $tahunAjaran): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail Data Tahun Ajaran',
            'data' => $tahunAjaran
        ]);
    public function show($id)
    {
        $tahunAjaran = TahunAjaran::find($id);

        if (!$tahunAjaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tahunAjaran
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TahunAjaran $tahunAjaran): JsonResponse
    {
        $request->validate([
            'nama' => 'string',
            'semester' => 'string',
            'is_aktif' => 'boolean'
        ]);

        // If setting to active, deactivate others
        if ($request->has('is_aktif') && $request->is_aktif) {
            TahunAjaran::where('id', '!=', $tahunAjaran->id)
                ->where('is_aktif', true)
                ->update(['is_aktif' => false]);
        }

        $tahunAjaran->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tahun Ajaran Updated',
            'data' => $tahunAjaran
        ]);
    public function update(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::find($id);

        if (!$tahunAjaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek kombinasi nama dan semester, kecuali id saat ini
        $cekDuplikat = TahunAjaran::where('nama', $request->nama)
            ->where('semester', $request->semester)
            ->where('id', '!=', $id)
            ->exists();

        if ($cekDuplikat) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun ajaran dan semester ini sudah ada.',
                'errors' => ['nama' => ['Tahun ajaran dan semester ini sudah ada.']]
            ], 422);
        }

        $tahunAjaran->update([
            'nama' => $request->nama,
            'semester' => $request->semester,
            'is_aktif' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengubah data!',
            'data' => $tahunAjaran
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TahunAjaran $tahunAjaran): JsonResponse
    {
    public function destroy($id)
    {
        $tahunAjaran = TahunAjaran::find($id);

        if (!$tahunAjaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $tahunAjaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tahun Ajaran Deleted'
        ]);
    }
}
            'message' => 'Berhasil menghapus data!'
        ], 200);
    }
}

