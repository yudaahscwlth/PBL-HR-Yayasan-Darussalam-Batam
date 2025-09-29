<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\JamKerja;
use App\Models\LogAktivitasAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\error;

class AbsensiController extends Controller
{
    public function showRekapTodayPage(){
        $today = Carbon::today();

        $user = User::all();

        $absensi = Absensi::withTrashed()->where('tanggal',$today)->latest()->get();

        return view('admin.kelola-absensi-today',[
            'dataAbsensi' => $absensi,
            'dataUser' => $user,
        ]);
    }

    public function rekapTodayStore(Request $request){
        $validatedData = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'id_user' => 'required|exists:users,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after_or_equal:check_in',
            'status' => 'required|in:hadir,sakit,cuti,terlambat,alpa',
            'file_pendukung' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            // Lokasi
            'latitude.required' => 'Lokasi latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'longitude.required' => 'Lokasi longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',

            // User
            'id_user.required' => 'Pengguna wajib dipilih.',
            'id_user.exists' => 'Pengguna tidak valid.',

            // Waktu
            'check_in.required' => 'Waktu check-in wajib diisi.',
            'check_in.date' => 'Format check-in tidak valid.',
            'check_out.required' => 'Waktu check-out wajib diisi.',
            'check_out.date' => 'Format check-out tidak valid.',
            'check_out.after_or_equal' => 'Waktu check-out harus setelah atau sama dengan waktu check-in.',

            // Status
            'status.required' => 'Status absensi wajib dipilih.',
            'status.in' => 'Status absensi tidak valid. Pilihan yang tersedia: hadir, sakit, cuti, terlambat.',

            // File
            'file_pendukung.file' => 'File pendukung harus berupa file.',
            'file_pendukung.mimes' => 'Format file hanya boleh: jpg, jpeg, png, pdf.',
            'file_pendukung.max' => 'Ukuran file maksimal 2MB.',

            // Keterangan
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
        ]);

        $filePath = null;
        if ($request->hasFile('file_pendukung')) {
            $filePath = $request->file('file_pendukung')->store('file_pendukung', 'public');
        }

        $tanggalToday = Carbon::today();

        $sudahAbsen = Absensi::where('id_user', $request->id_user)
            ->whereDate('tanggal', $tanggalToday)
            ->exists();

        if (!$sudahAbsen) {
            $save = Absensi::create([
                'id_user' => $request->id_user,
                'tanggal' => $tanggalToday,
                'status' => $request->status,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'latitude_out' => $request->latitude,
                'longitude_out' => $request->longitude,
                'keterangan' => $request->keterangan,
                'file_pendukung' => $filePath,
            ]);

            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menambahkan absen untuk hari ini.',
                'type' => 'success'
            ]);
        }

        return redirect()->back()->with([
            'notifikasi' => 'Absen untuk hari ini sudah tercatat.',
            'type' => 'warning'
        ]);
    }

    public function showRekapPribadiPage(){
        $user = Auth::user();
        $absensi = Absensi::withTrashed()->where('id_user',$user->id)->orderByDesc('tanggal')->get();

        return view('pegawai.rekap-absensi-pribadi',[
            'dataAbsensi' => $absensi,
        ]);
    }

    public function rekapPribadiStore(Request $request){
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'tanggal' => 'required|date',
            'durasi_hari' => 'required|integer|min:0',
            'status' => 'required',
            'file_pendukung' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            // Latitude & Longitude
            'latitude.required' => 'Lokasi latitude wajib dikirim.',
            'longitude.required' => 'Lokasi longitude wajib dikirim.',

            // Tanggal
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',

            // Durasi
            'durasi_hari.required' => 'Durasi hari wajib diisi.',
            'durasi_hari.integer' => 'Durasi hari harus berupa angka.',
            'durasi_hari.min' => 'Durasi hari minimal 0 (hari ini saja).',

            // Status
            'status.required' => 'Status wajib dipilih.',

            // File
            'file_pendukung.required' => 'File pendukung wajib diisi.',
            'file_pendukung.file' => 'File pendukung harus berupa file.',
            'file_pendukung.mimes' => 'File hanya boleh berformat: jpg, jpeg, png, atau pdf.',
            'file_pendukung.max' => 'Ukuran file maksimal 2MB.',

            // Keterangan
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('file_pendukung')) {
                $filePath = $request->file('file_pendukung')->store('file_pendukung', 'public');
            }

            $user = Auth::user();
            $tanggalAwal = Carbon::parse($request->tanggal);
            $durasi = $request->durasi_hari;
            $jumlahDisimpan = 0;

            for ($i = 0; $i <= $durasi; $i++) {
                $tanggalAbsen = $tanggalAwal->copy()->addDays($i);

                $sudahAbsen = Absensi::where('id_user', $user->id)
                    ->whereDate('tanggal', $tanggalAbsen)
                    ->exists();

                if (!$sudahAbsen) {
                    Absensi::create([
                        'id_user' => $user->id,
                        'tanggal' => $tanggalAbsen,
                        'status' => $request->status,
                        'check_in' => Carbon::now(),
                        'check_out' => Carbon::now(),
                        'latitude_in' => $request->latitude,
                        'longitude_in' => $request->longitude,
                        'latitude_out' => $request->latitude,
                        'longitude_out' => $request->longitude,
                        'keterangan' => $request->keterangan,
                        'file_pendukung' => $filePath,
                    ]);

                    $jumlahDisimpan++;
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'notifikasi' => $jumlahDisimpan > 0
                    ? 'Berhasil menambahkan absen ' . $request->status . ' selama ' . $jumlahDisimpan . ' hari.'
                    : 'Semua tanggal sudah tercatat. Tidak ada data baru yang ditambahkan.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with([
                'notifikasi' => 'Gagal menambahkan absen: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function checkInProcess(Request $request){
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ],[
            'latitude.required' => 'latitude harus diisi',
            'longitude.required' => 'longitude harus diisi',
        ]);

        if(!$validatedData){
            return redirect()->back()->with([
                'notifikasi' => 'latitude atau longitude harus diisi',
                'type' => 'warning'
            ]);
        }


        $user = Auth::user();
        $today = Carbon::now()->toDateString();

        // Cek apakah sudah check-in hari ini
        $sudahCheckIn = Absensi::where('id_user', $user->id)
            ->whereDate('tanggal', $today)
            ->whereNotNull('check_in')
            ->exists();

        if ($sudahCheckIn) {
            return redirect()->back()->with([
                'notifikasi' => 'Anda sudah melakukan check-in hari ini.',
                'type' => 'warning'
            ]);
        }


        if (!$user->profilePekerjaan || !$user->profilePekerjaan->tempatKerja) {
            return redirect()->back()->with([
                'notifikasi' => 'Lokasi kantor tidak tersedia. Silakan hubungi HR.',
                'type' => 'error'
            ]);
        }

        // lokasi kantor
        $officeLat = $user->profilePekerjaan->tempatKerja->latitude;
        $officeLon = $user->profilePekerjaan->tempatKerja->longitude;

        //lokasi user
        $userLat = $request->latitude;
        $userLon = $request->longitude;

        // Hitung jarak menggunakan Haversine Formula
        $distance = $this->calculateDistance($officeLat, $officeLon, $userLat, $userLon);

        //jarak dalam meter
        $jarak = round($distance);

        //lebih dari 500 meter
        if ($distance > 500) {
            return redirect()->back()->with([
                'notifikasi' => 'Anda berada di luar radius '. $jarak .' meter dari kantor.',
                'type' => 'error'
            ]);
        }

        // Tentukan hari sekarang
        $hariSekarang = strtolower(Carbon::now()->locale('id')->dayName); // contoh: "senin"
        $jabatanId = $user->profilePekerjaan->id_jabatan ?? null;

        $jamKerja = JamKerja::where('id_jabatan', $jabatanId)
            ->where('hari', $hariSekarang)
            ->first();

        if (!$jamKerja || $jamKerja->is_libur) {
            return redirect()->back()->with([
                'notifikasi' => 'Hari ini adalah hari libur atau jam kerja belum diatur.',
                'type' => 'warning'
            ]);
        }

        $jamMasuk = Carbon::createFromFormat('H:i:s', $jamKerja->jam_masuk);
        $waktuSekarang = Carbon::now();

        // Hitung selisih waktu dalam menit
        $selisihMenit = $jamMasuk->diffInMinutes($waktuSekarang, false);

        // Tentukan status
        $status = $selisihMenit > 15 ? 'terlambat' : 'hadir';

        // Simpan absensi (check-in)
        $save = Absensi::create([
            'id_user' => $user->id,
            'tanggal' => $today,
            'check_in' => $waktuSekarang,
            'latitude_in' => $userLat,
            'longitude_in' => $userLon,
            'status' => $status,
        ]);

        if($save){
            return redirect()->back()->with([
                'notifikasi' => 'Check-in berhasil!',
                'type' => 'success'
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Check-in gagal!',
                'type' => 'error'
            ]);
        }
    }

    //fungsi perhitungan radius
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
         $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance; // hasil dalam meter
    }

    public function checkOutProcess(Request $request)
    {
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ], [
            'latitude.required' => 'Latitude wajib diisi.',
            'longitude.required' => 'Longitude wajib diisi.',
        ]);

        if(!$validatedData){
            return redirect()->back()->with([
                'notifikasi' => 'latitude atau longitude harus diisi',
                'type' => 'warning'
            ]);
        }

        $user = Auth::user();
        $today = Carbon::now()->toDateString();

        // Cek apakah sudah check-in hari ini
        $absen = Absensi::where('id_user', $user->id)
            ->whereDate('tanggal', $today)
            ->whereNotNull('check_in')
            ->first();

        if (!$absen) {
            return redirect()->back()->with([
                'notifikasi' => 'Anda belum melakukan check-in hari ini.',
                'type' => 'warning'
            ]);
        }

        // Cek apakah sudah check-out
        if ($absen->check_out !== null) {
            return redirect()->back()->with([
                'notifikasi' => 'Anda sudah melakukan check-out hari ini.',
                'type' => 'info'
            ]);
        }

        // Simpan check-out
        $update = $absen->update([
            'check_out' => Carbon::now(),
            'latitude_out' => $request->latitude,
            'longitude_out' => $request->longitude,
        ]);

        if($update){
            return redirect()->back()->with([
                'notifikasi' => 'Check-out berhasil!',
                'type' => 'success'
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Check-out gagal!',
                'type' => 'error'
            ]);

        }
    }

    public function showRekapPegawaiPage($id_pegawai)
    {
        $absensi = Absensi::withTrashed()->where('id_user',$id_pegawai)->orderByDesc('tanggal')->get();
        $user = User::where('id',$id_pegawai)->firstOrFail();

        // Group by status dan hitung jumlah masing-masing
        $statusCounts = $absensi->groupBy('status')->map(function ($item) {
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
        $orderedStatusCounts = $orderedStatuses->merge($statusCounts);

        return view('admin.rekap-absen-pegawai',[
            'dataAbsensi' => $absensi,
            'dataUser' => $user,
            'statusCounts' => $orderedStatusCounts,
        ]);
    }

    public function rekapPegawaiStore(Request $request, $id_pegawai){
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'tanggal' => 'required|date',
            'durasi_hari' => 'required|integer|min:0',
            'status' => 'required',
            'file_pendukung' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            // Latitude & Longitude
            'latitude.required' => 'Lokasi latitude wajib dikirim.',
            'longitude.required' => 'Lokasi longitude wajib dikirim.',

            // Tanggal
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',

            // Durasi
            'durasi_hari.required' => 'Durasi hari wajib diisi.',
            'durasi_hari.integer' => 'Durasi hari harus berupa angka.',
            'durasi_hari.min' => 'Durasi hari minimal 0 (hari ini saja).',

            // Status
            'status.required' => 'Status wajib dipilih.',

            // File
            'file_pendukung.required' => 'File pendukung wajib diisi.',
            'file_pendukung.file' => 'File pendukung harus berupa file.',
            'file_pendukung.mimes' => 'File hanya boleh berformat: jpg, jpeg, png, atau pdf.',
            'file_pendukung.max' => 'Ukuran file maksimal 2MB.',

            // Keterangan
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('file_pendukung')) {
                $filePath = $request->file('file_pendukung')->store('file_pendukung', 'public');
            }

            $tanggalAwal = Carbon::parse($request->tanggal);
            $durasi = $request->durasi_hari;
            $jumlahDisimpan = 0;

            for ($i = 0; $i <= $durasi; $i++) {
                $tanggalAbsen = $tanggalAwal->copy()->addDays($i);

                $sudahAbsen = Absensi::where('id_user', $id_pegawai)
                    ->whereDate('tanggal', $tanggalAbsen)
                    ->exists();

                if (!$sudahAbsen) {
                    Absensi::create([
                        'id_user' => $id_pegawai,
                        'tanggal' => $tanggalAbsen,
                        'status' => $request->status,
                        'check_in' => Carbon::now(),
                        'check_out' => Carbon::now(),
                        'latitude_in' => $request->latitude,
                        'longitude_in' => $request->longitude,
                        'latitude_out' => $request->latitude,
                        'longitude_out' => $request->longitude,
                        'keterangan' => $request->keterangan,
                        'file_pendukung' => $filePath,
                    ]);

                    $jumlahDisimpan++;
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'notifikasi' => $jumlahDisimpan > 0
                    ? 'Berhasil menambahkan absen ' . $request->status . ' selama ' . $jumlahDisimpan . ' hari.'
                    : 'Semua tanggal sudah tercatat. Tidak ada data baru yang ditambahkan.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with([
                'notifikasi' => 'Gagal menambahkan absen: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function rekapUpdate(Request $request,$id_absensi){
        $validatedData = $request->validate([
        'check_in' => 'nullable|date',
        'check_out' => 'nullable|date|after_or_equal:check_in',
        'latitude_in' => 'nullable|numeric',
        'longitude_in' => 'nullable|numeric',
        'latitude_out' => 'nullable|numeric',
        'longitude_out' => 'nullable|numeric',
        'status' => 'required|in:hadir,sakit,cuti,terlambat,alpa',
        'file_pendukung' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'keterangan' => 'nullable|string|max:1000',
        ], [
        'check_in.date' => 'Format waktu check-in tidak valid.',
        'check_out.date' => 'Format waktu check-out tidak valid.',
        'check_out.after_or_equal' => 'Waktu check-out tidak boleh lebih awal dari check-in.',
        'latitude_in.numeric' => 'Latitude masuk harus berupa angka.',
        'longitude_in.numeric' => 'Longitude masuk harus berupa angka.',
        'latitude_out.numeric' => 'Latitude keluar harus berupa angka.',
        'longitude_out.numeric' => 'Longitude keluar harus berupa angka.',
        'status.required' => 'Status absen wajib dipilih.',
        'status.in' => 'Status absen tidak valid.',
        'file_pendukung.file' => 'File pendukung harus berupa file.',
        'file_pendukung.mimes' => 'Format file harus jpg, jpeg, png, atau pdf.',
        'file_pendukung.max' => 'Ukuran file maksimal 2MB.',
        'keterangan.string' => 'Keterangan harus berupa teks.',
        'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
    ]);

        $absensi = Absensi::findOrFail($id_absensi);

        if ($request->hasFile('file_pendukung')) {
            $old_file= $absensi->file_pendukung ?? null;
            if (!empty($old_file) && is_file('storage/'.$old_file)) {
                unlink('storage/'.$old_file);
            }

            $filePath = $request->file('file_pendukung')->store('file_pendukung', 'public');
        }else{
            $filePath = $absensi->file_pendukung;
        }

        $save = $absensi->update([
            'status' => $request->status,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'latitude_in' => $request->latitude_in ?? null,
            'longitude_in' => $request->longitude_in ?? null,
            'latitude_out' => $request->latitude_out ?? null,
            'longitude_out' => $request->longitude_out ?? null,
            'keterangan' => $request->keterangan,
            'file_pendukung' => $filePath,
        ]);

        if ($save) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil memperbarui data absen.',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal memperbarui absen.',
                'type' => 'error',
            ]);
        }

    }

    public function rekapDestroy($id_absensi){
        $absensi = Absensi::findOrFail($id_absensi);

        if (!$absensi) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($absensi->delete()) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menghapus data!',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menghapus data!',
                'type' => 'error',
            ]);
        }
    }

    public function rekapRestore($id_absensi){
        $absensi = Absensi::withTrashed()->findOrFail($id_absensi);

        if (!$absensi) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($absensi->restore()) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil memulihkan data!',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal memulihkan data!',
                'type' => 'error',
            ]);
        }
    }

}
