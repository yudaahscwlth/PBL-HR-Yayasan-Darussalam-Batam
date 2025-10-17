<?php

namespace App\Http\Controllers;

use App\Models\evaluasi;
use App\Models\KategoriEvaluasi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluasiController extends Controller
{
    public function showEvaluasiPage(){
        return view('admin.evaluasi-pegawai',[

        ]);
    }

    public function showRekapPribadiPage(){
        return view('general.rekap-evaluasi-pribadi',[

        ]);
    }

    public function showRekapPegawaiPage($id_pegawai){
        $user = User::where('id',$id_pegawai)->firstOrFail();
        return view('admin.rekap-evaluasi-pegawai',[
            'dataUser' => $user,
        ]);
    }

    public function storeEvaluasi(Request $request){
        $validatedData = $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'nilai' => 'required|array',
            'nilai.*' => 'required|numeric|min:1|max:5',
            'catatan' => 'nullable|string|max:1000',
        ], [
            // id_user
            'id_user.required' => 'Nama pendidik wajib dipilih.',
            'id_user.exists' => 'Pendidik tidak valid.',

            // id_tahun_ajaran
            'id_tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'id_tahun_ajaran.exists' => 'Tahun ajaran tidak valid.',

            // nilai
            'nilai.required' => 'Penilaian tidak boleh kosong.',
            'nilai.array' => 'Format nilai tidak valid.',
            'nilai.*.required' => 'Semua indikator penilaian harus diisi.',
            'nilai.*.numeric' => 'Nilai harus berupa angka.',
            'nilai.*.min' => 'Nilai minimal adalah 1.',
            'nilai.*.max' => 'Nilai maksimal adalah 5.',

            // catatan
            'catatan.string' => 'Catatan harus berupa teks.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validatedData['nilai'] as $idKategori => $nilai) {
                Evaluasi::create([
                    'id_user' => $validatedData['id_user'],
                    'id_penilai' => Auth::user()->id,
                    'id_kategori' => $idKategori,
                    'id_tahun_ajaran' => $validatedData['id_tahun_ajaran'],
                    'nilai' => $nilai,
                    'catatan' => $validatedData['catatan'], // bisa sama untuk semua atau disesuaikan
                ]);
            }

            DB::commit();

            return redirect()->back()->with([
                'notifikasi' => 'Evaluasi berhasil disimpan.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with([
                'notifikasi' => 'Terjadi kesalahan saat menyimpan evaluasi.',
                'type' => 'error'
            ]);
        }
    }

    public function updateEvaluasi(Request $request, $id_pegawai, $id_tahun_ajaran)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*' => 'required|in:1,2,3,4,5',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'nilai.required' => 'Semua nilai wajib diisi.',
            'nilai.*.in' => 'Nilai harus antara 1 sampai 5.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        try{
            DB::beginTransaction();

            $existingEvaluasi = Evaluasi::where('id_user', $id_pegawai)
                ->where('id_tahun_ajaran', $id_tahun_ajaran)
                ->get()
                ->keyBy('id_kategori'); // mengubah koleksi menjadi array assosiatif cth : 1 (ini merupakan id_kategori) => {id: 1, id_user : 1, id_tahun_ajaran : 1}

            // dd($existingEvaluasi);

            foreach ($validated['nilai'] as $idKategori => $nilaiBaru) {
                if ($existingEvaluasi->has($idKategori)) {
                    $evaluasi = $existingEvaluasi[$idKategori];
                    $evaluasi->update([
                        'id_penilai' => Auth::user()->id,
                        'nilai' => $nilaiBaru,
                        'catatan' => $validated['catatan'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'notifikasi' => 'Berhasil memperbarui data evaluasi.',
                'type' => 'success',
            ]);
        }catch(\Exception $e){
            DB::rollBack();

            return redirect()->back()->with([
                'notifikasi' => 'Terjadi kesalahan saat memperbarui evaluasi.',
                'type' => 'error'
            ]);
        }
    }
}
