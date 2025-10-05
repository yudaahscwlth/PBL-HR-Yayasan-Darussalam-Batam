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

class KepalaYayasanController extends Controller
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

        return view('kepala-yayasan.rekap.index', compact(
            'pegawai',
            'nama_departemen',
            'periode'
        ));
    }

    public function rekapPenilaian(Request $request, $periodeId)
    {
        $user = Auth::user();
        if (!$user || !$user->pegawai) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

        $periode = PeriodePenilaian::find($periodeId);
        if (!$periode) {
            return redirect()->route('kepala-yayasan.index')->with('error', 'Periode tidak ditemukan.');
        }

        // Ambil semua pegawai yang dinilai dalam periode ini
        $rekapPegawai = $this->getRekapPenilaianPegawai($periodeId);

        // Statistik keseluruhan
        $statistikUmum = $this->getStatistikUmum($periodeId);

        // Ranking pegawai berdasarkan rata-rata nilai
        $rankingPegawai = $this->getRankingPegawai($periodeId);

        // Statistik per departemen
        $statistikDepartemen = $this->getStatistikDepartemen($periodeId);

        return view('kepala-yayasan.rekap.detail', compact(
            'pegawai',
            'nama_departemen',
            'periode',
            'rekapPegawai',
            'statistikUmum',
            'rankingPegawai',
            'statistikDepartemen'
        ));
    }

    private function getRekapPenilaianPegawai($periodeId)
    {
        $pegawaiDinilai = Pegawai::with(['departemen', 'jabatan'])
            ->whereHas('penilaianDiterima', function($query) use ($periodeId) {
                $query->where('periode_id', $periodeId);
            })
            ->get();

        $rekapData = [];

        foreach ($pegawaiDinilai as $pegawai) {
            // Ambil semua penilaian untuk pegawai ini dalam periode tertentu
            $penilaianList = Penilaian::with(['penilai'])
                ->where('periode_id', $periodeId)
                ->where('dinilai_pegawai_id', $pegawai->id_pegawai)
                ->where('status', 'selesai')
                ->get();

            if ($penilaianList->isEmpty()) {
                continue;
            }

            // Hitung total nilai dari semua penilai
            $totalNilaiKeseluruhan = 0;
            $totalJumlahJawaban = 0;
            $jumlahPenilai = $penilaianList->count();
            $detailPenilai = [];

            foreach ($penilaianList as $penilaian) {
                $jawaban = JawabanKuisioner::where('penilaian_id', $penilaian->id)->get();
                $totalNilaiPenilai = $jawaban->sum('skor');
                $jumlahJawaban = $jawaban->count();
                
                $totalNilaiKeseluruhan += $totalNilaiPenilai;
                $totalJumlahJawaban += $jumlahJawaban;

                $detailPenilai[] = [
                    'nama_penilai' => $penilaian->penilai->nama ?? 'N/A',
                    'total_nilai' => $totalNilaiPenilai,
                    'jumlah_jawaban' => $jumlahJawaban,
                    'rata_rata' => $jumlahJawaban > 0 ? round($totalNilaiPenilai / $jumlahJawaban, 2) : 0,
                    'persentase' => $jumlahJawaban > 0 ? round(($totalNilaiPenilai / ($jumlahJawaban * 5)) * 100, 1) : 0
                ];
            }

            // Hitung rata-rata keseluruhan
            $rataRataKeseluruhan = $totalJumlahJawaban > 0 ? $totalNilaiKeseluruhan / $totalJumlahJawaban : 0;
            $persentaseKeseluruhan = $totalJumlahJawaban > 0 ? ($totalNilaiKeseluruhan / ($totalJumlahJawaban * 5)) * 100 : 0;
            $gradeKeseluruhan = $this->hitungGrade($persentaseKeseluruhan);

            // Statistik per kategori
            $statistikKategori = $this->getStatistikKategoriPegawai($penilaianList);

            $rekapData[] = [
                'pegawai' => $pegawai,
                'jumlah_penilai' => $jumlahPenilai,
                'total_nilai_keseluruhan' => $totalNilaiKeseluruhan,
                'total_jawaban' => $totalJumlahJawaban,
                'rata_rata_keseluruhan' => round($rataRataKeseluruhan, 2),
                'persentase_keseluruhan' => round($persentaseKeseluruhan, 1),
                'grade' => $gradeKeseluruhan,
                'detail_penilai' => $detailPenilai,
                'statistik_kategori' => $statistikKategori
            ];
        }

        // Urutkan berdasarkan rata-rata nilai tertinggi
        usort($rekapData, function($a, $b) {
            return $b['rata_rata_keseluruhan'] <=> $a['rata_rata_keseluruhan'];
        });

        return $rekapData;
    }

    private function getStatistikKategoriPegawai($penilaianList)
    {
        $kategoriStats = [];
        
        foreach ($penilaianList as $penilaian) {
            $jawaban = JawabanKuisioner::with('kuisioner')
                ->where('penilaian_id', $penilaian->id)
                ->get();

            foreach ($jawaban as $jawab) {
                $kategori = $jawab->kuisioner->kategori ?? 'Umum';
                
                if (!isset($kategoriStats[$kategori])) {
                    $kategoriStats[$kategori] = [
                        'total_skor' => 0,
                        'jumlah_jawaban' => 0
                    ];
                }
                
                $kategoriStats[$kategori]['total_skor'] += $jawab->skor;
                $kategoriStats[$kategori]['jumlah_jawaban']++;
            }
        }

        // Hitung rata-rata per kategori
        foreach ($kategoriStats as $kategori => $stats) {
            $rataRata = $stats['jumlah_jawaban'] > 0 ? $stats['total_skor'] / $stats['jumlah_jawaban'] : 0;
            $persentase = $stats['jumlah_jawaban'] > 0 ? ($stats['total_skor'] / ($stats['jumlah_jawaban'] * 5)) * 100 : 0;
            
            $kategoriStats[$kategori]['rata_rata'] = round($rataRata, 2);
            $kategoriStats[$kategori]['persentase'] = round($persentase, 1);
        }

        return $kategoriStats;
    }

    private function getStatistikUmum($periodeId)
    {
        $totalPenilaian = Penilaian::where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->count();

        $totalPegawaiDinilai = Penilaian::where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->distinct('dinilai_pegawai_id')
            ->count();

        $totalJawaban = JawabanKuisioner::whereHas('penilaian', function($query) use ($periodeId) {
            $query->where('periode_id', $periodeId)->where('status', 'selesai');
        })->count();

        $totalSkor = JawabanKuisioner::whereHas('penilaian', function($query) use ($periodeId) {
            $query->where('periode_id', $periodeId)->where('status', 'selesai');
        })->sum('skor');

        $rataRataUmum = $totalJawaban > 0 ? $totalSkor / $totalJawaban : 0;
        $persentaseUmum = $totalJawaban > 0 ? ($totalSkor / ($totalJawaban * 5)) * 100 : 0;

        return [
            'total_penilaian' => $totalPenilaian,
            'total_pegawai_dinilai' => $totalPegawaiDinilai,
            'total_jawaban' => $totalJawaban,
            'total_skor' => $totalSkor,
            'rata_rata_umum' => round($rataRataUmum, 2),
            'persentase_umum' => round($persentaseUmum, 1),
            'grade_umum' => $this->hitungGrade($persentaseUmum)
        ];
    }

    private function getRankingPegawai($periodeId)
    {
        $ranking = DB::table('penilaian as p')
            ->select(
                'peg.id_pegawai',
                'peg.nama',
                'dep.nama_departemen',
                DB::raw('AVG(CAST(p.total_nilai AS DECIMAL(10,2))) as rata_rata_nilai'),
                DB::raw('COUNT(p.id) as jumlah_penilai')
            )
            ->join('pegawai as peg', 'p.dinilai_pegawai_id', '=', 'peg.id_pegawai')
            ->join('departemen as dep', 'peg.departemen_id', '=', 'dep.id')
            ->where('p.periode_id', $periodeId)
            ->where('p.status', 'selesai')
            ->groupBy('peg.id_pegawai', 'peg.nama', 'dep.nama_departemen')
            ->orderBy('rata_rata_nilai', 'DESC')
            ->limit(10)
            ->get();

        return $ranking;
    }

    private function getStatistikDepartemen($periodeId)
    {
        $statistikDept = DB::table('penilaian as p')
            ->select(
                'dep.nama_departemen',
                DB::raw('COUNT(DISTINCT p.dinilai_pegawai_id) as jumlah_pegawai'),
                DB::raw('COUNT(p.id) as total_penilaian'),
                DB::raw('AVG(CAST(p.total_nilai AS DECIMAL(10,2))) as rata_rata_nilai')
            )
            ->join('pegawai as peg', 'p.dinilai_pegawai_id', '=', 'peg.id_pegawai')
            ->join('departemen as dep', 'peg.departemen_id', '=', 'dep.id')
            ->where('p.periode_id', $periodeId)
            ->where('p.status', 'selesai')
            ->groupBy('dep.nama_departemen')
            ->orderBy('rata_rata_nilai', 'DESC')
            ->get();

        return $statistikDept;
    }

    private function hitungGrade($persentase)
    {
        if ($persentase >= 90) return ['grade' => 'A', 'label' => 'Sangat Baik', 'color' => 'success'];
        if ($persentase >= 80) return ['grade' => 'B', 'label' => 'Baik', 'color' => 'primary'];
        if ($persentase >= 70) return ['grade' => 'C', 'label' => 'Cukup', 'color' => 'warning'];
        if ($persentase >= 60) return ['grade' => 'D', 'label' => 'Kurang', 'color' => 'danger'];
        return ['grade' => 'E', 'label' => 'Tidak Baik', 'color' => 'dark'];
    }

    public function detailPegawai($periodeId, $pegawaiId)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';

        $periode = PeriodePenilaian::find($periodeId);
        $pegawaiDinilai = Pegawai::with(['departemen', 'jabatan'])->find($pegawaiId);

        if (!$periode || !$pegawaiDinilai) {
            return redirect()->route('kepala-yayasan.index')->with('error', 'Data tidak ditemukan.');
        }

        // Ambil semua penilaian untuk pegawai ini
        $penilaianList = Penilaian::with(['penilai'])
            ->where('periode_id', $periodeId)
            ->where('dinilai_pegawai_id', $pegawaiId)
            ->where('status', 'selesai')
            ->get();

        // Detail jawaban per kategori dari semua penilai
        $detailKategori = [];
        $totalKeseluruhan = 0;
        $jumlahJawabanKeseluruhan = 0;

        foreach ($penilaianList as $penilaian) {
            $jawaban = JawabanKuisioner::with('kuisioner')
                ->where('penilaian_id', $penilaian->id)
                ->get();

            foreach ($jawaban as $jawab) {
                $kategori = $jawab->kuisioner->kategori ?? 'Umum';
                
                if (!isset($detailKategori[$kategori])) {
                    $detailKategori[$kategori] = [
                        'jawaban' => [],
                        'total_skor' => 0,
                        'jumlah_jawaban' => 0
                    ];
                }
                
                $detailKategori[$kategori]['jawaban'][] = [
                    'pertanyaan' => $jawab->kuisioner->pertanyaan,
                    'skor' => $jawab->skor,
                    'penilai' => $penilaian->penilai->nama ?? 'N/A'
                ];
                
                $detailKategori[$kategori]['total_skor'] += $jawab->skor;
                $detailKategori[$kategori]['jumlah_jawaban']++;
                
                $totalKeseluruhan += $jawab->skor;
                $jumlahJawabanKeseluruhan++;
            }
        }

        // Hitung statistik per kategori
        foreach ($detailKategori as $kategori => $data) {
            $rataRata = $data['jumlah_jawaban'] > 0 ? $data['total_skor'] / $data['jumlah_jawaban'] : 0;
            $persentase = $data['jumlah_jawaban'] > 0 ? ($data['total_skor'] / ($data['jumlah_jawaban'] * 5)) * 100 : 0;
            
            $detailKategori[$kategori]['rata_rata'] = round($rataRata, 2);
            $detailKategori[$kategori]['persentase'] = round($persentase, 1);
            $detailKategori[$kategori]['grade'] = $this->hitungGrade($persentase);
        }

        // Statistik keseluruhan
        $rataRataKeseluruhan = $jumlahJawabanKeseluruhan > 0 ? $totalKeseluruhan / $jumlahJawabanKeseluruhan : 0;
        $persentaseKeseluruhan = $jumlahJawabanKeseluruhan > 0 ? ($totalKeseluruhan / ($jumlahJawabanKeseluruhan * 5)) * 100 : 0;
        $gradeKeseluruhan = $this->hitungGrade($persentaseKeseluruhan);

        return view('kepala-yayasan.rekap.detail-pegawai', compact(
            'pegawai',
            'nama_departemen',
            'periode',
            'pegawaiDinilai',
            'penilaianList',
            'detailKategori',
            'totalKeseluruhan',
            'jumlahJawabanKeseluruhan',
            'rataRataKeseluruhan',
            'persentaseKeseluruhan',
            'gradeKeseluruhan'
        ));
    }
}