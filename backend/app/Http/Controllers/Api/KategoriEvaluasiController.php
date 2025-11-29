<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriEvaluasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriEvaluasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        if (!$this->userCanManage($user)) {
            $userRoles = $user->roles->pluck('name')->toArray();
            \Log::warning('Unauthorized access attempt to kategori evaluasi', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_roles' => $userRoles,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to manage kategori evaluasi. Required roles: kepala hrd, staff hrd, or superadmin',
                'user_roles' => $userRoles,
            ], 403);
        }

        $kategoriEvaluasi = KategoriEvaluasi::orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Kategori evaluasi retrieved successfully',
            'data' => $kategoriEvaluasi,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_evaluasis,nama',
        ]);

        $kategori = KategoriEvaluasi::create(['nama' => $validated['nama']]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori evaluasi created successfully',
            'data' => $kategori,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriEvaluasi $kategoriEvaluasi): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_evaluasis,nama,' . $kategoriEvaluasi->id,
        ]);

        $kategoriEvaluasi->update(['nama' => $validated['nama']]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori evaluasi updated successfully',
            'data' => $kategoriEvaluasi,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, KategoriEvaluasi $kategoriEvaluasi): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $kategoriEvaluasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori evaluasi deleted successfully',
        ]);
    }

    private function userCanManage($user): bool
    {
        if (!$user) {
            return false;
        }

        // Ensure roles are loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        return $user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin']);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to manage kategori evaluasi',
        ], 403);
    }
}

