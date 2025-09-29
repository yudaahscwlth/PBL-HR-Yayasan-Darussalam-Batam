<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\PengajuanCuti;
use App\Models\ProfilePekerjaan;
use App\Models\ProfilePribadi;
use App\Models\SosialMedia;
use App\Models\TempatKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function showKelolaPegawaiPage(Request $request){
        $user = Auth::user();

        if ($user->hasRole('kepala sekolah')) {
            // Hanya tenaga pendidik di tempat kerja yang sama
            $pegawai = User::whereHas('roles', function ($query) {
                    $query->where('name', 'tenaga pendidik');
                })
                ->whereHas('profilePekerjaan', function ($query) use ($user) {
                    $query->where('id_tempat_kerja', $user->profilePekerjaan->id_tempat_kerja);
                })
                ->with('profilePribadi', 'profilePekerjaan')
                ->orderByDesc('created_at')
                ->get();

            // Role yang boleh ditampilkan
            $roles = Role::where('name', 'tenaga pendidik')->get();

        } elseif ($user->hasRole('kepala departemen')) {
            // Hanya seluruh tenaga pendidik
            $pegawai = User::whereHas('roles', function ($query) {
                        $query->where('name', 'tenaga pendidik');
                    })
                    ->with('profilePribadi', 'profilePekerjaan')
                    ->orderByDesc('created_at')
                    ->get();

            $roles = Role::whereIn('name', ['tenaga pendidik', 'kepala sekolah'])->get();

        } elseif (!$user->hasRole('superadmin')) {
            // Semua kecuali superadmin dan kepala yayasan
            $pegawai = User::whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', ['superadmin', 'kepala yayasan']);
                })
                ->with('profilePribadi', 'profilePekerjaan')
                ->orderByDesc('created_at')
                ->get();

            $roles = Role::where('name', '!=', 'superadmin')->get();

        } else {
            // Superadmin bisa melihat semua
            $pegawai = User::with('profilePribadi', 'profilePekerjaan')
                ->orderByDesc('created_at')
                ->get();
            $roles = Role::all();
        }


        $jabatan = Jabatan::all();

        $departemen = Departemen::all();

        $tempatKerja = TempatKerja::all();

        $chartStatus = $this->chartStatusPegawai();

        $jsonKecamatan = File::get(resource_path('json/kecamatan-indonesia.json'));
        $dataKecamatanJson = json_decode($jsonKecamatan, true);

        // Urutkan berdasarkan key 'name'
        usort($dataKecamatanJson, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        //Deteksi apakah user mengaktifkan filter
        $isFiltering = $request->hasAny(['status', 'kecamatan', 'golongan_darah', 'usia']);

        // ⛳ Jika ya, panggil fungsi filter
        if ($isFiltering) {
            $pegawai = $this->filterPegawai($request);
        }

        return view('admin.kelola-pegawai',[
            'dataPegawai' => $pegawai,
            'dataJabatan' => $jabatan,
            'dataDepartemen' => $departemen,
            'dataTempatKerja' => $tempatKerja,
            'dataRoles' => $roles,
            'jumlahPegawaiPerTahun' => $this->chartJumlahPegawai(),
            'dataKecamatan' =>$dataKecamatanJson,
        ]+ $chartStatus);
    }

    private function filterPegawai(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('kepala sekolah')) {
            // Hanya tenaga pendidik di tempat kerja yang sama
            $query = User::whereHas('roles', function ($query) {
                    $query->where('name', 'tenaga pendidik');
                })
                ->whereHas('profilePekerjaan', function ($query) use ($user) {
                    $query->where('id_tempat_kerja', $user->profilePekerjaan->id_tempat_kerja);
                })
                ->with('profilePribadi', 'profilePekerjaan')
                ->orderByDesc('created_at');


        } elseif ($user->hasRole('kepala departemen')) {
            // Hanya tenaga pendidik dan kepala sekolah
            $query = User::whereHas('roles', function ($query) {
                        $query->where('name', 'tenaga pendidik');
                    })
                    ->with('profilePribadi', 'profilePekerjaan')
                    ->orderByDesc('created_at');

        } elseif (!$user->hasRole('superadmin')) {
            // Semua kecuali superadmin dan kepala yayasan
            $query = User::whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', ['superadmin', 'kepala yayasan']);
                })
                ->with('profilePribadi', 'profilePekerjaan')
                ->orderByDesc('created_at');

        } else {
            // Superadmin bisa melihat semua
            $query = User::with('profilePribadi', 'profilePekerjaan')
                ->orderByDesc('created_at');
        }


        // Filter Kecamatan
        if ($request->filled('kecamatan')) {
            $query->whereHas('profilePribadi', function ($q) use ($request) {
                $q->whereIn('kecamatan', $request->kecamatan);
            });
        }

        // Filter Golongan Darah
        if ($request->filled('golongan_darah')) {
            $query->whereHas('profilePribadi', function ($q) use ($request) {
                $q->whereIn('golongan_darah', $request->golongan_darah);
            });
        }

        //filter usia
        if ($request->filled('usia')) {
            $query->whereHas('profilePribadi', function ($q) use ($request) {
                $q->where(function ($subQuery) use ($request) {
                    foreach ($request->usia as $range) {
                        if ($range == '56+') {
                            $date = Carbon::now()->subYears(56)->format('Y-m-d');
                            $subQuery->orWhere('tanggal_lahir', '<=', $date);
                        } else {
                            [$min, $max] = explode('-', $range);
                            $maxDate = Carbon::now()->subYears($min)->format('Y-m-d');
                            $minDate = Carbon::now()->subYears($max)->addDay()->format('Y-m-d'); // +1 agar rentang tidak tumpang tindih
                            $subQuery->orWhereBetween('tanggal_lahir', [$minDate, $maxDate]);
                        }
                    }
                });
            });
        }

        return $query->get();
    }

    private function chartJumlahPegawai(){
        $pegawai = User::with('profilePekerjaan')->orderByDesc('created_at')->get();

        //Hitung jumlah pegawai per tahun masuk
        $jumlahPegawaiPerTahun = $pegawai->filter(function ($item) {
            return $item->profilePekerjaan && $item->profilePekerjaan->tanggal_masuk;
        })->groupBy(function ($item) {
            return Carbon::parse($item->profilePekerjaan->tanggal_masuk)->format('Y');
        })->map(function ($group) {
            return $group->count();
        });

        return $jumlahPegawaiPerTahun;
    }

    private function chartStatusPegawai(){
        $pegawai = User::with('profilePekerjaan')->orderByDesc('created_at')->get();

        //jumlah status pergawai per tahun
        $statusPegawaiPerTahun = $pegawai->filter(function ($item) {
            return $item->profilePekerjaan && $item->profilePekerjaan->tanggal_masuk;
        })
        ->map(function ($item) {
            return [
                'tahun' => Carbon::parse($item->profilePekerjaan->tanggal_masuk)->format('Y'),
                'status' => $item->profilePekerjaan->status, // pastikan ini valid, misal: 'aktif', 'keluar'
            ];
        })
        ->groupBy('tahun')
        ->map(function ($group) {
            return collect($group)->groupBy('status')->map(function ($statusGroup) {
                return count($statusGroup);
            });
        });

         // Ambil list tahun dan status
        $tahunList = $statusPegawaiPerTahun->keys()->sort()->values(); // contoh: ['2021', '2022']
        $statusList = $statusPegawaiPerTahun->flatMap(fn ($g) => $g->keys())->unique()->values(); // contoh: ['aktif', 'keluar']

        // Warna opsional
        $colors = [
            'aktif'     => '#4CAF50',   // Hijau - Menunjukkan aktif dan stabil
            'nonaktif'  => '#9E9E9E',   // Abu-abu - Netral/tidak aktif
            'kontrak'   => '#FF9800',   // Oranye - Sementara dan transisi
            'tetap'     => '#2196F3',   // Biru - Stabil dan permanen
            'magang'    => '#00BCD4',   // Cyan - Pembelajar atau trainee
            'honorer'   => '#795548',   // Coklat - Fleksibel/tidak tetap
            'pensiun'   => '#607D8B',   // Biru abu-abu - Tidak aktif tapi terhormat
            'cuti'      => '#FFC107',   // Kuning - Sementara istirahat
            'skorsing'  => '#F44336',   // Merah - Status darurat/masalah
        ];


        // Siapkan datasets untuk Chart.js
        $chartDatasets = [];
        foreach ($statusList as $status) {
            $data = [];
            foreach ($tahunList as $tahun) {
                $data[] = $statusPegawaiPerTahun[$tahun][$status] ?? 0;
            }

            $chartDatasets[] = [
                'label' => ucfirst($status),
                'data' => $data,
                'borderColor' => $colors[$status] ?? '#000',
                'backgroundColor' => $colors[$status] ?? '#000',
                'tension' => 0.1,
                'fill' => false,
            ];
        }

        return [
            'chartStatusLabels' => $tahunList,
            'chartStatusDatasets' => $chartDatasets,
        ];
    }

    public function showRekapCutiPegawaiPage($id_pegawai){
        $user = User::findOrFail($id_pegawai);

        $pengajuan_cuti = PengajuanCuti::where('id_user',$id_pegawai)->latest()->get();

        return view('admin.rekap-cuti-pegawai',[
            'dataUser' => $user,
            'dataCuti' => $pengajuan_cuti,
        ]);
    }

    public function tambahPegawai(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'nama_lengkap' => 'required|string|max:255',
            'nomor_induk_kependudukan' => 'required|numeric|unique:profile_pribadi,nomor_induk_kependudukan',
            'nomor_induk_karyawan' => 'required|string|unique:profile_pekerjaans,nomor_induk_karyawan',
            'tanggal_masuk' => 'required|date',
            'jabatan' => 'required',
            'departemen' => 'required',
            'tempat_kerja' => 'required',
            'status_karyawan' => 'required',
            'roles' => 'required',
        ],[
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus terdiri dari :min karakter.',

            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks.',
            'nama_lengkap.max' => 'Nama lengkap maksimal :max karakter.',

            'nomor_induk_kependudukan.required' => 'NIK wajib diisi.',
            'nomor_induk_kependudukan.numeric' => 'NIK harus berupa angka.',
            'nomor_induk_kependudukan.unique' => 'NIK sudah terdaftar.',

            'nomor_induk_karyawan.required' => 'Nomor induk karyawan wajib diisi.',
            'nomor_induk_karyawan.string' => 'Nomor induk karyawan harus berupa teks.',
            'nomor_induk_karyawan.unique' => 'Nomor induk karyawan sudah terdaftar.',

            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Tanggal masuk harus berupa tanggal yang valid.',

            'jabatan.required' => 'Jabatan wajib dipilih.',
            'departemen.required' => 'Departemen wajib dipilih.',
            'tempat_kerja.required' => 'Tempat Bekerja wajib dipilih.',
            'status_karyawan.required' => 'Status karyawan wajib dipilih.',

            'roles.required' => 'Roles wajib dipilih.',
        ]);

        try{
            DB::beginTransaction();

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            ProfilePribadi::create([
                'id_user' => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'nomor_induk_kependudukan' => $request->nomor_induk_kependudukan,
            ]);

            ProfilePekerjaan::create([
                'id_user' => $user->id,
                'id_jabatan' => $request->jabatan,
                'id_departemen' => $request->departemen,
                'id_tempat_kerja' => $request->tempat_kerja,
                'nomor_induk_karyawan' => $request->nomor_induk_karyawan,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => $request->status_karyawan,
            ]);

            $user->syncRoles($request->roles);

            DB::commit();

            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menambah data',
                'type' => 'success',
            ]);

        }catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with([
                'notifikasi' => 'Gagal menambah data' ,
                'type' => 'error',
            ]);
        }
    }

    public function hapusMassalPegawai(Request $request)
    {
        $ids = explode(',', $request->id); // Sesuai input name di form

        try {
            $deleted = User::whereIn('id', $ids)->delete();

            if ($deleted === 0) {
                return redirect()->back()->with([
                    'notifikasi' => 'Tidak ada data yang dihapus!',
                    'type' => 'warning',
                ]);
            }

            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menghapus ' . $deleted . ' data.',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menghapus data!',
                'type' => 'error',
            ]);
        }
    }

    public function showEditPegawaiPage($id_pegawai){
        $pegawai = User::where('id',$id_pegawai)->firstOrFail();
        $departemen = Departemen::all();
        $jabatan = jabatan::all();
        $sosialMedia = SosialMedia::all();

        $tempatKerja = TempatKerja::all();

        $roles = Role::all();

        //jika role bukan superadmin,sembunyikan opsi superadmin
        if (!Auth::user()->hasRole('superadmin')) {
            $roles = $roles->filter(fn($role) => $role->name !== 'superadmin');
        }

        $lamaPengabdian = Carbon::parse($pegawai->profilePekerjaan->tanggal_masuk)->diffForHumans(null, true);

        $jsonKota = File::get(resource_path('json/kota-indonesia.json'));
        $dataKotaJson = json_decode($jsonKota, true);

        $jsonKecamatan = File::get(resource_path('json/kecamatan-indonesia.json'));
        $dataKecamatanJson = json_decode($jsonKecamatan, true);

        return view('admin.edit-pegawai-profile',[
            'data' => $pegawai,
            'dataDepartemen' => $departemen,
            'dataTempatKerja' => $tempatKerja,
            'dataRoles' => $roles,
            'dataJabatan' => $jabatan,
            'dataSosialMedia' => $sosialMedia,
            'lamaPengabdian' => $lamaPengabdian,
            'allKota' => $dataKotaJson,
            'allKecamatan' => $dataKecamatanJson,
        ]);
    }

    public function updatePegawaiProfile(Request $request,$id_pegawai){
        $validatedData= $request->validate([
            //validasi user
            'email' => 'required|unique:users,email,'. $request->old_email .',email|email:dns' ,

            //validasi profile kerja
            'departemen' => 'required|exists:departemens,id',
            'tempat_kerja' => 'required|exists:tempat_kerjas,id',
            'jabatan' => 'required|exists:jabatans,id',
            'nomor_induk_karyawan' => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'status_karyawan' => 'required|in:aktif,nonaktif,kontrak,tetap,magang,honorer,pensiun,cuti,skorsing',
            'roles' => 'required',

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

            //profile kerja
            'departemen.required' => 'Departemen wajib dipilih.',
            'departemen.exists' => 'Departemen yang dipilih tidak valid.',
            'tempat_kerja.required' => 'Tempat Bekerja wajib dipilih.',
            'tempat_kerja.exists' => 'Tempat Bekerja yang dipilih tidak valid.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.exists' => 'Jabatan yang dipilih tidak valid.',
            'nomor_induk_karyawan.required' => 'Nomor Induk Karyawan wajib diisi.',
            'nomor_induk_karyawan.string' => 'Nomor Induk Karyawan harus berupa teks.',
            'nomor_induk_karyawan.max' => 'Nomor Induk Karyawan maksimal 100 karakter.',
            'tanggal_masuk.required' => 'Tanggal Masuk wajib diisi.',
            'tanggal_masuk.date' => 'Format Tanggal Masuk tidak valid.',
            'status_karyawan.required' => 'Status Karyawan wajib dipilih.',
            'status_karyawan.in' => 'Status Karyawan yang dipilih tidak valid.',
            'roles.required' => 'Role Karyawan wajib dipilih.',

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

            $user = User::where('id',$id_pegawai)->firstOrFail();
            $user->email = $request->email;
            $user->save();

            //update profile kerja
            $user->profilePekerjaan()->update([
                'id_user' => $user->id,
                'id_departemen' => $request->departemen,
                'id_tempat_kerja' => $request->tempat_kerja,
                'id_jabatan' => $request->jabatan,
                'nomor_induk_karyawan' => $request->nomor_induk_karyawan,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => $request->status_karyawan,
            ]);

            $user->syncRoles($request->roles);

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

            // Update Profile pribadi
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

    public function updatePasswordPegawai(Request $request,$id_pegawai){
        $validatedData = $request->validate([
            'password_baru' => 'required|min:8',
            'konf_password' => 'required|same:password_baru',
        ], [
            'password_baru.required' => 'Masukkan password baru.',
            'password_baru.min' => 'Password baru minimal terdiri dari 8 karakter.',
            'konf_password.required' => 'Masukkan konfirmasi password baru.',
            'konf_password.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = User::where('id',$id_pegawai)->firstOrFail();
        $user->password = Hash::make($request->password_baru);
        if ($user->save()) {
            return redirect()->back()->with([
                'notifikasi' => 'Password berhasil diperbarui!',
                'type' => 'success'
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Password gagal diperbarui!',
                'type' => 'error'
            ]);
        }
    }

    public function updatePassword(Request $request){
        $validatedData = $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|different:password_lama',
            'konf_password' => 'required|same:password_baru',
        ], [
            'password_lama.required' => 'Masukkan password saat ini.',
            'password_baru.required' => 'Masukkan password baru.',
            'password_baru.min' => 'Password baru minimal terdiri dari 8 karakter.',
            'password_baru.different' => 'Password baru harus berbeda dengan password saat ini.',
            'konf_password.required' => 'Masukkan konfirmasi password baru.',
            'konf_password.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($request->password_lama, Auth::user()->password)) {
            return redirect()->back()->withErrors(['password_lama' => 'Password salah.'])->withInput();
        }

        $user = User::where('id',Auth::user()->id)->firstOrFail();
        $user->password = Hash::make($request->password_baru);
        if ($user->save()) {
            return redirect()->back()->with([
                'notifikasi' => 'Password berhasil diperbarui!',
                'type' => 'success'
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Password gagal diperbarui!',
                'type' => 'error'
            ]);
        }
    }

    public function assignIndex(){
        $user = User::all();
        $roles = Role::all();

       // Ambil semua permission dan kelompokkan berdasarkan prefix (modul)
        // Contoh: 'user.create' → 'user' => ['create', 'read', ...]
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0]; // Ambil prefix modul
        })->map(function ($groupedPermissions) {
            // Ambil hanya action-nya, misalnya dari 'user.create' → 'create'
            $actions = $groupedPermissions->map(function ($permission) {
                return explode('.', $permission->name)[1];
            });

            // Tentukan urutan preferensi
            $preferredOrder = ['create', 'read', 'update', 'delete'];

            // Urutkan berdasarkan urutan preferensi
            return collect($preferredOrder)->filter(function ($action) use ($actions) {
                return $actions->contains($action);
            })->values();
        });

        return view('roles-and-permissions.user-assign',[
            'users' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function assignPermission(Request $request, $id_user) {
        $validate = $request->validate([
            'permissions' => 'array',
        ]);

        try {
            // Mencari user berdasarkan ID
            $user = User::findOrFail($id_user);

            // Menyinkronkan permissions dengan user
            $user->syncPermissions($request->permissions);

            return redirect()->back()->with([
                'notifikasi' => 'Permission berhasil ditambahkan ke user',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {  // Menangkap exception
            // Menangani error jika terjadi
            return redirect()->back()->with([
                'notifikasi' => 'Permission gagal ditambahkan ke user: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function assignRoles(Request $request, $id_user) {
        $validate = $request->validate([
            'roles' => 'array',
        ]);

        try {
            // Mencari user berdasarkan ID
            $user = User::findOrFail($id_user);

            // Menyinkronkan role dengan user
            $user->syncRoles($request->roles);

            return redirect()->back()->with([
                'notifikasi' => 'Role berhasil ditambahkan ke user',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {  // Menangkap exception
            // Menangani error jika terjadi
            return redirect()->back()->with([
                'notifikasi' => 'Role gagal ditambahkan ke user: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
}
