<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\PengajuanCuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showAdminDashboard(){
        $user = Auth::user();
        $selisih = Carbon::parse($user->profilePekerjaan->tanggal_masuk)->diff(Carbon::now());

        $pengajuanCuti = PengajuanCuti::latest()->get();

        // Ambil tahun dan bulan
        $tahunPengabdian = $selisih->y;
        $bulanPengabdian = $selisih->m;

        return view('admin.dashboard', [
            'dataProfile' => $user,
            'dataPengajuanCuti' => $pengajuanCuti,
            'tahunPengabdian' => $tahunPengabdian,
            'bulanPengabdian' => $bulanPengabdian,
        ]);
    }

    public function showPegawaiDashboard()
    {
        $user = Auth::user();
        $selisih = Carbon::parse($user->profilePekerjaan->tanggal_masuk)->diff(Carbon::now());

        //hitung jumlah tiap status
        $absensiCount = Absensi::where('id_user',$user->id)->get()->groupBy('status')->map(function ($item) {
            return $item->count();
        });

        // Tentukan urutan dan isi default 0 jika tidak ada
        $orderedStatuses = collect([
            'hadir' => 0,
            'terlambat' => 0,
            'sakit' => 0,
            'cuti' => 0,
            'alpa' => 0,
        ]);

         // Gabungkan hasil group dengan default
        $orderedStatusCounts = $orderedStatuses->merge($absensiCount);

        // Ambil tahun dan bulan
        $tahunPengabdian = $selisih->y;
        $bulanPengabdian = $selisih->m;

        return view('pegawai.dashboard', [
            'dataProfile' => $user,
            'tahunPengabdian' => $tahunPengabdian,
            'bulanPengabdian' => $bulanPengabdian,
            'statusCounts' => $orderedStatusCounts,
        ]);
    }

}
