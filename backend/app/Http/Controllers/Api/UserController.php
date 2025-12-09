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
            // Profile Pribadi
            'nama_lengkap' => 'sometimes|string',
            'nik' => 'sometimes|string',
            'npwp' => 'nullable|string',
            'nomor_rekening' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string',
            'status_pernikahan' => 'nullable|string',
            'golongan_darah' => 'nullable|string',
            'kecamatan' => 'nullable|string',
            'alamat_lengkap' => 'nullable|string',
            'no_hp' => 'nullable|string',
            // Profile Pekerjaan
            'nik_karyawan' => 'sometimes|string',
            'tanggal_masuk' => 'sometimes|date',
            'id_jabatan' => 'sometimes|exists:jabatans,id',
            'id_departemen' => 'sometimes|exists:departemens,id',
            'id_tempat_kerja' => 'sometimes|exists:tempat_kerjas,id',
            'status' => 'sometimes|string',
            'role' => 'sometimes|string|exists:roles,name',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Update User
            $userData = [];
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('password')) $userData['password'] = bcrypt($request->password);
            if (!empty($userData)) $user->update($userData);

            // Update Role
            if ($request->has('role')) {
                $user->syncRoles([$request->role]);
            }

            // Update Profile Pribadi
            $profilePribadiData = [];
            $pribadiFields = ['nama_lengkap', 'nik', 'npwp', 'nomor_rekening', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_pernikahan', 'golongan_darah', 'kecamatan', 'alamat_lengkap', 'no_hp'];
            foreach ($pribadiFields as $field) {
                if ($request->has($field)) {
                    // Map 'nik' to 'nomor_induk_kependudukan'
                    $dbField = $field === 'nik' ? 'nomor_induk_kependudukan' : $field;
                    $profilePribadiData[$dbField] = $request->$field;
                }
            }
            
            if (!empty($profilePribadiData)) {
                $user->profilePribadi()->updateOrCreate(['id_user' => $user->id], $profilePribadiData);
            }

            // Update Profile Pekerjaan
            $profilePekerjaanData = [];
            $pekerjaanFields = ['nik_karyawan', 'tanggal_masuk', 'id_jabatan', 'id_departemen', 'id_tempat_kerja', 'status'];
            foreach ($pekerjaanFields as $field) {
                if ($request->has($field)) {
                    // Map 'nik_karyawan' to 'nomor_induk_karyawan'
                    $dbField = $field === 'nik_karyawan' ? 'nomor_induk_karyawan' : $field;
                    $profilePekerjaanData[$dbField] = $request->$field;
                }
            }

            if (!empty($profilePekerjaanData)) {
                $user->profilePekerjaan()->updateOrCreate(['id_user' => $user->id], $profilePekerjaanData);
            }

            // Update Orang Tua
            $orangTuaData = [];
            $orangTuaFields = ['nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu', 'alamat_orang_tua'];
            foreach ($orangTuaFields as $field) {
                if ($request->has($field)) {
                    $orangTuaData[$field] = $request->$field;
                }
            }
            if (!empty($orangTuaData)) {
                $user->orangTua()->updateOrCreate(['id_user' => $user->id], $orangTuaData);
            }

            // Update Keluarga
            if ($request->has('keluarga')) {
                // Expecting 'keluarga' to be an array of objects
                $keluargaData = $request->keluarga;
                if (is_array($keluargaData)) {
                    // Get existing IDs to determine what to delete
                    $existingIds = $user->keluarga()->pluck('id')->toArray();
                    $updatedIds = [];

                    foreach ($keluargaData as $item) {
                        $dataToSave = \Illuminate\Support\Arr::except($item, ['id']);
                        if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                            // Update existing
                            $user->keluarga()->where('id', $item['id'])->update($dataToSave);
                            $updatedIds[] = $item['id'];
                        } else {
                            // Create new
                            $user->keluarga()->create($dataToSave);
                        }
                    }

                    // Delete removed items
                    $idsToDelete = array_diff($existingIds, $updatedIds);
                    if (!empty($idsToDelete)) {
                        $user->keluarga()->whereIn('id', $idsToDelete)->delete();
                    }
                }
            }

            // Update User Sosial Media
            if ($request->has('user_sosial_media')) {
                $sosmedData = $request->user_sosial_media;
                if (is_array($sosmedData)) {
                    $existingIds = $user->userSosialMedia()->pluck('id')->toArray();
                    $updatedIds = [];

                    foreach ($sosmedData as $item) {
                        $dataToSave = \Illuminate\Support\Arr::except($item, ['id']);
                        if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                            $user->userSosialMedia()->where('id', $item['id'])->update($dataToSave);
                            $updatedIds[] = $item['id'];
                        } else {
                            $user->userSosialMedia()->create($dataToSave);
                        }
                    }

                    $idsToDelete = array_diff($existingIds, $updatedIds);
                    if (!empty($idsToDelete)) {
                        $user->userSosialMedia()->whereIn('id', $idsToDelete)->delete();
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $user->load(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja', 'orangTua', 'keluarga', 'userSosialMedia.sosialMedia']);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => new UserResource($user),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::build([
              'driver' => 'single',
              'path' => storage_path('logs/debug_user_update.log'),
            ])->error('Update failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
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

