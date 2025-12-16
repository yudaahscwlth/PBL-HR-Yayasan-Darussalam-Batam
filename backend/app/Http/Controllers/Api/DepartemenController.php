<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartemenController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $departemen = Departemen::with(['kepala.profilePribadi'])
                ->latest()
                ->get();
            
            $users = User::with('profilePribadi')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data departemen berhasil diambil',
                'data' => [
                    'departemen' => $departemen,
                    'users' => $users
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_departemen' => 'required|string|max:255',
            'id_kepala_departemen' => 'nullable|exists:users,id',
        ], [
            'nama_departemen.required' => 'Nama departemen wajib diisi',
            'id_kepala_departemen.exists' => 'Kepala departemen tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $departemen = Departemen::create([
                'id_kepala_departemen' => $request->id_kepala_departemen,
                'nama_departemen' => $request->nama_departemen,
            ]);

            $departemen->load(['kepala.profilePribadi']);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data departemen',
                'data' => $departemen
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $departemen = Departemen::with(['kepala.profilePribadi'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data departemen berhasil diambil',
                'data' => $departemen
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_departemen' => 'required|string|max:255',
            'id_kepala_departemen' => 'nullable|exists:users,id',
        ], [
            'nama_departemen.required' => 'Nama departemen wajib diisi',
            'id_kepala_departemen.exists' => 'Kepala departemen tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $departemen = Departemen::findOrFail($id);

            $departemen->update([
                'id_kepala_departemen' => $request->id_kepala_departemen,
                'nama_departemen' => $request->nama_departemen,
            ]);

            $departemen->load(['kepala.profilePribadi']);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah data departemen',
                'data' => $departemen
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $departemen = Departemen::findOrFail($id);
            
            $namaDepartemen = $departemen->nama_departemen;
            $departemen->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus data departemen: {$namaDepartemen}",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users for dropdown selection
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {
        try {
            $users = User::with('profilePribadi')
                ->select('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data users berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get members of a specific department
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembers($id)
    {
        try {
            $users = User::whereHas('profilePekerjaan', function($q) use ($id) {
                $q->where('id_departemen', $id);
            })
            ->whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['superadmin', 'kepala yayasan']);
            })
            ->with(['profilePribadi', 'profilePekerjaan', 'profilePekerjaan.jabatan'])
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data anggota departemen berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data anggota',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}