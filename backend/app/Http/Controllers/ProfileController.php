<?php

namespace App\Http\Controllers;

use App\Models\SosialMedia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function showProfilePage(){
        $user = Auth::user();
        $sosialMedia = SosialMedia::all();

        $lamaPengabdian = Carbon::parse($user->profilePekerjaan->tanggal_masuk)->diffForHumans(null, true);

        $jsonKota = File::get(resource_path('json/kota-indonesia.json'));
        $dataKotaJson = json_decode($jsonKota, true);

        $jsonKecamatan = File::get(resource_path('json/kecamatan-indonesia.json'));
        $dataKecamatanJson = json_decode($jsonKecamatan, true);

        return view('general.profile',[
            'data'=>$user,
            'dataSosialMedia' => $sosialMedia,
            'lamaPengabdian' => $lamaPengabdian,
            'allKota' => $dataKotaJson,
            'allKecamatan' => $dataKecamatanJson,
        ]);
    }

    public function update(Request $request){
        $validatedData= $request->validate([
            //validasi user
            'email' => 'required|unique:users,email,'. Auth::user()->email .',email|email:dns' ,

            // Validasi untuk profile
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
            'id_platform.*' => 'required|exists:sosial_media,id',
            'username.*' => 'required|string|max:255',
            'link.*' => 'required|url|max:255',
        ], [
            // Pesan error kustom

            // Email
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid, pastikan formatnya benar.',
            'email.unique' => 'Email yang Anda masukkan sudah terdaftar. Coba gunakan email lain.',

            // No HP
            'no_hp.regex' => 'Nomor HP hanya boleh terdiri dari angka.',
            'no_hp.min' => 'Nomor HP harus terdiri dari minimal 10 digit.',
            'no_hp.max' => 'Nomor HP tidak boleh lebih dari 15 digit.',

            // Profile - Nama lengkap dan nomor induk kependudukan
            'nomor_induk_kependudukan.required' => 'Nomor Induk Kependudukan wajib diisi.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string' => 'Nama lengkap harus berupa huruf.',
            'nama_lengkap.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',

            // Profile - Tempat lahir, Tanggal lahir, Jenis kelamin
            'tempat_lahir.string' => 'Tempat lahir harus berupa teks.',
            'tanggal_lahir.date' => 'Tanggal lahir harus dalam format yang valid.',
            'jenis_kelamin.in' => 'Jenis kelamin harus berupa salah satu dari: pria, wanita.',

            // Orang Tua
            'nama_ayah.string' => 'Nama ayah harus berupa teks.',
            'pekerjaan_ayah.string' => 'Pekerjaan ayah harus berupa teks.',
            'nama_ibu.string' => 'Nama ibu harus berupa teks.',
            'pekerjaan_ibu.string' => 'Pekerjaan ibu harus berupa teks.',
            'alamat_orang_tua.string' => 'Alamat orang tua harus berupa teks.',

            // Keluarga
            'id_keluarga.*.exists' => 'ID keluarga tidak valid.',
            'nama.*.required' => 'Nama anggota keluarga wajib diisi.',
            'nama.*.string' => 'Nama anggota keluarga harus berupa teks.',
            'hubungan.*.required' => 'Hubungan keluarga wajib diisi untuk setiap anggota.',
            'hubungan.*.string' => 'Hubungan keluarga harus berupa teks.',
            'tanggal_lahir_keluarga.*.required' => 'Tanggal lahir keluarga wajib diisi untuk setiap anggota.',
            'tanggal_lahir_keluarga.*.date' => 'Tanggal lahir keluarga harus dalam format yang valid.',
            'pekerjaan.*.required' => 'Pekerjaan wajib diisi untuk setiap anggota.',
            'pekerjaan.*.string' => 'Pekerjaan keluarga harus berupa teks.',

            //sosmed
            'id_platform.*.required' => 'Platform wajib dipilih.',
            'id_platform.*.exists' => 'Platform yang dipilih tidak valid.',
            'username.*.required' => 'Username wajib diisi.',
            'username.*.max' => 'Username terlalu panjang.',
            'link.*.required' => 'Link sosial media wajib diisi.',
            'link.*.url' => 'Link sosial media harus berupa URL yang valid.',
            'link.*.max' => 'Link sosial media terlalu panjang.',
        ]);

        try{
            DB::beginTransaction();

            $user = User::where('id',Auth::user()->id)->firstOrFail();
            $user->email = $request->email;
            $user->save();

            if ($request->hasFile('foto')) {
                $old_foto = $user->profilePribadi->foto ?? null;
                if (!empty($old_foto) && is_file('storage/'.$old_foto)) {
                    unlink('storage/'.$old_foto);
                }
                // Store the photo in the public/profile_img directory
                $foto = $request->file('foto')->store('profile_img','public');

            }else{
                $foto = $user->profilePribadi->foto;
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
            $tanggal_lahir = $request->input('tanggal_lahir_keluarga', []);
            $pekerjaan = $request->input('pekerjaan', []);

            for ($i = 0; $i < count($nama); $i++) {
                $id = $id_keluarga[$i] ?? null;

                $user->keluarga()->updateOrCreate(
                    ['id' => $id],
                    [
                        'id_user' => $user->id,
                        'nama' => $nama[$i],
                        'hubungan' => $hubungan[$i],
                        'tanggal_lahir' => $tanggal_lahir[$i],
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

            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengubah data',
                'type' => 'success',
            ]);
        }catch (\Exception $e) {

            DB::rollback();

            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengubah data'.$e ,
                'type' => 'error',
            ]);
        }
    }
}
