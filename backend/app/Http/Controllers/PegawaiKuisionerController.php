<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Kuisioner;
use App\Models\JawabanKuisioner;
use App\Models\PeriodePenilaian;
use App\Models\Penilaian;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PegawaiKuisionerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->pegawai) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

        $periode = PeriodePenilaian::where('status', 'aktif')
            ->orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        return view('karyawan.kuisioner.index', compact(
            'pegawai',
            'nama_departemen',
            'periode'
        ));
    }

    public function getAllPegawai(Request $request)
{
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak terautentikasi.'
        ], 401);
    }

    $currentUser = Auth::user();
    if (!$currentUser->pegawai) {
        return response()->json([
            'success' => false,
            'message' => 'Data pegawai tidak ditemukan.'
        ], 404);
    }

    $currentPegawai = $currentUser->pegawai;
    $periodeId = $request->input('periode_id'); // Ambil periode_id dari request

    $allPegawai = Pegawai::with(['departemen', 'user'])
        ->where('id_pegawai', '!=', $currentPegawai->id_pegawai)
        ->orderBy('nama')
        ->get();

    if ($allPegawai->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada pegawai lain yang ditemukan.',
            'data' => []
        ]);
    }

    $data = $allPegawai->map(function ($pegawai) use ($currentPegawai, $periodeId) {
        // Cek status penilaian jika periode_id tersedia
        $statusPenilaian = 'belum_diisi';
        if ($periodeId) {
            $penilaian = Penilaian::where([
                'periode_id' => $periodeId,
                'dinilai_pegawai_id' => $pegawai->id_pegawai,
                'penilai_pegawai_id' => $currentPegawai->id_pegawai,
            ])->first();
            
            if ($penilaian) {
                $statusPenilaian = $penilaian->status;
            }
        }

        return [
            'id_pegawai' => $pegawai->id_pegawai,
            'nama' => $pegawai->nama,
            'jabatan' => $pegawai->jabatan->nama_jabatan,
            'no_hp' => $pegawai->no_hp ?? 'N/A',
            'departemen' => $pegawai->departemen->nama_departemen ?? 'N/A',
            'id_user' => $pegawai->user->id_user ?? $pegawai->user->id ?? null,
            'status_penilaian' => $statusPenilaian
        ];
    });

    return response()->json([
        'success' => true,
        'count' => $data->count(),
        'data' => $data
    ]);
}

    public function reset($periodeId, $dinilaiPegawaiId)
    {
        $currentUser = Auth::user();
        $penilaiPegawai = $currentUser->pegawai;

        if (!$penilaiPegawai) {
            return redirect()->back()->with('error', 'Data pegawai penilai tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $penilaian = Penilaian::where([
                'periode_id' => $periodeId,
                'dinilai_pegawai_id' => $dinilaiPegawaiId,
                'penilai_pegawai_id' => $penilaiPegawai->id_pegawai,
            ])->first();

            if ($penilaian) {
                JawabanKuisioner::where('penilaian_id', $penilaian->id)->delete();
                $penilaian->update([
                    'total_nilai' => 0,
                    'status' => 'belum_diisi',
                    'tanggal_penilaian' => null
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Semua jawaban berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus jawaban.');
        }
    }

   public function show($periodeId, $dinilaiPegawaiId)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->pegawai) {
            return redirect()->route('kuisioner.index')->with('error', 'Silakan login terlebih dahulu.');
        }

        $pegawai = $currentUser->pegawai;
        $penilaiPegawai = $currentUser->pegawai;
        $periode = PeriodePenilaian::find($periodeId);

        if (!$periode || $periode->status !== 'aktif') {
            return redirect()->route('kuisioner.index')->with('error', 'Periode tidak aktif atau tidak ditemukan.');
        }

        $dinilaiPegawai = Pegawai::find($dinilaiPegawaiId);
        if (!$dinilaiPegawai || $dinilaiPegawaiId == $penilaiPegawai->id_pegawai) {
            return redirect()->route('kuisioner.index')->with('error', 'Pegawai tidak valid.');
        }

        // PERBAIKAN: Gunakan relasi many-to-many yang sudah dibuat
        $kuisionerData = $periode->kuisioner()
            ->aktif() // menggunakan scope aktif
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        // Alternative jika ingin filter berdasarkan departemen dan golongan pegawai
        /*
        $kuisionerData = $periode->kuisioner()
            ->aktif()
            ->departemen($dinilaiPegawai->departemen_id)
            ->golongan($dinilaiPegawai->golongan)
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();
        */

        if ($kuisionerData->isEmpty()) {
            return redirect()->route('kuisioner.index')->with('error', 'Tidak ada kuisioner aktif untuk periode ini.');
        }

        $kuisionerByKategori = $kuisionerData->groupBy('kategori');

        $penilaian = Penilaian::firstOrCreate([
            'periode_id' => $periodeId,
            'dinilai_pegawai_id' => $dinilaiPegawai->id_pegawai,
            'penilai_pegawai_id' => $penilaiPegawai->id_pegawai,
        ], [
            'status' => 'belum_diisi',
            'total_nilai' => 0,
        ]);

        $existingAnswers = JawabanKuisioner::where('penilaian_id', $penilaian->id)
            ->pluck('skor', 'kuisioner_id')
            ->toArray();
        
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

        return view('karyawan.kuisioner.form', [
            'pegawai' => $pegawai,
            'nama_departemen' => $nama_departemen,
            'periode' => $periode,
            'kuisionerByKategori' => $kuisionerByKategori,
            'existingAnswers' => $existingAnswers,
            'dinilai' => (object)[
                'id_pegawai' => $dinilaiPegawai->id_pegawai,
                'nama_pegawai' => $dinilaiPegawai->nama
            ],
            'penilai' => (object)[
                'nama_pegawai' => $penilaiPegawai->nama,
                'foto' => $penilaiPegawai->foto
            ],
            'penilaian' => $penilaian
        ]);
    }
   public function store(Request $request, $periodeId, $dinilaiPegawaiId)
{
    // Debug input
    Log::info('Kuisioner Input:', $request->all());
    
    $request->validate([
        'jawaban' => 'required|array',
        'jawaban.*' => 'integer|min:1|max:5'
    ]);

    $currentUser = Auth::user();
    $penilaiPegawai = $currentUser->pegawai;

    if (!$penilaiPegawai) {
        return redirect()->back()->with('error', 'Data pegawai penilai tidak ditemukan.');
    }

    DB::beginTransaction();
    try {
        $penilaian = Penilaian::firstOrCreate([
            'periode_id' => $periodeId,
            'dinilai_pegawai_id' => $dinilaiPegawaiId,
            'penilai_pegawai_id' => $penilaiPegawai->id_pegawai,
        ]);

        Log::info('Penilaian ID: ' . $penilaian->id);

        foreach ($request->input('jawaban') as $kuisionerId => $skor) {
            Log::info("Saving: Kuisioner ID $kuisionerId, Skor $skor");
            
            $jawaban = JawabanKuisioner::updateOrCreate([
                'penilaian_id' => $penilaian->id,
                'kuisioner_id' => $kuisionerId,
            ], [
                'skor' => $skor
            ]);
            
            Log::info('Jawaban saved with ID: ' . $jawaban->id);
        }

        $totalNilai = JawabanKuisioner::where('penilaian_id', $penilaian->id)->sum('skor');
        $penilaian->update([
            'total_nilai' => $totalNilai,
            'status' => 'selesai',
            'update_at' => now()
        ]);

        DB::commit();
        return redirect()->back()->with('success', 'Jawaban berhasil disimpan!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error saving kuisioner: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()->back()->with('error', 'Gagal menyimpan jawaban: ' . $e->getMessage());
    }
}
    public function result($periodeId, $dinilaiPegawaiId)
{
    $currentUser = Auth::user();
    $penilaiPegawai = $currentUser->pegawai;

    if (!$penilaiPegawai) {
        return redirect()->back()->with('error', 'Data pegawai penilai tidak ditemukan.');
    }

    // Get periode dan pegawai data
    $periode = PeriodePenilaian::find($periodeId);
    $dinilai = Pegawai::find($dinilaiPegawaiId);
    $pegawai = $penilaiPegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

    if (!$periode || !$dinilai) {
        return redirect()->route('kuisioner.index')->with('error', 'Data tidak ditemukan.');
    }

    // Get penilaian data
    $penilaian = Penilaian::where([
        'periode_id' => $periodeId,
        'dinilai_pegawai_id' => $dinilaiPegawaiId,
        'penilai_pegawai_id' => $penilaiPegawai->id_pegawai,
    ])->first();

    if (!$penilaian) {
        return redirect()->route('kuisioner.fill', [$periodeId, $dinilaiPegawaiId])
            ->with('error', 'Belum ada penilaian. Silakan isi kuisioner terlebih dahulu.');
    }

    // Get jawaban dengan detail kuisioner dan kategori
    $jawaban = JawabanKuisioner::with(['kuisioner'])
        ->where('penilaian_id', $penilaian->id)
        ->get();

    // Cek jika tidak ada jawaban
    if ($jawaban->count() == 0) {
        return redirect()->route('kuisioner.fill', [$periodeId, $dinilaiPegawaiId])
            ->with('error', 'Belum ada jawaban kuisioner. Silakan isi kuisioner terlebih dahulu.');
    }

    // Group jawaban by kategori
    $jawabanByKategori = $jawaban->groupBy(function($item) {
        return $item->kuisioner->kategori ?? 'Umum';
    });

    // Hitung statistik per kategori
    $statistikKategori = [];
    foreach ($jawabanByKategori as $kategori => $jawabanList) {
        $totalSkor = $jawabanList->sum('skor');
        $jumlahSoal = $jawabanList->count();
        $maxSkor = $jumlahSoal * 5; // Asumsi skala 1-5
        
        // Prevent division by zero
        $rata2 = $jumlahSoal > 0 ? $totalSkor / $jumlahSoal : 0;
        $persentase = $maxSkor > 0 ? ($totalSkor / $maxSkor) * 100 : 0;

        $statistikKategori[$kategori] = [
            'total_soal' => $jumlahSoal,
            'total_skor' => $totalSkor,
            'max_skor' => $maxSkor,
            'rata_rata' => round($rata2, 2),
            'persentase' => round($persentase, 1)
        ];
    }

    // Statistik keseluruhan
    $totalKeseluruhan = $jawaban->sum('skor');
    $jumlahJawaban = $jawaban->count();
    $maxKeseluruhan = $jumlahJawaban * 5;
    
    // Prevent division by zero
    $rataKeseluruhan = $jumlahJawaban > 0 ? $totalKeseluruhan / $jumlahJawaban : 0;
    $persentaseKeseluruhan = $maxKeseluruhan > 0 ? ($totalKeseluruhan / $maxKeseluruhan) * 100 : 0;

    // Prediksi grade
    $grade = $this->hitungGrade($persentaseKeseluruhan);

    return view('karyawan.kuisioner.result', compact(
        'periode', 'dinilai', 'pegawai', 'penilaian',
        'jawabanByKategori', 'statistikKategori',
        'totalKeseluruhan', 'maxKeseluruhan', 'rataKeseluruhan', 
        'persentaseKeseluruhan', 'grade','nama_departemen'
    ));
}

private function hitungGrade($persentase)
{
    if ($persentase >= 90) return ['grade' => 'A', 'label' => 'Sangat Baik', 'color' => 'success'];
    if ($persentase >= 80) return ['grade' => 'B', 'label' => 'Baik', 'color' => 'primary'];
    if ($persentase >= 70) return ['grade' => 'C', 'label' => 'Cukup', 'color' => 'warning'];
    if ($persentase >= 60) return ['grade' => 'D', 'label' => 'Kurang', 'color' => 'danger'];
    return ['grade' => 'E', 'label' => 'Tidak Baik', 'color' => 'dark'];
}
}
