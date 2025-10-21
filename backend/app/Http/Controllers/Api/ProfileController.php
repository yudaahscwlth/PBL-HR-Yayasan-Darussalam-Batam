<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilePribadi;
use App\Models\ProfilePekerjaan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Get personal profile
     */
    public function getPersonal(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = ProfilePribadi::where('id_user', $user->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Personal profile retrieved successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Update personal profile
     */
    public function updatePersonal(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:255',
            'status_perkawinan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'no_handphone' => 'nullable|string|max:20',
        ]);

        $profile = ProfilePribadi::updateOrCreate(
            ['id_user' => $user->id],
            $request->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Personal profile updated successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Get work profile
     */
    public function getWork(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = ProfilePekerjaan::with(['jabatan', 'departemen', 'tempatKerja'])
            ->where('id_user', $user->id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Work profile retrieved successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Update work profile
     */
    public function updateWork(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $request->validate([
            'nip' => 'nullable|string|max:50',
            'id_jabatan' => 'nullable|exists:jabatan,id',
            'id_departemen' => 'nullable|exists:departemen,id',
            'id_tempat_kerja' => 'nullable|exists:tempat_kerja,id',
            'tanggal_masuk' => 'nullable|date',
            'status_pegawai' => 'nullable|string|max:255',
        ]);

        $profile = ProfilePekerjaan::updateOrCreate(
            ['id_user' => $user->id],
            $request->all()
        );

        $profile->load(['jabatan', 'departemen', 'tempatKerja']);

        return response()->json([
            'success' => true,
            'message' => 'Work profile updated successfully',
            'data' => $profile,
        ]);
    }
}

