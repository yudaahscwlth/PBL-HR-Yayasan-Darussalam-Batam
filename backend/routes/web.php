<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JamKerjaController;
use App\Http\Controllers\KategoriEvaluasiController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\LogAktivitasAbsensiController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SosialMediaController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TempatKerjaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSosialMediaController;
use App\Http\Controllers\VerifikasiCutiController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginPage'])->name('login.page');
    Route::post('/proses-login', [AuthController::class, 'loginProcess'])->name('login.process');
});

Route::middleware(['auth'])->group(function () {

    //roles route
    Route::prefix('role')->name('role.')->group(function(){
        Route::get('/',[RolesController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_role.read')
        ->name('index');
        Route::post('/',[RolesController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_role.create')
        ->name('store');
        Route::put('/{id_role}',[RolesController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_role.update')
        ->name('update');
        Route::delete('/{id_role}',[RolesController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_role.destroy')
        ->name('destroy');
    });

    //permission route
    Route::prefix('permission')->name('permission.')->group(function(){
        Route::get('/',[PermissionController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_hak_akses.read')
        ->name('index');
        Route::post('/',[PermissionController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_hak_akses.create')
        ->name('store');
        Route::put('/{id_permission}',[PermissionController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_hak_akses.update')
        ->name('update');
        Route::delete('/mass-delete',[PermissionController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_hak_akses.delete')
        ->name('destroy');
    });

    //user assign route
    Route::prefix('users')->name('user.assign.')->group(function(){
        Route::get('assign', [UserController::class, 'assignIndex'])->middleware('Check_Roles_or_Permissions:manajemen_hak_akses_user.read')
        ->name('index');

        Route::middleware('Check_Roles_or_Permissions:manajemen_hak_akses_user.update')->group(function () {
            Route::put('{id}/assign-roles', [UserController::class, 'assignRoles'])
            ->name('roles.update');
            Route::put('{id}/assign-permisson', [UserController::class, 'assignPermission'])
            ->name('permissions.update');
        });
    });

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function(){
        Route::get('/', [ProfileController::class, 'showProfilePage'])->middleware('Check_Roles_or_Permissions:manajemen_profil.read')
        ->name('page');
        Route::middleware('Check_Roles_or_Permissions:manajemen_profil.update')->group(function(){
            Route::put('/update', [ProfileController::class, 'update'])
            ->name('update');
            Route::put('/change-password',([UserController::class,'updatePassword']))
            ->name('password.update');
        });
    });

    //keluarga routes
    Route::prefix('keluarga/')->name('keluarga.')->group(function(){
        Route::delete('/{id_keluarga}/delete',[KeluargaController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_profil.delete')
        ->name('destroy');
    });

    //user sosial media routes
    Route::prefix('user/sosmed')->name('user.sosmed.')->group(function(){
        Route::delete('/{id_user_sosmed}/delete',[UserSosialMediaController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_profil.delete')
        ->name('destroy');
    });

    // Kelola Pegawai routes
    Route::prefix('kelola/pegawai')->name('kelola.pegawai.')->group(function(){
        Route::get('/',[UserController::class,'showKelolaPegawaiPage'])->middleware('Check_Roles_or_Permissions:manajemen_user.read|manajemen_tenaga_pendidik_kepsek.read|manajemen_tenaga_pendidik_all.read')
        ->name('page');

        //crud pegawai
        Route::get('/{id_pegawai}/profile',[UserController::class,'showEditPegawaiPage'])->middleware('Check_Roles_or_Permissions:manajemen_user.read|manajemen_tenaga_pendidik_kepsek.read|manajemen_tenaga_pendidik_all.read')
        ->name('edit.page');

        Route::post('/',[UserController::class,'tambahPegawai'])->middleware('Check_Roles_or_Permissions:manajemen_user.create|manajemen_tenaga_pendidik_kepsek.create|manajemen_tenaga_pendidik_all.create')
        ->name('store');

        Route::put('/{id_pegawai}/update',[UserController::class,'updatePegawaiProfile'])->middleware('Check_Roles_or_Permissions:manajemen_user.update|manajemen_tenaga_pendidik_kepsek.update|manajemen_tenaga_pendidik_all.update')
        ->name('update');

        Route::put('/{id_pegawai}/password',([UserController::class,'updatePasswordPegawai']))->middleware('Check_Roles_or_Permissions:manajemen_user.update|manajemen_tenaga_pendidik_kepsek.update|manajemen_tenaga_pendidik_all.update')
        ->name('password.update');

        Route::delete('/kelola/mass-delete/pegawai', [UserController::class, 'hapusMassalPegawai'])->middleware('Check_Roles_or_Permissions:manajemen_user.delete|manajemen_tenaga_pendidik_kepsek.delete|manajemen_tenaga_pendidik_all.delete')
        ->name('mass.delete');

        //manajemen rekap absen
        Route::get('/{id_pegawai}/rekap-absen',[AbsensiController::class,'showRekapPegawaiPage'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi.read')
        ->name('rekap.absen.page');

        Route::post('/{id_pegawai}/rekap-absen/store',[AbsensiController::class,'rekapPegawaiStore'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi.create')
        ->name('rekap.absen.store');

        //manajemen rekap cuti
        Route::get('/{id_pegawai}/rekap-cuti',[UserController::class,'showRekapCutiPegawaiPage'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_cuti_pegawai.read')
        ->name('rekap.cuti.page');

        Route::post('/{id_pegawai}/rekap-cuti/store',[PengajuanCutiController::class,'storeRekapCuti'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_cuti_pegawai.create')
        ->name('rekap.cuti.store');

        Route::put('/{id_pengajuan}/rekap-cuti/update',[PengajuanCutiController::class,'updateRekapCuti'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_cuti_pegawai.update')
        ->name('rekap.cuti.update');

        Route::delete('/{id_pengajuan}/rekap-cuti/delete',[PengajuanCutiController::class,'destroyRekapCuti'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_cuti_pegawai.delete')
        ->name('rekap.cuti.destroy');
    });

    //pengajuan cuti routes
    Route::prefix('pengajuan/cuti')->name('pengajuan.cuti.')->group(function(){
        //halaman Tenaga pendidik
        Route::get('/tendik', [PengajuanCutiController::class, 'showPengajuanCutiTendikPage'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_tenaga_pendidik.read')
        ->name('tendik.page');

        Route::post('/tendik/store', [PengajuanCutiController::class, 'storePengajuanTendik'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_tenaga_pendidik.create')
        ->name('tendik.store');

        //halaman Kepala sekolah dan kepala departemen
        Route::get('/kepsek', [PengajuanCutiController::class, 'showPengajuanCutiKepsekPage'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_kepsek.read')
        ->name('kepsek.page');

        Route::post('/kepsek/store', [PengajuanCutiController::class, 'storePengajuanKepsek'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_kepsek.create')
        ->name('kepsek.store');

        //halaman staff hrd
        Route::get('/staff-hrd', [PengajuanCutiController::class, 'showPengajuanCutiStaffHrdPage'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_staff_hrd.read')
        ->name('staff.hrd.page');

        Route::post('/staff-hrd/store', [PengajuanCutiController::class, 'storePengajuanStaffHrd'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_staff_hrd.create')
        ->name('staff.hrd.store');

        //halaman kepala hrd
        Route::get('/kepala-hrd', [PengajuanCutiController::class, 'showPengajuanCutiKepalaHrdPage'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_kepala_hrd.read')
        ->name('kepala.hrd.page');

        Route::post('/kepala-hrd/store', [PengajuanCutiController::class, 'storePengajuanKepalaHrd'])->middleware('Check_Roles_or_Permissions:pengajuan_cuti_kepala_hrd.create')
        ->name('kepala.hrd.store');
    });

    // verifikasi cuti routes
    Route::prefix('verifikasi-cuti')->name('verifikasi.cuti.')->group(function(){
        //halaman kepsek
        Route::get('/kepsek', [VerifikasiCutiController::class, 'showVerifikasiKepsekPage'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_kepsek.read')
        ->name('kepsek.page');

        Route::put('{id_pengajuan}/kepsek', [VerifikasiCutiController::class, 'verifikasiCutiKepsek'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_kepsek.update')
        ->name('kepsek.update');

        //halaman dirpen
        Route::get('/dirpen', [VerifikasiCutiController::class, 'showVerifikasiDirpenPage'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_dirpen.read')
        ->name('dirpen.page');

        Route::put('{id_pengajuan}/dirpen', [VerifikasiCutiController::class, 'verifikasiCutiDirpen'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_dirpen.update')
        ->name('dirpen.update');

        //halaman staff hrd
        Route::get('/staff-hrd', [VerifikasiCutiController::class, 'showVerifikasiHrdPage'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_staff_hrd.read')
        ->name('hrd.page');

        Route::put('{id_pengajuan}/staff-hrd', [VerifikasiCutiController::class, 'verifikasiCutiHrd'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_staff_hrd.update')
        ->name('hrd.update');

        //halaman kepala hrd
        Route::get('/kepala-hrd', [VerifikasiCutiController::class, 'showVerifikasiKepalaHrdPage'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_kepala_hrd.read')
        ->name('kepala.hrd.page');

        Route::put('{id_pengajuan}/kepala-hrd', [VerifikasiCutiController::class, 'verifikasiCutiKepalaHrd'])->middleware('Check_Roles_or_Permissions:verifikasi_cuti_kepala_hrd.update')
        ->name('kepala.hrd.update');

    });

    //absensi route
    Route::prefix('absensi')->name('absensi.')->group(function(){
        //log absensi
        Route::get('{id_absensi}/log', [LogAktivitasAbsensiController::class, 'showLogAbsenPage'])->middleware('Check_Roles_or_Permissions:rekap_absensi_pribadi.read')
        ->name('log.page');

        Route::post('/check-in',[AbsensiController::class,'checkInProcess'])->middleware('Check_Roles_or_Permissions:rekap_absensi_pribadi.create')
        ->name('check.in');

        Route::put('/check-out',[AbsensiController::class,'checkOutProcess'])->middleware('Check_Roles_or_Permissions:rekap_absensi_pribadi.create')
        ->name('check.out');
    });

     //rekap absensi routes
    Route::prefix('rekap/absensi')->name('rekap.absensi.')->group(function(){
        Route::get('/hari-ini', [AbsensiController::class, 'showRekapTodayPage'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi_today.read')
        ->name('today.page');

        Route::post('/hari-ini/store', [AbsensiController::class, 'rekapTodayStore'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi_today.create')
        ->name('today.store');

        Route::get('/pribadi', [AbsensiController::class, 'showRekapPribadiPage'])->middleware('Check_Roles_or_Permissions:rekap_absensi_pribadi.read')
        ->name('pribadi.page');

        Route::post('pribadi/store', [AbsensiController::class, 'rekapPribadiStore'])->middleware('Check_Roles_or_Permissions:rekap_absensi_pribadi.create')
        ->name('pribadi.store');

        Route::put('{id_absensi}/update', [AbsensiController::class, 'rekapUpdate'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi_today.update|manajemen_rekap_absensi.update')
        ->name('update');

        Route::delete('{id_absensi}/destroy', [AbsensiController::class, 'rekapDestroy'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi_today.delete|manajemen_rekap_absensi.delete')
        ->name('delete');

        Route::put('{id_absensi}/restore', [AbsensiController::class, 'rekapRestore'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_absensi_today.delete|manajemen_rekap_absensi.delete')
        ->name('restore');
    });

    //HRD pages
    Route::prefix('hrd')->name('hrd.')->middleware('Check_Roles_or_Permissions:superadmin|kepala yayasan|direktur pendidikan|kepala hrd|staff hrd')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'showAdminDashboard'])
        ->name('dashboard.page');
    });

    //pegawai pages
    Route::prefix('pegawai')->name('pegawai.')->middleware('Check_Roles_or_Permissions:superadmin|kepala departemen|kepala sekolah|tenaga pendidik')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'showPegawaiDashboard'])->name('dashboard.page');
    });

    //jabatan routes
    Route::prefix('jabatan')->name('jabatan.')->group(function(){
        Route::get('/',[JabatanController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_jabatan.read')
        ->name('index');

        Route::post('/',[JabatanController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_jabatan.create')
        ->name('store');

        Route::put('/{id_jabatan}',[JabatanController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_jabatan.update')
        ->name('update');

        Route::delete('/{id_jabatan}',[JabatanController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_jabatan.delete')
        ->name('destroy');

        Route::prefix('{id_jabatan}/jam-kerja')->name('jam.kerja.')->group(function(){
            Route::get('/',[JamKerjaController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_jam_kerja.read')
            ->name('index');

            Route::post('/',[JamKerjaController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_jam_kerja.create')
            ->name('store');

            Route::put('/{id_jam_kerja}',[JamKerjaController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_jam_kerja.update')
            ->name('update');

            Route::delete('/{id_jam_kerja}',[JamKerjaController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_jam_kerja.delete')
            ->name('destroy');
        });
    });

    //departemen routes
    Route::prefix('departemen')->name('departemen.')->group(function(){
        Route::get('/',[DepartemenController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_departemen.read')
        ->name('index');

        Route::post('/',[DepartemenController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_departemen.create')
        ->name('store');

        Route::put('/{id_departemen}',[DepartemenController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_departemen.update')
        ->name('update');

        Route::delete('/{id_departemen}',[DepartemenController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_departemen.delete')
        ->name('destroy');
    });

    //sosialmedia routes
    Route::prefix('sosial-media')->name('sosmed.')->group(function(){
        Route::get('/',[SosialMediaController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_sosial_media.read')
        ->name('index');

        Route::post('/',[SosialMediaController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_sosial_media.create')
        ->name('store');

        Route::put('/{id_sosmed}',[SosialMediaController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_sosial_media.update')
        ->name('update');

        Route::delete('/{id_sosmed}',[SosialMediaController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_sosial_media.delete')
        ->name('destroy');
    });

    //tempat kerja routes
    Route::prefix('kantor')->name('kantor.')->group(function(){
        Route::get('/',[TempatKerjaController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_tempat_kerja.read')
        ->name('index');

        Route::post('/',[TempatKerjaController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_tempat_kerja.create')
        ->name('store');

        Route::put('/{id_tempat_kerja}',[TempatKerjaController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_tempat_kerja.update')
        ->name('update');

        Route::delete('/{id_tempat_kerja}',[TempatKerjaController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_tempat_kerja.delete')
        ->name('destroy');
    });

    //tahun ajaran routes
    Route::prefix('tahun-ajaran')->name('tahun.ajaran.')->group(function(){
        Route::get('/',[TahunAjaranController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_tahun_ajaran.read')
        ->name('index');

        Route::post('/',[TahunAjaranController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_tahun_ajaran.create')
        ->name('store');

        Route::put('/{id_tahun_ajaran}',[TahunAjaranController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_tahun_ajaran.update')
        ->name('update');

        Route::delete('/{id_tahun_ajaran}',[TahunAjaranController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_tahun_ajaran.delete')
        ->name('destroy');
    });

    //kategori evaluasi routes
    Route::prefix('kategori-evaluasi')->name('kategori.evaluasi.')->group(function(){
        Route::get('/',[KategoriEvaluasiController::class,'index'])->middleware('Check_Roles_or_Permissions:manajemen_kategori_evaluasi.read')
        ->name('index');

        Route::post('/',[KategoriEvaluasiController::class,'store'])->middleware('Check_Roles_or_Permissions:manajemen_kategori_evaluasi.create')
        ->name('store');

        Route::put('/{id_kategori}',[KategoriEvaluasiController::class,'update'])->middleware('Check_Roles_or_Permissions:manajemen_kategori_evaluasi.update')
        ->name('update');

        Route::delete('/{id_kategori}',[KategoriEvaluasiController::class,'destroy'])->middleware('Check_Roles_or_Permissions:manajemen_kategori_evaluasi.delete')
        ->name('destroy');
    });

    //evaluasi routes
    Route::prefix('evaluasi')->name('evaluasi.')->group(function(){
        Route::get('/',[EvaluasiController::class,'showEvaluasiPage'])->middleware('Check_Roles_or_Permissions:manajemen_evaluasi.read')
        ->name('pegawai.page');

        Route::post('/store',[EvaluasiController::class,'storeEvaluasi'])->middleware('Check_Roles_or_Permissions:manajemen_evaluasi.create')
        ->name('store');

        Route::put('/{id_pegawai}/{id_tahun_ajaran}/update',[EvaluasiController::class,'updateEvaluasi'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_evaluasi.update')
        ->name('update');

        Route::get('/rekap/pribadi',[EvaluasiController::class,'showRekapPribadiPage'])->middleware('Check_Roles_or_Permissions:rekap_evaluasi_pribadi.read')
        ->name('rekap.pribadi.page');

        Route::get('/rekap/{id_pegawai}/pegawai',[EvaluasiController::class,'showRekapPegawaiPage'])->middleware('Check_Roles_or_Permissions:manajemen_rekap_evaluasi.read')
        ->name('rekap.pegawai.page');
    });

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
