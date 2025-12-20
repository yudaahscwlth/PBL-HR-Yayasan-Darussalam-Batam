<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilePribadi;
use App\Models\ProfilePekerjaan;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Get complete profile data
     */
    public function getCompleteProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load all relationships
        $user->load([
            'profilePribadi',
            'profilePekerjaan.jabatan',
            'profilePekerjaan.departemen',
            'profilePekerjaan.tempatKerja',
            'orangTua',
            'keluarga',
            'userSosialMedia.sosialMedia'
        ]);

        // Calculate lama pengabdian
        $lamaPengabdian = null;
        if ($user->profilePekerjaan && $user->profilePekerjaan->tanggal_masuk) {
            $lamaPengabdian = \Carbon\Carbon::parse($user->profilePekerjaan->tanggal_masuk)->diffForHumans(null, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Complete profile retrieved successfully',
            'data' => [
                'user' => $user,
                'lama_pengabdian' => $lamaPengabdian
            ]
        ]);
    }

    /**
     * Get social media platforms
     */
    public function getSosialMediaPlatforms(Request $request): JsonResponse
    {
        $platforms = \App\Models\SosialMedia::all();

        return response()->json([
            'success' => true,
            'message' => 'Social media platforms retrieved successfully',
            'data' => $platforms
        ]);
    }

    /**
     * Update complete profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'email' => 'required|unique:users,email,' . $user->id . ',id|email:dns',
            'nomor_induk_kependudukan' => 'required',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:pria,wanita',
            'golongan_darah' => 'nullable',
            'status_pernikahan' => 'nullable',
            'npwp' => 'nullable',
            'kecamatan' => 'nullable',
            'alamat_lengkap' => 'nullable|string',
            'no_hp' => 'nullable|regex:/^[0-9]+$/|min:10|max:15',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Validasi untuk orang tua
            'nama_ayah' => 'nullable|string',
            'pekerjaan_ayah' => 'nullable|string',
            'nama_ibu' => 'nullable|string',
            'pekerjaan_ibu' => 'nullable|string',
            'alamat_orang_tua' => 'nullable|string',

            // Validasi untuk keluarga (array) - hanya validasi jika ada data
            'id_keluarga' => 'nullable|array',
            'id_keluarga.*' => 'nullable|exists:keluargas,id',
            'nama' => 'nullable|array',
            'nama.*' => 'nullable|string',
            'hubungan' => 'nullable|array',
            'hubungan.*' => 'nullable|string',
            'tanggal_lahir_keluarga' => 'nullable|array',
            'tanggal_lahir_keluarga.*' => 'nullable|date',
            'pekerjaan' => 'nullable|array',
            'pekerjaan.*' => 'nullable|string',

            // validasi user sosmed - hanya validasi jika ada data
            'id_user_sosmed' => 'nullable|array',
            'id_user_sosmed.*' => 'nullable|exists:user_sosial_media,id',
            'id_platform' => 'nullable|array',
            'id_platform.*' => 'nullable|exists:sosial_media,id',
            'username' => 'nullable|array',
            'username.*' => 'nullable|string|max:255',
            'link' => 'nullable|array',
            'link.*' => 'nullable|url|max:255',
        ], [
            // Pesan error kustom
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid, pastikan formatnya benar.',
            'email.unique' => 'Email yang Anda masukkan sudah terdaftar. Coba gunakan email lain.',
            'nomor_induk_kependudukan.required' => 'Nomor Induk Kependudukan wajib diisi.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks.',
            'nama_lengkap.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'jenis_kelamin.in' => 'Jenis kelamin harus berupa salah satu dari: pria, wanita.',
            'no_hp.regex' => 'Nomor HP hanya boleh terdiri dari angka.',
            'no_hp.min' => 'Nomor HP harus terdiri dari minimal 10 digit.',
            'no_hp.max' => 'Nomor HP tidak boleh lebih dari 15 digit.',
            'nama.*.required' => 'Nama anggota keluarga wajib diisi.',
            'hubungan.*.required' => 'Hubungan keluarga wajib diisi.',
            'tanggal_lahir_keluarga.*.required' => 'Tanggal lahir keluarga wajib diisi.',
            'pekerjaan.*.required' => 'Pekerjaan keluarga wajib diisi.',
            'id_platform.*.required' => 'Platform sosial media wajib dipilih.',
            'id_platform.exists' => 'Platform yang dipilih tidak valid.',
            'username.*.required' => 'Username sosial media wajib diisi.',
            'username.max' => 'Username sosial media terlalu panjang.',
            'link.*.required' => 'Link sosial media wajib diisi.',
            'link.url' => 'Link sosial media harus berupa URL yang valid.',
            'link.max' => 'Link sosial media terlalu panjang.',
        ]);

        try {
            DB::beginTransaction();

            $user->email = $request->email;
            $user->save();

            if ($request->hasFile('foto')) {
                $old_foto = $user->profilePribadi->foto ?? null;
                if (!empty($old_foto) && is_file('storage/' . $old_foto)) {
                    unlink('storage/' . $old_foto);
                }

                $foto = $request->file('foto')->store('profile_img', 'public');
            } else {
                $foto = $user->profilePribadi->foto ?? null;
            }

            // Update Profile
            $profileData = $request->only([
                'nomor_induk_kependudukan',
                'nama_lengkap',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'golongan_darah',
                'status_pernikahan',
                'npwp',
                'kecamatan',
                'alamat_lengkap',
                'no_hp',
                'nomor_rekening',
            ]);

            if ($foto) {
                $profileData['foto'] = $foto;
            }

            $user->profilePribadi()->updateOrCreate(['id_user' => $user->id], $profileData);

            // Update OrangTua
            $user->orangTua()->updateOrCreate(
                ['id_user' => $user->id],
                $request->only([
                    'nama_ayah',
                    'pekerjaan_ayah',
                    'nama_ibu',
                    'pekerjaan_ibu',
                    'alamat_orang_tua',
                ])
            );

            // Update or Create Keluarga (bisa hapus, update, atau tambah)
            $id_keluarga = $request->input('id_keluarga', []);
            $nama = $request->input('nama', []);
            $hubungan = $request->input('hubungan', []);
            $tanggal_lahir_keluarga = $request->input('tanggal_lahir_keluarga', []);
            $pekerjaan = $request->input('pekerjaan', []);

            // Get all existing keluarga IDs for this user
            $existingKeluargaIds = $user->keluarga()->pluck('id')->toArray();
            $updatedKeluargaIds = [];

            // Pastikan semua array memiliki panjang yang sama
            $maxCount = max(
                count($id_keluarga),
                count($nama),
                count($hubungan),
                count($tanggal_lahir_keluarga),
                count($pekerjaan)
            );

            for ($i = 0; $i < $maxCount; $i++) {
                // Skip jika data tidak lengkap
                if (empty($nama[$i]) || empty($hubungan[$i]) || empty($tanggal_lahir_keluarga[$i]) || empty($pekerjaan[$i])) {
                    continue;
                }

                $id = $id_keluarga[$i] ?? null;
                // Convert empty string to null for new records
                $id = ($id === '' || $id === null) ? null : $id;

                $keluarga = $user->keluarga()->updateOrCreate(
                    ['id' => $id],
                    [
                        'id_user' => $user->id,
                        'nama' => $nama[$i],
                        'hubungan' => $hubungan[$i],
                        'tanggal_lahir' => $tanggal_lahir_keluarga[$i],
                        'pekerjaan' => $pekerjaan[$i],
                    ]
                );

                // Track updated IDs
                if ($keluarga->id) {
                    $updatedKeluargaIds[] = $keluarga->id;
                }
            }

            // Delete keluarga that were removed (exist in DB but not in request)
            $idsToDeleteKeluarga = array_diff($existingKeluargaIds, $updatedKeluargaIds);
            if (!empty($idsToDeleteKeluarga)) {
                $user->keluarga()->whereIn('id', $idsToDeleteKeluarga)->delete();
            }

            //update or create user sosial media
            $id_user_sosmed = $request->input('id_user_sosmed', []);
            $id_platform = $request->input('id_platform', []);
            $username = $request->input('username', []);
            $link = $request->input('link', []);

            // Get all existing user_sosial_media IDs for this user
            $existingSosmedIds = $user->userSosialMedia()->pluck('id')->toArray();
            $updatedSosmedIds = [];

            // Pastikan semua array memiliki panjang yang sama
            $maxCount = max(
                count($id_user_sosmed),
                count($id_platform),
                count($username),
                count($link)
            );

            for ($i = 0; $i < $maxCount; $i++) {
                // Skip jika data tidak lengkap
                if (empty($id_platform[$i]) || empty($username[$i]) || empty($link[$i])) {
                    continue;
                }

                $id = $id_user_sosmed[$i] ?? null;
                // Convert empty string to null for new records
                $id = ($id === '' || $id === null) ? null : $id;

                $sosmed = $user->userSosialMedia()->updateOrCreate(
                    ['id' => $id],
                    [
                        'id_user' => $user->id,
                        'id_platform' => $id_platform[$i],
                        'username' => $username[$i],
                        'link' => $link[$i],
                    ]
                );

                // Track updated IDs
                if ($sosmed->id) {
                    $updatedSosmedIds[] = $sosmed->id;
                }
            }

            // Delete user_sosial_media that were removed (exist in DB but not in request)
            $idsToDeleteSosmed = array_diff($existingSosmedIds, $updatedSosmedIds);
            if (!empty($idsToDeleteSosmed)) {
                $user->userSosialMedia()->whereIn('id', $idsToDeleteSosmed)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()->load([
                    'profilePribadi',
                    'profilePekerjaan.jabatan',
                    'profilePekerjaan.departemen',
                    'profilePekerjaan.tempatKerja',
                    'orangTua',
                    'keluarga',
                    'userSosialMedia.sosialMedia'
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Profile update error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

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

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|different:password_lama',
            'konf_password' => 'required|same:password_baru',
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.different' => 'Password baru harus berbeda dengan password lama.',
            'konf_password.required' => 'Konfirmasi password wajib diisi.',
            'konf_password.same' => 'Konfirmasi password tidak cocok dengan password baru.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->password = Hash::make($request->password_baru);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
