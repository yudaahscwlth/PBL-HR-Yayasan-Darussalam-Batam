<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            //administrator
            'manajemen_role',
            'manajemen_hak_akses',
            'manajemen_hak_akses_user',

            //dirpen
            'verifikasi_cuti_dirpen',

            //kepala hrd
            'verifikasi_cuti_kepala_hrd',
            'pengajuan_cuti_kepala_hrd',

            //hrd
            'manajemen_user',
            'manajemen_rekap_absensi',
            'manajemen_rekap_absensi_today',
            'manajemen_rekap_cuti_pegawai',
            'manajemen_evaluasi',
            'manajemen_rekap_evaluasi',
            'verifikasi_cuti_staff_hrd',
            'pengajuan_cuti_staff_hrd',

            // 'pengajuan_cuti_all',

            // kadep
            'manajemen_tenaga_pendidik_all',

            // kepsek
            'manajemen_tenaga_pendidik_kepsek',

            //kepsek & kadep
            'verifikasi_cuti_kepsek',
            'pengajuan_cuti_kepsek',

            //tenaga pendidik
            'pengajuan_cuti_tenaga_pendidik',

            //general
            'manajemen_profil',
            'rekap_absensi_pribadi',
            'rekap_evaluasi_pribadi',

            //other
            'manajemen_jabatan',
            'manajemen_jam_kerja',
            'manajemen_departemen',
            'manajemen_sosial_media',
            'manajemen_tempat_kerja',
            'manajemen_tahun_ajaran',
            'manajemen_kategori_evaluasi',
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        foreach ($modules as $module) {
            if (str_contains($module, '.')) {
                // Jika module sudah dalam format "module.action", langsung buat permission-nya
                Permission::firstOrCreate(['name' => $module]);
            } else {
                // Jika belum, maka buat permission untuk setiap action
                foreach ($actions as $action) {
                    Permission::firstOrCreate(['name' => "$module.$action"]);
                }
            }
        }



        // Mapping role ke permission module
        $rolePermissions = [
            'kepala yayasan' => [
                'manajemen_profil',
                'manajemen_user.read',
                'manajemen_rekap_evaluasi.read',
            ],
            'direktur pendidikan' => [
                'manajemen_profil',
                'rekap_absensi_pribadi',
                'rekap_evaluasi_pribadi',
                'verifikasi_cuti_dirpen',
                'manajemen_user',
                'manajemen_rekap_absensi.read',
                'manajemen_rekap_absensi_today.read',
                'manajemen_rekap_cuti_pegawai.read',
                'manajemen_rekap_evaluasi.read',
            ],
            'kepala hrd' => [
                'manajemen_profil',
                'rekap_absensi_pribadi',
                'rekap_evaluasi_pribadi',
                'manajemen_user',
                'manajemen_rekap_absensi.read',
                'manajemen_rekap_absensi_today.read',
                'manajemen_rekap_cuti_pegawai.read',
                'manajemen_rekap_evaluasi.read',
                'verifikasi_cuti_kepala_hrd',
                'pengajuan_cuti_kepala_hrd',

                //other
                'manajemen_jabatan',
                'manajemen_jam_kerja',
                'manajemen_departemen',
                'manajemen_sosial_media',
                'manajemen_tempat_kerja',
                'manajemen_tahun_ajaran',
                'manajemen_kategori_evaluasi',
            ],
            'staff hrd' => [
                'manajemen_profil',
                'rekap_absensi_pribadi',
                'rekap_evaluasi_pribadi',
                'manajemen_user',
                'manajemen_rekap_absensi',
                'manajemen_rekap_absensi_today',
                'manajemen_rekap_cuti_pegawai',
                'manajemen_rekap_evaluasi',
                'manajemen_evaluasi',
                'verifikasi_cuti_staff_hrd',
                'pengajuan_cuti_staff_hrd',

                //other
                'manajemen_jabatan',
                'manajemen_jam_kerja',
                'manajemen_departemen',
                'manajemen_sosial_media',
                'manajemen_tempat_kerja',
                'manajemen_tahun_ajaran',
                'manajemen_kategori_evaluasi',
            ],
            'kepala departemen' => [
                'manajemen_profil',
                'manajemen_tenaga_pendidik_all.read',
                'manajemen_rekap_absensi.read',
                'manajemen_rekap_cuti_pegawai.read',
                'manajemen_rekap_evaluasi.read',
                'manajemen_rekap_evaluasi.update',
                'manajemen_evaluasi',
                'rekap_absensi_pribadi',
                'rekap_evaluasi_pribadi',
                'pengajuan_cuti_kepsek'
            ],
            'kepala sekolah' => [
                'manajemen_profil',
                'manajemen_tenaga_pendidik_kepsek.read',
                'manajemen_rekap_absensi.read',
                'manajemen_rekap_cuti_pegawai.read',
                'manajemen_rekap_evaluasi.read',
                'manajemen_rekap_evaluasi.update',
                'manajemen_evaluasi',
                'rekap_absensi_pribadi',
                'rekap_evaluasi_pribadi',
                'pengajuan_cuti_kepsek',
                'verifikasi_cuti_kepsek',
            ],
            'tenaga pendidik' => [
                'manajemen_profil',
                'rekap_absensi_pribadi',
                'rekap_evaluasi_pribadi',
                'pengajuan_cuti_tenaga_pendidik'
            ],
        ];

        // Assign permission ke setiap role
        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            $permissionNames = collect($perms)->flatMap(function ($perm) use ($actions) {
                // Jika permission sudah berupa module.action, langsung pakai
                if (str_contains($perm, '.')) {
                    return [$perm];
                }

                // Kalau belum, beri semua aksi default (CRUD)
                return collect($actions)->map(fn($act) => "$perm.$act");
            })->toArray();

            $role->syncPermissions($permissionNames);
        }

        // Assign permissions to superadmin role
        $superAdminRole = Role::findByName('superadmin'); // Pastikan role 'superadmin' sudah ada

        // Ambil semua permission yang telah dibuat dan assign ke superadmin
        $permissions = Permission::all();
        $superAdminRole->givePermissionTo($permissions);
    }
}
