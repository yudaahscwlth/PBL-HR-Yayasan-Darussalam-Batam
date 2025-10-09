<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, AbsensiController, CutiController, ProfileController, KaryawanController,
    KuisionerController, PenilaianController, LokasiKantorController, ListPengajuanController,
    AdminDashboardController, RiwayatAbsensiController, PegawaiDashboardController,
    PeriodeController, PeriodeKuisionerController, PegawaiKuisionerController,
    PegawaiCutiController, KepalaDashboardController, KepalaListPengajuanController,
    RekapPenilaianSDMController, LogSistemController,UserController,DepartemenController
};

// Redirect root ke login
Route::redirect('/', '/login');

// ====================
// Autentikasi
// ====================
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// ====================
// HRD Routes
// ====================
Route::middleware(['auth', 'check.role:hrd'])->prefix('hrd')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.index');
    Route::post('/absen', [AdminDashboardController::class, 'absen'])->name('admin.absen');

    // Lokasi Kantor
    // Lokasi Kantor
    Route::resource('lokasi-kantor', LokasiKantorController::class)->names([
        'index'   => 'admin.LokasiKantor.index',
        'create'  => 'admin.LokasiKantor.tambah',
        'store'   => 'admin.LokasiKantor.store',
        'show'    => 'admin.LokasiKantor.show',
        'edit'    => 'admin.LokasiKantor.edit',
        'update'  => 'admin.LokasiKantor.update',
        'destroy' => 'admin.LokasiKantor.destroy',
    ]);
   

    Route::get('/lokasi-kantor/{id}', [AdminDashboardController::class, 'getLokasiKantor'])->name('admin.lokasi-kantor.get')->where('id', '[0-9]+');

    // Karyawan
    Route::prefix('pegawai')->group(function () {
        Route::get('/', [KaryawanController::class, 'index'])->name('admin.karyawan');
        Route::get('/create', [KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('/', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/{id}', [KaryawanController::class, 'show'])->name('pegawai.show');
        Route::get('/{id}/edit', [KaryawanController::class, 'edit'])->name('pegawai.edit');
        Route::put('/{id}', [KaryawanController::class, 'update'])->name('pegawai.update');
        Route::delete('/{id}', [KaryawanController::class, 'destroy'])->name('pegawai.destroy');
        Route::post('/{id}/generate-account', [KaryawanController::class, 'generateUserAccount'])->name('karyawan.generateAccount');
        Route::post('/{id}/reset-password', [KaryawanController::class, 'resetUserPassword'])->name('karyawan.resetPassword');
    });

    // Absensi
    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('admin.absensi');
        Route::get('/{id}/edit', [AbsensiController::class, 'edit'])->name('admin.absensi.edit');
        Route::put('/{id}', [AbsensiController::class, 'update'])->name('admin.absensi.update');
        Route::delete('/{id}', [AbsensiController::class, 'destroy'])->name('admin.absensi.destroy');
    });

    Route::get('/riwayat-absensi', [RiwayatAbsensiController::class, 'index'])->name('admin.RiwayatAbsensi');

    // Cuti
     Route::prefix('cuti')->name('admin.')->group(function (){
            Route::get('/', [CutiController::class, 'index'])->name('cuti.index');
            Route::get('/create', [CutiController::class, 'create'])->name('cuti.create');
            Route::post('/', [CutiController::class, 'store'])->name('cuti.store');
            Route::get('/{id}', [CutiController::class, 'show'])->name('cuti.show');
            Route::get('/{id}/edit', [CutiController::class, 'edit'])->name('cuti.edit');
            Route::put('/{id}', [CutiController::class, 'update'])->name('cuti.update');
            Route::delete('/{id}', [CutiController::class, 'destroy'])->name('cuti.destroy');
    });

    // Kuisioner
    Route::resource('kuisioner', KuisionerController::class)->except(['create', 'edit'])->names('admin.kuisioner');
    Route::get('kuisioner/create', [KuisionerController::class, 'create'])->name('admin.kuisioner.create');
    Route::get('kuisioner/{kuisioner}/edit', [KuisionerController::class, 'edit'])->name('admin.kuisioner.edit');
    Route::patch('kuisioner/{kuisioner}/toggle', [KuisionerController::class, 'toggle'])->name('admin.kuisioner.toggle');

    // Periode
    Route::prefix('periode')->group(function () {
        Route::resource('/', PeriodeController::class)->parameters(['' => 'id'])->names('periode');
        Route::prefix('{periodeId}/kuisioner')->group(function () {
            Route::get('/', [PeriodeKuisionerController::class, 'index'])->name('periode.kuisioner.index');
            Route::put('/', [PeriodeKuisionerController::class, 'update'])->name('periode.kuisioner.update');
            Route::post('/copy', [PeriodeKuisionerController::class, 'copyFromPeriode'])->name('periode.kuisioner.copy');
            Route::delete('/reset', [PeriodeKuisionerController::class, 'reset'])->name('periode.kuisioner.reset');
            Route::post('/auto-select', [PeriodeKuisionerController::class, 'autoSelect'])->name('periode.kuisioner.auto-select');
        });
        Route::post('/{periode}/kuisioner/bulk-action', [PeriodeKuisionerController::class, 'bulkAction'])->name('admin.periode.kuisioner.bulk-action');
    });

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.edit-profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('admin.password.update');

    // Pengajuan
    Route::get('/list-pengajuan', [ListPengajuanController::class, 'index'])->name('admin.listPengajuan');
    Route::put('/cuti/{id}/update-status', [ListPengajuanController::class, 'updateStatus'])->name('cuti.updateStatus');
    Route::get('/cuti/{id}/detail', [ListPengajuanController::class, 'show'])->name('cuti.detail');

    // Log Sistem
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/log-sistem', [LogSistemController::class, 'index'])->name('log-sistem.index');
        Route::delete('/log-sistem/{id}', [LogSistemController::class, 'destroy'])->name('log-sistem.destroy');
        Route::delete('/log-sistem-clear-all', [LogSistemController::class, 'clearAll'])->name('log-sistem.clear-all');
    });

Route::name('admin.')->group(function () {
 // User Management Routes
Route::get('user', [UserController::class, 'index'])->name('user.index');
Route::get('user/create', [UserController::class, 'create'])->name('user.create');
Route::post('user', [UserController::class, 'store'])->name('user.store');
Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
Route::put('user/{id}', [UserController::class, 'update'])->name('user.update');
Route::delete('user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

// Additional routes for user management
Route::post('user/create-for-pegawai', [UserController::class, 'createForPegawai'])->name('user.create-for-pegawai');
Route::post('user/create-multiple', [UserController::class, 'createMultiple'])->name('user.create-multiple');
});

});

// ====================
// Pegawai Routes
// ====================
Route::prefix('pegawai')->middleware(['check.role:pegawai'])->group(function () {
    Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('karyawan.index');
    Route::post('/absen', [PegawaiDashboardController::class, 'absen'])->name('pegawai.absensi');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('karyawan.edit-profil');
    Route::post('/profile', [ProfileController::class, 'update'])->name('pegawai.profile.update');
    Route::get('/absensi', [RiwayatAbsensiController::class, 'index'])->name('karyawan.absensi');

    // Kuisioner
    Route::prefix('kuisioner')->name('kuisioner.')->group(function () {
        Route::get('/', [PegawaiKuisionerController::class, 'index'])->name('index');
        Route::get('/get-all-pegawai', [PegawaiKuisionerController::class, 'getAllPegawai']);
        Route::get('/debug/pegawai', [PegawaiKuisionerController::class, 'debugPegawai']);
        Route::get('/{periode}/{dinilai}', [PegawaiKuisionerController::class, 'show'])->name('show');
        Route::post('/{periode}/{dinilai}', [PegawaiKuisionerController::class, 'store'])->name('store');
        Route::get('/{periode}/{dinilai}/reset', [PegawaiKuisionerController::class, 'reset'])->name('reset');
        Route::get('/{periode}/{dinilai}/result', [PegawaiKuisionerController::class, 'result'])->name('result');
        Route::get('/history', [PegawaiKuisionerController::class, 'history'])->name('history');
        Route::delete('/{periode}/{dinilai}', [PegawaiKuisionerController::class, 'destroy'])->name('destroy');
    });

    // Cuti
    Route::resource('cuti', PegawaiCutiController::class)->names('pegawai.cuti');
});

// ====================
// Kepala Yayasan Routes
// ====================
Route::middleware(['auth', 'check.role:kepala_yayasan'])->prefix('kepala_yayasan')->group(function () {
    Route::get('/dashboard', [KepalaDashboardController::class, 'index'])->name('kepala.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('kepala.edit-profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('kepala.profile.update');
    Route::post('/password/update', [ProfileController::class, 'updatePassword'])->name('kepala.password.update');

    Route::prefix('list-pengajuan')->name('kepala.')->group(function () {
        Route::get('/', [KepalaListPengajuanController::class, 'index'])->name('listPengajuan');
        Route::put('/cuti/{id}/status', [KepalaListPengajuanController::class, 'updateStatus'])->name('cuti.updateStatus');
        Route::get('/cuti/{id}/detail', [KepalaListPengajuanController::class, 'show'])->name('cuti.detail');
    });

    Route::prefix('kepala-yayasan/rekap-sdm')->name('kepala.rekap.')->group(function () {
        Route::get('/', [RekapPenilaianSDMController::class, 'index'])->name('index');
        Route::get('/detail/{pegawaiId}', [RekapPenilaianSDMController::class, 'detail'])->name('detail');
        Route::get('/export', [RekapPenilaianSDMController::class, 'export'])->name('export');

        // API
        Route::prefix('api')->group(function () {
            Route::get('/data', [RekapPenilaianSDMController::class, 'getRekapData'])->name('api.data');
            Route::get('/statistik', [RekapPenilaianSDMController::class, 'getStatistikDashboard'])->name('api.statistik');
        });
    });
});
