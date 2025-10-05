<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\JenisCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CutiController extends Controller
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

        return view('admin.cuti.index', [
            'cuti' => $cuti,
            'jenisCuti' => $jenisCuti, // Add this line
            'pegawai' => $this->pegawai,
            'nama_departemen' => $this->nama_departemen,
        ]);
    }

    public function create()
    {
        $jenisCuti = JenisCuti::all();
        return view('admin.cuti.create', [
            'jenisCuti' => $jenisCuti
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        DB::statement("SET @current_user_id = " . $user->id_user);

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

        return redirect()->route('admin.cuti.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
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
                'no_hp' => $cuti->pegawai->no_hp
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
 

public function edit($id)
{
    $cuti = Cuti::with(['pegawai', 'jenisCuti'])->findOrFail($id);
    $user = Auth::user();

    // Check if the leave belongs to the logged-in user
    if ($cuti->id_pegawai !== $user->id_pegawai) {
        return abort(403, 'Anda tidak berhak mengakses data ini.');
    }

    // Only allow editing pending leaves
    if ($cuti->status_cuti !== 'Menunggu') {
        return redirect()->back()->with('error', 'Hanya pengajuan dengan status "Menunggu" yang dapat diedit.');
    }

    $jenisCuti = JenisCuti::all();
    $pegawai = $user->pegawai;
    $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

    // Hitung statistik cuti
    $jatah = $pegawai->jatah_tahunan ?? 12;
    $cutiTerpakai = Cuti::where('id_pegawai', $user->id_pegawai)
        ->where('status_cuti', 'Disetujui')
        ->where('id_cuti', '!=', $id) // Exclude current leave being edited
        ->get()
        ->sum(function($item) {
            return Carbon::parse($item->tanggal_mulai)
                ->diffInDays(Carbon::parse($item->tanggal_selesai)) + 1;
        });
    $sisaCuti = $jatah - $cutiTerpakai;

    return view('admin.cuti.edit', [
        'cuti' => $cuti,
        'jenisCuti' => $jenisCuti,
        'pegawai' => $pegawai,
        'nama_departemen' => $nama_departemen,
        'sisaCuti' => $sisaCuti,
        'jatah' => $jatah,
        'cutiTerpakai' => $cutiTerpakai,
    ]);
}

public function update(Request $request, $id)
{
    $cuti = Cuti::findOrFail($id);
    $user = Auth::user();
    DB::statement("SET @current_user_id = " . $user->id_user);

    // Check if the leave belongs to the logged-in user
    if ($cuti->id_pegawai !== $user->id_pegawai) {
        return abort(403, 'Anda tidak berhak mengakses data ini.');
    }

    // Only allow updating pending leaves
    if ($cuti->status_cuti !== 'Menunggu') {
        return redirect()->back()->with('error', 'Hanya pengajuan dengan status "Menunggu" yang dapat diupdate.');
    }

    $request->validate([
        'id_jenis_cuti' => 'required|exists:jenis_cuti,id_jenis_cuti',
        'tanggal_mulai' => 'required|date|after_or_equal:today',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'keterangan' => 'required|string|max:500',
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

    // Validasi maksimal hari cuti
    if ($workingDays > $jenisCuti->max_hari_cuti) {
        return redirect()->back()
            ->with('error', "Jumlah hari cuti ({$workingDays} hari) melebihi batas maksimal ({$jenisCuti->max_hari_cuti} hari) untuk jenis cuti ini.")
            ->withInput();
    }

    // Validasi sisa cuti (exclude current leave being edited)
    $cutiTerpakai = Cuti::where('id_pegawai', $user->id_pegawai)
        ->where('status_cuti', 'Disetujui')
        ->where('id_cuti', '!=', $id)
        ->get()
        ->sum(function($item) {
            return Carbon::parse($item->tanggal_mulai)
                ->diffInDays(Carbon::parse($item->tanggal_selesai)) + 1;
        });

    $jatah = $user->pegawai->jatah_tahunan ?? 12;
    $sisaCuti = $jatah - $cutiTerpakai;

    if ($workingDays > $sisaCuti) {
        return redirect()->back()
            ->with('error', "Jumlah hari cuti ({$workingDays} hari) melebihi sisa cuti Anda ({$sisaCuti} hari).")
            ->withInput();
    }

    // Update data cuti
    $cuti->id_jenis_cuti = $request->id_jenis_cuti;
    $cuti->tanggal_mulai = $request->tanggal_mulai;
    $cuti->tanggal_selesai = $request->tanggal_selesai;
    $cuti->keterangan = $request->keterangan;
    $cuti->save();

    return redirect()->route('admin.cuti.index')->with('success', 'Pengajuan cuti berhasil diperbarui.');
}

    public function destroy($id)
    {
        $cuti = Cuti::findOrFail($id);
        $user = Auth::user();
        DB::statement("SET @current_user_id = " . $user->id_user);

        // Check if the leave belongs to the logged-in user
        if ($cuti->id_pegawai !== $user->id_pegawai) {
            return abort(403, 'Anda tidak berhak mengakses data ini.');
        }

        // Only allow canceling pending leaves
        if ($cuti->status_cuti !== 'Menunggu') {
            return redirect()->back()->with('error', 'Hanya pengajuan dengan status "Menunggu" yang dapat dibatalkan.');
        }

        $cuti->delete();

        return redirect()->route('admin.cuti.index')->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }
}