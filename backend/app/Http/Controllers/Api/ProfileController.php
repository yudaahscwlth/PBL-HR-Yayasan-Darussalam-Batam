<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilePribadi;
use App\Models\ProfilePekerjaan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            'email' => 'required|unique:users,email,' . $user->email . ',email|email:dns',
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

            // Validasi untuk keluarga (array)
            'id_keluarga.*' => 'nullable|exists:keluargas,id',
            'nama.*' => 'required|string',
            'hubungan.*' => 'required|string',
            'tanggal_lahir_keluarga.*' => 'required|date',
            'pekerjaan.*' => 'required|string',

            // validasi user sosmed
            'id_user_sosmed.*' => 'nullable|exists:user_sosial_media,id',
            'id_platform.*' => 'required|exists:sosial_media,id',
            'username.*' => 'required|string|max:255',
            'link.*' => 'required|url|max:255',
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
                if (!empty($old_foto) && is_file('storage/'.$old_foto)) {
                    unlink('storage/'.$old_foto);
                }
                
                $foto = $request->file('foto')->store('profile_img','public');
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

            for ($i = 0; $i < count($nama); $i++) {
                $id = $id_keluarga[$i] ?? null;

                $user->keluarga()->updateOrCreate(
                    ['id' => $id],
                    [
                        'id_user' => $user->id,
                        'nama' => $nama[$i],
                        'hubungan' => $hubungan[$i],
                        'tanggal_lahir' => $tanggal_lahir_keluarga[$i],
                        'pekerjaan' => $pekerjaan[$i],
                    ]
                );
            }

            //update or create user sosial media
            $id_user_sosmed = $request->input('id_user_sosmed', []);
            $id_platform = $request->input('id_platform', []);
            $username = $request->input('username', []);
            $link = $request->input('link', []);

            for ($i = 0; $i < count($id_platform); $i++) {
                $id = $id_user_sosmed[$i] ?? null;

                $user->userSosialMedia()->updateOrCreate(
                    ['id' => $id],
                    [
                        'id_user' => $user->id,
                        'id_platform' => $id_platform[$i],
                        'username' => $username[$i],
                        'link' => $link[$i],
                    ]
                );
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
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
}
