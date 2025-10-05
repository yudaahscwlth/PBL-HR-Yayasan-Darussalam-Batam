<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\JenisCuti;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PegawaiCutiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil cuti berdasarkan pegawai yang login
        $cuti = Cuti::with('jenisCuti')
            ->where('id_pegawai', $user->id_pegawai)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        // Ambil semua jenis cuti untuk form modal
        $jenisCuti = JenisCuti::all();
            $pegawai = $user->pegawai; // or however you get pegawai data
    $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

        return view('karyawan.cuti.index', [
            'cuti' => $cuti,
            'jenisCuti' => $jenisCuti, // Add this line
            'pegawai' => $this->pegawai,
            'nama_departemen' => $this->nama_departemen,
        ]);
    }

    public function create()
    {
        $jenisCuti = JenisCuti::all();
        return view('karyawan.cuti.create', [
            'jenisCuti' => $jenisCuti
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_jenis_cuti' => 'required|exists:jenis_cuti,id_jenis_cuti',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string',
            'konfirmasi' => 'required|accepted',
        ]);

        $jenisCuti = JenisCuti::findOrFail($request->id_jenis_cuti);
        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_selesai);

        // Hitung hari kerja (tidak termasuk Sabtu dan Minggu)
        $workingDays = 0;
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            if (!in_array($currentDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        if ($workingDays > $jenisCuti->max_hari_cuti) {
            return redirect()->back()
                ->with('error', "Jumlah hari cuti ({$workingDays} hari) melebihi batas maksimal ({$jenisCuti->max_hari_cuti} hari) untuk jenis cuti ini.")
                ->withInput();
        }

        $cuti = new Cuti();
        $cuti->id_pegawai = $user->id_pegawai;
        $cuti->id_jenis_cuti = $request->id_jenis_cuti;
        $cuti->tanggal_pengajuan = now(); // Set current date automatically
        $cuti->tanggal_mulai = $request->tanggal_mulai;
        $cuti->tanggal_selesai = $request->tanggal_selesai;
        $cuti->status_cuti = 'Menunggu'; // Set default status
        $cuti->keterangan = $request->keterangan;
        $cuti->save();

        return redirect()->route('pegawai.cuti.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function show($id)
    {
        $cuti = Cuti::with(['pegawai', 'jenisCuti'])->findOrFail($id);
        $user = Auth::user();

        if ($cuti->id_pegawai !== $user->id_pegawai) {
            return abort(403, 'Anda tidak berhak mengakses data ini.');
        }

        // Calculate working days for display
        $startDate = Carbon::parse($cuti->tanggal_mulai);
        $endDate = Carbon::parse($cuti->tanggal_selesai);
        $workingDays = 0;
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            if (!in_array($currentDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return response()->json([
            'id_cuti' => $cuti->id_cuti,
            'pegawai' => [
                'nama_pegawai' => $cuti->pegawai->nama_pegawai,
                'nip' => $cuti->pegawai->nip
            ],
            'jenis_cuti' => [
                'nama_jenis_cuti' => $cuti->jenisCuti->nama_jenis_cuti,
                'max_hari_cuti' => $cuti->jenisCuti->max_hari_cuti
            ],
            'tanggal_pengajuan' => $cuti->tanggal_pengajuan,
            'tanggal_mulai' => $cuti->tanggal_mulai,
            'tanggal_selesai' => $cuti->tanggal_selesai,
            'status_cuti' => $cuti->status_cuti,
            'keterangan' => $cuti->keterangan,
            'jumlah_hari' => $workingDays,
        ]);
    }

    public function destroy($id)
    {
        $cuti = Cuti::findOrFail($id);
        $user = Auth::user();

        // Check if the leave belongs to the logged-in user
        if ($cuti->id_pegawai !== $user->id_pegawai) {
            return abort(403, 'Anda tidak berhak mengakses data ini.');
        }

        // Only allow canceling pending leaves
        if ($cuti->status_cuti !== 'Menunggu') {
            return redirect()->back()->with('error', 'Hanya pengajuan dengan status "Menunggu" yang dapat dibatalkan.');
        }

        $cuti->delete();

        return redirect()->route('pegawai.cuti.index')->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }
}