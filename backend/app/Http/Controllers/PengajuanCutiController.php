<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanCutiController extends Controller
{
    public function showPengajuanCutiTendikPage(){
        $pengajuanCutiSedangDiproses = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'ditinjau kepala sekolah',
                'disetujui kepala sekolah menunggu tinjauan dirpen',
            ])->latest()->get();

        $pengajuanCutiSelesai = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'disetujui kepala sekolah',
                'ditolak kepala sekolah',
                'disetujui dirpen',
                'ditolak dirpen',
            ])->orderBy('updated_at', 'desc')->get();

        return view('pegawai.pengajuan-cuti-tendik', [
            'dataSedangDiproses' => $pengajuanCutiSedangDiproses,
            'dataSelesai' => $pengajuanCutiSelesai
        ]);
    }

    public function storePengajuanTendik(Request $request){
        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_cuti' => 'required|in:cuti tahunan,cuti melahirkan,cuti nikah,cuti kematian,cuti bersama,cuti pemotongan gaji,cuti lainnya',
            'file_pendukung' => 'nullable|file|max:2048',
            'alasan_pendukung' => 'nullable|string|max:1000',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'tipe_cuti.required' => 'Tipe cuti wajib dipilih.',
            'tipe_cuti.in' => 'Tipe cuti yang dipilih tidak valid.',

            'file_pendukung.file' => 'File pendukung harus berupa file yang valid.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',

            'alasan_pendukung.string' => 'Alasan pendukung harus berupa teks.',
            'alasan_pendukung.max' => 'Alasan pendukung maksimal 1000 karakter.',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $file_pendukung = $request->file('file_pendukung')->store('file_pendukung','public');
        }else{
            $file_pendukung = null;
        }

        $pengajuanCuti = PengajuanCuti::create([
            'id_user' => Auth::user()->id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => $request->tipe_cuti,
            'status_pengajuan' => 'ditinjau kepala sekolah',
            'file_pendukung' => $file_pendukung,
            'alasan_pendukung' => $request->alasan_pendukung,
        ]);

        if($pengajuanCuti){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengajukan cuti',
                'type' => 'success',
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengajukan cuti',
                'type' => 'error',
            ]);
        }

    }

    public function showPengajuanCutiKepsekPage(){
        $pengajuanCutiSedangDiproses = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'ditinjau hrd',
                'disetujui hrd menunggu tinjauan dirpen',
            ])->latest()->get();

        $pengajuanCutiSelesai = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'disetujui hrd',
                'ditolak hrd',
                'disetujui dirpen',
                'ditolak dirpen',
            ])->orderBy('updated_at', 'desc')->get();

        return view('pegawai.pengajuan-cuti-kepsek', [
            'dataSedangDiproses' => $pengajuanCutiSedangDiproses,
            'dataSelesai' => $pengajuanCutiSelesai
        ]);
    }

    public function storePengajuanKepsek(Request $request){
        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_cuti' => 'required|in:cuti tahunan,cuti melahirkan,cuti nikah,cuti kematian,cuti bersama,cuti pemotongan gaji,cuti lainnya',
            'file_pendukung' => 'nullable|file|max:2048',
            'alasan_pendukung' => 'nullable|string|max:1000',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'tipe_cuti.required' => 'Tipe cuti wajib dipilih.',
            'tipe_cuti.in' => 'Tipe cuti yang dipilih tidak valid.',

            'file_pendukung.file' => 'File pendukung harus berupa file yang valid.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',

            'alasan_pendukung.string' => 'Alasan pendukung harus berupa teks.',
            'alasan_pendukung.max' => 'Alasan pendukung maksimal 1000 karakter.',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $file_pendukung = $request->file('file_pendukung')->store('file_pendukung','public');
        }else{
            $file_pendukung = null;
        }

        $pengajuanCuti = PengajuanCuti::create([
            'id_user' => Auth::user()->id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => $request->tipe_cuti,
            'status_pengajuan' => 'ditinjau hrd',
            'file_pendukung' => $file_pendukung,
            'alasan_pendukung' => $request->alasan_pendukung,
        ]);

        if($pengajuanCuti){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengajukan cuti',
                'type' => 'success',
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengajukan cuti',
                'type' => 'error',
            ]);
        }

    }

    public function showPengajuanCutiStaffHrdPage(){
        $pengajuanCutiSedangDiproses = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'ditinjau kepala hrd',
                'disetujui kepala hrd menunggu tinjauan dirpen',
            ])->latest()->get();

        $pengajuanCutiSelesai = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'disetujui kepala hrd',
                'ditolak kepala hrd',
                'disetujui dirpen',
                'ditolak dirpen',
            ])->orderBy('updated_at', 'desc')->get();

        return view('pegawai.pengajuan-cuti-staff-hrd', [
            'dataSedangDiproses' => $pengajuanCutiSedangDiproses,
            'dataSelesai' => $pengajuanCutiSelesai
        ]);
    }

    public function storePengajuanStaffHrd(Request $request){
        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_cuti' => 'required|in:cuti tahunan,cuti melahirkan,cuti nikah,cuti kematian,cuti bersama,cuti pemotongan gaji,cuti lainnya',
            'file_pendukung' => 'nullable|file|max:2048',
            'alasan_pendukung' => 'nullable|string|max:1000',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'tipe_cuti.required' => 'Tipe cuti wajib dipilih.',
            'tipe_cuti.in' => 'Tipe cuti yang dipilih tidak valid.',

            'file_pendukung.file' => 'File pendukung harus berupa file yang valid.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',

            'alasan_pendukung.string' => 'Alasan pendukung harus berupa teks.',
            'alasan_pendukung.max' => 'Alasan pendukung maksimal 1000 karakter.',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $file_pendukung = $request->file('file_pendukung')->store('file_pendukung','public');
        }else{
            $file_pendukung = null;
        }

        $pengajuanCuti = PengajuanCuti::create([
            'id_user' => Auth::user()->id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => $request->tipe_cuti,
            'status_pengajuan' => 'ditinjau kepala hrd',
            'file_pendukung' => $file_pendukung,
            'alasan_pendukung' => $request->alasan_pendukung,
        ]);

        if($pengajuanCuti){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengajukan cuti',
                'type' => 'success',
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengajukan cuti',
                'type' => 'error',
            ]);
        }

    }

    public function showPengajuanCutiKepalaHrdPage(){
        $pengajuanCutiSedangDiproses = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'ditinjau dirpen',
            ])->latest()->get();

        $pengajuanCutiSelesai = PengajuanCuti::where('id_user', Auth::user()->id)
            ->whereIn('status_pengajuan', [
                'disetujui dirpen',
                'ditolak dirpen',
            ])->orderBy('updated_at', 'desc')->get();

        return view('pegawai.pengajuan-cuti-kepala-hrd', [
            'dataSedangDiproses' => $pengajuanCutiSedangDiproses,
            'dataSelesai' => $pengajuanCutiSelesai
        ]);
    }

    public function storePengajuanKepalaHrd(Request $request){
        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_cuti' => 'required|in:cuti tahunan,cuti melahirkan,cuti nikah,cuti kematian,cuti bersama,cuti pemotongan gaji,cuti lainnya',
            'file_pendukung' => 'nullable|file|max:2048',
            'alasan_pendukung' => 'nullable|string|max:1000',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'tipe_cuti.required' => 'Tipe cuti wajib dipilih.',
            'tipe_cuti.in' => 'Tipe cuti yang dipilih tidak valid.',

            'file_pendukung.file' => 'File pendukung harus berupa file yang valid.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',

            'alasan_pendukung.string' => 'Alasan pendukung harus berupa teks.',
            'alasan_pendukung.max' => 'Alasan pendukung maksimal 1000 karakter.',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $file_pendukung = $request->file('file_pendukung')->store('file_pendukung','public');
        }else{
            $file_pendukung = null;
        }

        $pengajuanCuti = PengajuanCuti::create([
            'id_user' => Auth::user()->id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => $request->tipe_cuti,
            'status_pengajuan' => 'ditinjau dirpen',
            'file_pendukung' => $file_pendukung,
            'alasan_pendukung' => $request->alasan_pendukung,
        ]);

        if($pengajuanCuti){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengajukan cuti',
                'type' => 'success',
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengajukan cuti',
                'type' => 'error',
            ]);
        }

    }

    public function storeRekapCuti(Request $request,$id_pegawai){
        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_cuti' => 'required|in:cuti tahunan,cuti melahirkan,cuti nikah,cuti kematian,cuti bersama,cuti pemotongan gaji,cuti lainnya',
            'file_pendukung' => 'nullable|file|max:2048',
            'alasan_pendukung' => 'nullable|string|max:1000',
            'status_pengajuan' => 'required',
            'komentar' => 'nullable|string|max:1000',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'tipe_cuti.required' => 'Tipe cuti wajib dipilih.',
            'tipe_cuti.in' => 'Tipe cuti yang dipilih tidak valid.',

            'file_pendukung.file' => 'File pendukung harus berupa file yang valid.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',

            'alasan_pendukung.string' => 'Alasan pendukung harus berupa teks.',
            'alasan_pendukung.max' => 'Alasan pendukung maksimal 1000 karakter.',

            'status_pengajuan.required' => 'Status pengajuan wajib diisi.',

            'komentar.string' => 'Komentar harus berupa teks.',
            'komentar.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $file_pendukung = $request->file('file_pendukung')->store('file_pendukung','public');
        }else{
            $file_pendukung = null;
        }

        $pengajuanCuti = PengajuanCuti::create([
            'id_user' => $id_pegawai,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => $request->tipe_cuti,
            'status_pengajuan' => $request->status_pengajuan,
            'file_pendukung' => $file_pendukung,
            'alasan_pendukung' => $request->alasan_pendukung,
            'komentar' => $request->komentar,
        ]);

        if($pengajuanCuti){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengajukan cuti',
                'type' => 'success',
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengajukan cuti',
                'type' => 'error',
            ]);
        }

    }

    public function updateRekapCuti(Request $request,$id_pengajuan){
        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_cuti' => 'required|in:cuti tahunan,cuti melahirkan,cuti nikah,cuti kematian,cuti bersama,cuti pemotongan gaji,cuti lainnya',
            'file_pendukung' => 'nullable|file|max:2048',
            'alasan_pendukung' => 'nullable|string|max:1000',
            'status_pengajuan' => 'required',
            'komentar' => 'nullable|string|max:1000',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'tipe_cuti.required' => 'Tipe cuti wajib dipilih.',
            'tipe_cuti.in' => 'Tipe cuti yang dipilih tidak valid.',

            'file_pendukung.file' => 'File pendukung harus berupa file yang valid.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',

            'alasan_pendukung.string' => 'Alasan pendukung harus berupa teks.',
            'alasan_pendukung.max' => 'Alasan pendukung maksimal 1000 karakter.',

            'status_pengajuan.required' => 'Status pengajuan wajib diisi.',

            'komentar.string' => 'Komentar harus berupa teks.',
            'komentar.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        // Ambil data pengajuan
        $pengajuan = PengajuanCuti::findOrFail($id_pengajuan);

        if ($request->hasFile('file_pendukung')) {
            $old_file = $pengajuan->file_pendukung ?? null;
            if (!empty($old_file) && is_file('storage/'.$old_file)) {
                unlink('storage/'.$old_file);
            }
            $file_pendukung = $request->file('file_pendukung')->store('file_pendukung','public');
        }else{
            $file_pendukung = $pengajuan->file_pendukung;
        }

        $pengajuanCuti = $pengajuan->update([
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => $request->tipe_cuti,
            'status_pengajuan' => $request->status_pengajuan,
            'file_pendukung' => $file_pendukung,
            'alasan_pendukung' => $request->alasan_pendukung,
            'komentar' => $request->komentar,
        ]);

        if($pengajuanCuti){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengubah rekap cuti',
                'type' => 'success',
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Gagal mengubah cuti',
                'type' => 'error',
            ]);
        }

    }

    public function destroyRekapCuti($id_pengajuan)
    {
        $pengajuan = PengajuanCuti::findOrFail($id_pengajuan);

        if (!$pengajuan) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($pengajuan->delete()) {
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
}
