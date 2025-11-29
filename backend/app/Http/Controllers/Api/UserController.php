<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(): JsonResponse
    {
        $users = User::with(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja'])->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Store a newly created user
     */
    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'nama_lengkap' => 'required|string',
            'nik' => 'required|string',
            'nik_karyawan' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'id_jabatan' => 'required|exists:jabatans,id',
            'id_departemen' => 'required|exists:departemens,id',
            'id_tempat_kerja' => 'required|exists:tempat_kerjas,id',
            'status' => 'required|string',
            'role' => 'required|string|exists:roles,name',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $user->assignRole($request->role);

            \App\Models\ProfilePribadi::create([
                'id_user' => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'nomor_induk_kependudukan' => $request->nik,
            ]);

            \App\Models\ProfilePekerjaan::create([
                'id_user' => $user->id,
                'nomor_induk_karyawan' => $request->nik_karyawan,
                'tanggal_masuk' => $request->tanggal_masuk,
                'id_jabatan' => $request->id_jabatan,
                'id_departemen' => $request->id_departemen,
                'id_tempat_kerja' => $request->id_tempat_kerja,
                'status' => $request->status,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            $user->load(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja']);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => new UserResource($user),
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja']);
        
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
        ]);

        $updateData = [];
        
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }
        
        if ($request->has('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);
        $user->load(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }
}

