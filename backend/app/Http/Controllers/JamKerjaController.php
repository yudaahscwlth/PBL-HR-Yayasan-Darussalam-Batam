<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\JamKerja;
use Illuminate\Http\Request;

class JamKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id_jabatan)
    {
        $jamKerja = JamKerja::where('id_jabatan',$id_jabatan)->latest()->get();

        return view('admin.kelola-jam-kerja',[
            'dataJamKerja' => $jamKerja,
            'id_jabatan' => $id_jabatan,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,$id_jabatan)
    {
        $validated = $request->validate([
            'jam_masuk'   => 'required',
            'jam_pulang'  => 'required|after:jam_masuk',
            'is_libur'    => 'required|in:0,1',
            'keterangan'  => 'nullable|string|max:255',
            'hari'        => 'required|array|min:1',
            'hari.*'      => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
        ], [
            'hari.required'     => 'Pilih setidaknya satu hari.',
            'hari.*.in'         => 'Hari yang dipilih tidak valid.',
            'jam_masuk.required'=> 'Jam masuk wajib diisi.',
            'jam_pulang.required'=> 'Jam pulang wajib diisi.',
            'jam_pulang.after'  => 'Jam pulang harus setelah jam masuk.',
            'is_libur.required' => 'Status libur wajib dipilih.',
        ]);

        try {
            $duplikat = [];

            foreach ($request->hari as $hari) {
                $sudahAda = JamKerja::where('id_jabatan', $request->id_jabatan)
                    ->where('hari', $hari)
                    ->exists();

                if ($sudahAda) {
                    $duplikat[] = ucfirst($hari); // Simpan nama hari yang duplikat
                    continue;
                }

                $isSunday = $hari === 'minggu';
                $isLibur = $isSunday ? true : (bool) $request->is_libur;

                JamKerja::create([
                    'id_jabatan' => $request->id_jabatan,
                    'hari'       => $hari,
                    'jam_masuk'  => $isLibur ? null : $request->jam_masuk,
                    'jam_pulang' => $isLibur ? null : $request->jam_pulang,
                    'is_libur'   => $isLibur,
                    'keterangan' => $isLibur
                        ? ($request->keterangan ?? 'Libur')
                        : ($request->keterangan ?? null),
                ]);
            }

            if (count($duplikat)) {
                return redirect()->back()->with([
                    'notifikasi' => 'Sebagian data berhasil disimpan, tapi hari berikut sudah ada: ' . implode(', ', $duplikat),
                    'type'       => 'warning',
                ]);
            }

            return redirect()->back()->with([
                'notifikasi' => 'Jam kerja berhasil ditambahkan.',
                'type'       => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menambahkan jam kerja. Silakan coba lagi.',
                'type'       => 'error',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JamKerja $jamKerja)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JamKerja $jamKerja)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_jabatan,$id_jam_kerja)
    {
        $validated = $request->validate([
        'jam_masuk'   => 'required',
        'jam_pulang'  => 'required|after:jam_masuk',
        'is_libur'    => 'required|in:0,1',
        'keterangan'  => 'nullable|string|max:255',
        ], [
            'jam_masuk.required'  => 'Jam masuk wajib diisi.',
            'jam_pulang.required' => 'Jam pulang wajib diisi.',
            'jam_pulang.after'    => 'Jam pulang harus setelah jam masuk.',
            'is_libur.required'   => 'Status libur wajib dipilih.',
        ]);

        try {
            $jamKerja = JamKerja::where('id_jabatan',$id_jabatan)->where('id',$id_jam_kerja)->firstOrFail();

            $isSunday = $jamKerja->hari === 'minggu';
            $isLibur = $isSunday ? true : (bool) $request->is_libur;

            $jamKerja->update([
                'jam_masuk'  => $isLibur ? null : $request->jam_masuk,
                'jam_pulang' => $isLibur ? null : $request->jam_pulang,
                'is_libur'   => $isLibur,
                'keterangan' => $isLibur
                    ? ($request->keterangan ?? 'Libur')
                    : ($request->keterangan ?? null),
            ]);

            return redirect()->back()->with([
                'notifikasi' => 'Jam kerja berhasil diperbarui.',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal memperbarui jam kerja. Silakan coba lagi.',
                'type' => 'error',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_jabatan,$id_jam_kerja)
    {
        $jamKerja = JamKerja::where('id_jabatan',$id_jabatan)->where('id',$id_jam_kerja)->firstOrFail();

        if (!$jamKerja) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($jamKerja->delete()) {
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
