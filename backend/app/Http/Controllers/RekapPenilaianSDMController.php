<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Kuisioner;
use App\Models\JawabanKuisioner;
use App\Models\PeriodePenilaian;
use App\Models\Penilaian;
use App\Models\Pegawai;
use App\Models\Departemen;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekapPenilaianSDMController extends Controller
{
    
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'kepala_yayasan') {
            return redirect()->route('login')->with('error', 'Akses ditolak.');
        }
        $pegawai = $user->pegawai; 
        $nama_jabatan = $pegawai->jabatan->nama_jabatan;
        $nama_departemen = $pegawai->departemen->nama_departemen;

        // Get filter parameters
        $periodeId = $request->get('periode_id');
        $departemenId = $request->get('departemen_id');
        $status = $request->get('status', 'all');
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');

        // Data untuk dropdown filter
        $periodeList = PeriodePenilaian::orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->get();
        
        $departemenList = Departemen::orderBy('nama_departemen')->get();

        // Get periode aktif jika tidak ada filter periode
        $periodeAktif = $periodeId ? 
            PeriodePenilaian::find($periodeId) : 
            PeriodePenilaian::where('status', 'aktif')->first();

        if (!$periodeAktif) {
            return view('kepala.rekap.index', [
                'periodeList' => $periodeList,
                'departemenList' => $departemenList,
                'error' => 'Tidak ada periode penilaian yang tersedia.'
            ]);
        }
        
        // Build query untuk rekap dengan rata-rata yang benar
        $rekapData = $this->buildRekapQueryWithCorrectAverage($periodeAktif->id, $departemenId, $status, $sortBy, $sortOrder);

        // Hitung statistik keseluruhan
        $statistikKeseluruhan = $this->hitungStatistikKeseluruhan($periodeAktif->id, $departemenId);
        
        // Statistik per departemen
        $statistikDepartemen = $this->hitungStatistikDepartemen($periodeAktif->id);
        
        // Top dan bottom performers
        $topPerformers = $this->getTopPerformers($periodeAktif->id, 5);
        $bottomPerformers = $this->getBottomPerformers($periodeAktif->id, 5);

        return view('kepala.rekap.index', compact(
            'rekapData',
            'periodeList',
            'departemenList',
            'periodeAktif',
            'statistikKeseluruhan',
            'statistikDepartemen',
            'topPerformers',
            'bottomPerformers',
            'periodeId',
            'departemenId',
            'sortBy',
            'status',
            'sortOrder',
            'nama_departemen',
            'pegawai'
        ));
    }
    private function hitungNilaiRataRataPegawai($pegawaiId, $periodeId)
{
    // Ambil semua penilaian untuk pegawai ini di periode tertentu
    $penilaianData = DB::table('penilaian')
        ->where('dinilai_pegawai_id', $pegawaiId)
        ->where('periode_id', $periodeId)
        ->get();

    $nilaiData = [
        'rata_nilai' => 0,
        'nilai_tertinggi' => 0,
        'nilai_terendah' => 0,
        'total_penilaian' => $penilaianData->count(),
        'penilaian_selesai' => 0
    ];

    // Filter penilaian yang selesai
    $penilaianSelesai = $penilaianData->where('status', 'selesai');
    $nilaiData['penilaian_selesai'] = $penilaianSelesai->count();

    if ($penilaianSelesai->count() > 0) {
        $nilaiArray = [];
        
        foreach ($penilaianSelesai as $penilaian) {
            // Ambil semua jawaban untuk penilaian ini
            $jawabanData = DB::table('jawaban_kuisioner')
                ->where('penilaian_id', $penilaian->id)
                ->get();
            
            if ($jawabanData->count() > 0) {
                // Hitung rata-rata skor dari jawaban untuk penilaian ini
                $rataSkorPenilaian = $jawabanData->avg('skor');
                $nilaiArray[] = $rataSkorPenilaian;
            }
        }
        
        if (count($nilaiArray) > 0) {
            $nilaiData['rata_nilai'] = array_sum($nilaiArray) / count($nilaiArray);
            $nilaiData['nilai_tertinggi'] = max($nilaiArray);
            $nilaiData['nilai_terendah'] = min($nilaiArray);
        }
    }

    return [
        'rata_nilai' => round($nilaiData['rata_nilai'], 2),
        'nilai_tertinggi' => round($nilaiData['nilai_tertinggi'], 2),
        'nilai_terendah' => round($nilaiData['nilai_terendah'], 2),
        'total_penilaian' => $nilaiData['total_penilaian'],
        'penilaian_selesai' => $nilaiData['penilaian_selesai']
    ];
}

    private function buildRekapQueryWithCorrectAverage($periodeId, $departemenId = null, $status = 'all', $sortBy = 'nama', $sortOrder = 'asc')
    {
        // Ambil semua pegawai dengan informasi dasar
        $pegawaiQuery = DB::table('pegawai as p')
            ->leftJoin('departemen as d', 'p.id_departemen', '=', 'd.id_departemen')
            ->leftJoin('jabatan as j', 'p.id_jabatan', '=', 'j.id_jabatan')
            ->select([
                'p.id_pegawai',
                'p.nama',
                'p.email',
                'p.no_hp',
                'p.foto',
                'd.nama_departemen',
                'j.nama_jabatan'
            ]);

        // Filter departemen jika ada
        if ($departemenId && $departemenId !== 'all') {
            $pegawaiQuery->where('p.id_departemen', $departemenId);
        }

        $pegawaiData = $pegawaiQuery->get();

        // Untuk setiap pegawai, hitung statistik penilaian
        $rekapData = $pegawaiData->map(function($pegawai) use ($periodeId) {
            // Ambil semua penilaian untuk pegawai ini
            $penilaianData = DB::table('penilaian')
                ->where('dinilai_pegawai_id', $pegawai->id_pegawai)
                ->where('periode_id', $periodeId)
                ->get();

            $totalPenilaian = $penilaianData->count();
            $penilaianSelesai = $penilaianData->where('status', 'selesai')->count();
            
            // Hitung rata-rata dari penilaian yang selesai
            $nilaiSelesai = $penilaianData->where('status', 'selesai')->pluck('total_nilai')->filter();
            $rataNilai = $nilaiSelesai->count() > 0 ? $nilaiSelesai->avg() : 0;
            
 $grade = $this->hitungGrade($rataNilai > 0 ? ($rataNilai / 5 * 100) : 0);

            // Nilai tertinggi dan terendah
            $nilaiTertinggi = $nilaiSelesai->count() > 0 ? $nilaiSelesai->max() : 0;
            $nilaiTerendah = $nilaiSelesai->count() > 0 ? $nilaiSelesai->min() : 0;

            // Tentukan status keseluruhan
            $statusKeseluruhan = 'belum_dinilai';
            if ($totalPenilaian > 0) {
                if ($penilaianSelesai == 0) {
                    $statusKeseluruhan = 'sedang_proses';
                } elseif ($penilaianSelesai == $totalPenilaian) {
                    $statusKeseluruhan = 'selesai';
                } else {
                    $statusKeseluruhan = 'sebagian_selesai';
                }
            }

           return (object) [
            'id_pegawai' => $pegawai->id_pegawai,
            'nama' => $pegawai->nama,
            'email' => $pegawai->email,
            'no_hp' => $pegawai->no_hp,
            'foto' => $pegawai->foto,
            'nama_departemen' => $pegawai->nama_departemen,
            'nama_jabatan' => $pegawai->nama_jabatan,
            'total_penilaian' => $totalPenilaian,
            'penilaian_selesai' => $penilaianSelesai,
            'rata_rata_nilai' => round($rataNilai, 2),
            'nilai_tertinggi' => $nilaiTertinggi,
            'nilai_terendah' => $nilaiTerendah,
            'status_keseluruhan' => $statusKeseluruhan,
            'grade' => $grade // TAMBAHAN: Grade sudah dihitung
        ];
        });

        // Filter berdasarkan status
        if ($status !== 'all') {
            $rekapData = $rekapData->filter(function($item) use ($status) {
                switch ($status) {
                    case 'belum_dinilai':
                        return $item->total_penilaian == 0;
                    case 'selesai':
                        return $item->status_keseluruhan == 'selesai';
                    case 'proses':
                        return in_array($item->status_keseluruhan, ['sedang_proses', 'sebagian_selesai']);
                    default:
                        return true;
                }
            });
        }

        // Sorting
        $rekapData = $this->applySortingToCollection($rekapData, $sortBy, $sortOrder);

        return $rekapData;
    }

    private function applySortingToCollection($collection, $sortBy, $sortOrder)
    {
        switch ($sortBy) {
            case 'nama':
                return $sortOrder === 'desc' ? 
                    $collection->sortByDesc('nama') : 
                    $collection->sortBy('nama');
            case 'departemen':
                return $sortOrder === 'desc' ? 
                    $collection->sortByDesc('nama_departemen') : 
                    $collection->sortBy('nama_departemen');
            case 'jabatan':
                return $sortOrder === 'desc' ? 
                    $collection->sortByDesc('nama_jabatan') : 
                    $collection->sortBy('nama_jabatan');
            case 'rata_nilai':
                return $sortOrder === 'desc' ? 
                    $collection->sortByDesc('rata_rata_nilai') : 
                    $collection->sortBy('rata_rata_nilai');
            case 'total_penilaian':
                return $sortOrder === 'desc' ? 
                    $collection->sortByDesc('total_penilaian') : 
                    $collection->sortBy('total_penilaian');
            default:
                return $collection->sortBy('nama');
        }
    }

    // Method lama untuk backward compatibility
    private function buildRekapQuery($periodeId, $departemenId = null, $status = 'all')
    {
        $query = DB::table('pegawai as p')
            ->leftJoin('departemen as d', 'p.id_departemen', '=', 'd.id_departemen')
            ->leftJoin('jabatan as j', 'p.id_jabatan', '=', 'j.id_jabatan')
            ->leftJoin('penilaian as pen', function($join) use ($periodeId) {
                $join->on('p.id_pegawai', '=', 'pen.dinilai_pegawai_id')
                     ->where('pen.periode_id', '=', $periodeId);
            })
            ->select([
                'p.id_pegawai',
                'p.nama',
                'p.email',
                'p.no_hp',
                'p.foto',
                'd.nama_departemen',
                'j.nama_jabatan',
                DB::raw('COUNT(DISTINCT pen.penilai_pegawai_id) as total_penilaian'),
                DB::raw('COUNT(DISTINCT CASE WHEN pen.status = "selesai" THEN pen.penilai_pegawai_id END) as penilaian_selesai'),
                DB::raw('COALESCE(AVG(CASE WHEN pen.status = "selesai" THEN pen.total_nilai END), 0) as rata_rata_nilai'),
                DB::raw('COALESCE(MAX(CASE WHEN pen.status = "selesai" THEN pen.total_nilai END), 0) as nilai_tertinggi'),
                DB::raw('COALESCE(MIN(CASE WHEN pen.status = "selesai" THEN pen.total_nilai END), 0) as nilai_terendah'),
                DB::raw('CASE 
                    WHEN COUNT(DISTINCT pen.penilai_pegawai_id) = 0 THEN "belum_dinilai"
                    WHEN COUNT(DISTINCT CASE WHEN pen.status = "selesai" THEN pen.penilai_pegawai_id END) = 0 THEN "sedang_proses"
                    WHEN COUNT(DISTINCT pen.penilai_pegawai_id) = COUNT(DISTINCT CASE WHEN pen.status = "selesai" THEN pen.penilai_pegawai_id END) THEN "selesai"
                    ELSE "sebagian_selesai"
                END as status_keseluruhan')
            ])
            ->groupBy([
                'p.id_pegawai', 'p.nama', 'p.email', 'p.no_hp', 'p.foto',
                'd.nama_departemen', 'j.nama_jabatan'
            ]);

        // Filter departemen
        if ($departemenId && $departemenId !== 'all') {
            $query->where('p.id_departemen', $departemenId);
        }

        // Filter status
        if ($status !== 'all') {
            switch ($status) {
                case 'belum_dinilai':
                    $query->having('total_penilaian', '=', 0);
                    break;
                case 'selesai':
                    $query->having('status_keseluruhan', '=', 'selesai');
                    break;
                case 'proses':
                    $query->having('status_keseluruhan', 'IN', ['sedang_proses', 'sebagian_selesai']);
                    break;
            }
        }

        return $query;
    }

    private function applySorting($query, $sortBy, $sortOrder)
    {
        switch ($sortBy) {
            case 'nama':
                return $query->orderBy('p.nama', $sortOrder);
            case 'departemen':
                return $query->orderBy('d.nama_departemen', $sortOrder);
            case 'jabatan':
                return $query->orderBy('j.nama_jabatan', $sortOrder);
            case 'rata_nilai':
                return $query->orderBy('rata_rata_nilai', $sortOrder);
            case 'total_penilaian':
                return $query->orderBy('total_penilaian', $sortOrder);
            default:
                return $query->orderBy('p.nama', 'asc');
        }
    }

    private function hitungStatistikKeseluruhan($periodeId, $departemenId = null)
{
    $baseQuery = Pegawai::query();
    
    if ($departemenId && $departemenId !== 'all') {
        $baseQuery->where('id_departemen', $departemenId);
    }

    $totalPegawai = $baseQuery->count();
    
    // Hitung berdasarkan pegawai yang sudah dinilai dengan rata-rata yang benar
    $pegawaiList = $baseQuery->pluck('id_pegawai');
    
    $pegawaiSelesai = 0;
    $pegawaiProses = 0;
    $totalNilaiPegawai = [];
    
    foreach ($pegawaiList as $pegawaiId) {
        $penilaianData = DB::table('penilaian')
            ->where('dinilai_pegawai_id', $pegawaiId)
            ->where('periode_id', $periodeId)
            ->get();
        
        $totalPenilaian = $penilaianData->count();
        $penilaianSelesaiCount = $penilaianData->where('status', 'selesai')->count();
        
        if ($totalPenilaian > 0) {
            if ($penilaianSelesaiCount == $totalPenilaian) {
                $pegawaiSelesai++;
            } elseif ($penilaianSelesaiCount > 0) {
                $pegawaiProses++;
            }
            
            // PERBAIKAN: Hitung rata-rata nilai untuk pegawai ini menggunakan total_nilai
            $nilaiSelesai = $penilaianData->where('status', 'selesai')->pluck('total_nilai')->filter();
            if ($nilaiSelesai->count() > 0) {
                $rataNilaiPegawai = $nilaiSelesai->avg();
                $totalNilaiPegawai[] = $rataNilaiPegawai;
            }
        }
    }
    
    $rataNilaiKeseluruhan = count($totalNilaiPegawai) > 0 ? array_sum($totalNilaiPegawai) / count($totalNilaiPegawai) : 0;
    
    return [
        'total_pegawai' => $totalPegawai,
        'total_penilaian' => $pegawaiList->count(),
        'penilaian_selesai' => $pegawaiSelesai,
        'penilaian_proses' => $pegawaiProses,
        'persentase_selesai' => $totalPegawai > 0 ? round(($pegawaiSelesai / $totalPegawai) * 100, 1) : 0,
        'rata_nilai_keseluruhan' => round($rataNilaiKeseluruhan, 2),
        'grade_keseluruhan' => $this->hitungGrade($rataNilaiKeseluruhan > 0 ? ($rataNilaiKeseluruhan / $this->getMaxPossibleScore() * 100) : 0)
    ];
}

   private function hitungStatistikDepartemen($periodeId)
{
    return Departemen::select([
            'departemen.id_departemen',
            'departemen.nama_departemen'
        ])
        ->leftJoin('pegawai as p', 'departemen.id_departemen', '=', 'p.id_departemen')
        ->groupBy('departemen.id_departemen', 'departemen.nama_departemen')
        ->orderBy('departemen.nama_departemen')
        ->get()
        ->map(function($item) use ($periodeId) {
            // Ambil semua pegawai di departemen ini
            $pegawaiIds = DB::table('pegawai')
                ->where('id_departemen', $item->id_departemen)
                ->pluck('id_pegawai');
            
            $item->total_pegawai = $pegawaiIds->count();
            $item->penilaian_selesai = 0;
            $totalNilaiDepartemen = [];
            
            foreach ($pegawaiIds as $pegawaiId) {
                $penilaianData = DB::table('penilaian')
                    ->where('dinilai_pegawai_id', $pegawaiId)
                    ->where('periode_id', $periodeId)
                    ->get();
                
                $totalPenilaian = $penilaianData->count();
                $penilaianSelesaiCount = $penilaianData->where('status', 'selesai')->count();
                
                if ($totalPenilaian > 0 && $penilaianSelesaiCount == $totalPenilaian) {
                    $item->penilaian_selesai++;
                }
                
                // PERBAIKAN: Hitung rata-rata nilai untuk pegawai ini menggunakan total_nilai
                $nilaiSelesai = $penilaianData->where('status', 'selesai')->pluck('total_nilai')->filter();
                if ($nilaiSelesai->count() > 0) {
                    $rataNilaiPegawai = $nilaiSelesai->avg();
                    $totalNilaiDepartemen[] = $rataNilaiPegawai;
                }
            }
            
            $item->rata_nilai = count($totalNilaiDepartemen) > 0 ? 
                array_sum($totalNilaiDepartemen) / count($totalNilaiDepartemen) : 0;
            
            $item->persentase_selesai = $item->total_pegawai > 0 ? 
                round(($item->penilaian_selesai / $item->total_pegawai) * 100, 1) : 0;
            $item->grade = $this->hitungGrade($item->rata_nilai > 0 ? 
                (($item->rata_nilai / $this->getMaxPossibleScore()) * 100) : 0);
            
            return $item;
        });
}

    private function getTopPerformers($periodeId, $limit = 5)
{
    // Ambil semua pegawai dan hitung rata-rata nilai mereka
    $pegawaiData = DB::table('pegawai as p')
        ->join('departemen as d', 'p.id_departemen', '=', 'd.id_departemen')
        ->join('jabatan as j', 'p.id_jabatan', '=', 'j.id_jabatan')
        ->select([
            'p.id_pegawai',
            'p.nama',
            'p.foto',
            'd.nama_departemen',
            'j.nama_jabatan'
        ])
        ->get();

    $performers = [];
    
    foreach ($pegawaiData as $pegawai) {
        $penilaianData = DB::table('penilaian')
            ->where('dinilai_pegawai_id', $pegawai->id_pegawai)
            ->where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->get();
        
        if ($penilaianData->count() > 0) {
            $nilaiRataRata = [];
            
            foreach ($penilaianData as $penilaian) {
                // Ambil semua jawaban untuk penilaian ini
                $jawabanData = DB::table('jawaban_kuisioner')
                    ->where('penilaian_id', $penilaian->id)
                    ->get();
                
                if ($jawabanData->count() > 0) {
                    // Hitung rata-rata skor dari jawaban
                    $rataSkorJawaban = $jawabanData->avg('skor');
                    $nilaiRataRata[] = $rataSkorJawaban;
                }
            }
            
            if (count($nilaiRataRata) > 0) {
                $rataNilai = array_sum($nilaiRataRata) / count($nilaiRataRata);
                $jumlahPenilaian = $penilaianData->count();
                
                $performers[] = (object) [
                    'nama' => $pegawai->nama,
                    'foto' => $pegawai->foto,
                    'nama_departemen' => $pegawai->nama_departemen,
                    'nama_jabatan' => $pegawai->nama_jabatan,
                    'rata_nilai' => round($rataNilai, 2),
                    'jumlah_penilaian' => $jumlahPenilaian,
                    'grade' => $this->hitungGrade($rataNilai / 5 * 100) // Asumsi skala 1-5
                ];
            }
        }
    }
    
    // Sort by rata_nilai descending dan ambil top performers
    usort($performers, function($a, $b) {
        return $b->rata_nilai <=> $a->rata_nilai;
    });
    
    return collect(array_slice($performers, 0, $limit));
}

   private function getBottomPerformers($periodeId, $limit = 5)
{
    // Ambil semua pegawai dan hitung rata-rata nilai mereka
    $pegawaiData = DB::table('pegawai as p')
        ->join('departemen as d', 'p.id_departemen', '=', 'd.id_departemen')
        ->join('jabatan as j', 'p.id_jabatan', '=', 'j.id_jabatan')
        ->select([
            'p.id_pegawai',
            'p.nama',
            'p.foto',
            'd.nama_departemen',
            'j.nama_jabatan'
        ])
        ->get();

    $performers = [];
    
    foreach ($pegawaiData as $pegawai) {
        $penilaianData = DB::table('penilaian')
            ->where('dinilai_pegawai_id', $pegawai->id_pegawai)
            ->where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->get();
        
        if ($penilaianData->count() > 0) {
            $nilaiRataRata = [];
            
            foreach ($penilaianData as $penilaian) {
                // Ambil semua jawaban untuk penilaian ini
                $jawabanData = DB::table('jawaban_kuisioner')
                    ->where('penilaian_id', $penilaian->id)
                    ->get();
                
                if ($jawabanData->count() > 0) {
                    // Hitung rata-rata skor dari jawaban
                    $rataSkorJawaban = $jawabanData->avg('skor');
                    $nilaiRataRata[] = $rataSkorJawaban;
                }
            }
            
            if (count($nilaiRataRata) > 0) {
                $rataNilai = array_sum($nilaiRataRata) / count($nilaiRataRata);
                $jumlahPenilaian = $penilaianData->count();
                
                $performers[] = (object) [
                    'nama' => $pegawai->nama,
                    'foto' => $pegawai->foto,
                    'nama_departemen' => $pegawai->nama_departemen,
                    'nama_jabatan' => $pegawai->nama_jabatan,
                    'rata_nilai' => round($rataNilai, 2),
                    'jumlah_penilaian' => $jumlahPenilaian,
                    'grade' => $this->hitungGrade($rataNilai / 5 * 100) // Asumsi skala 1-5
                ];
            }
        }
    }
    
    // Sort by rata_nilai ascending dan ambil bottom performers
    usort($performers, function($a, $b) {
        return $a->rata_nilai <=> $b->rata_nilai;
    });
    
    return collect(array_slice($performers, 0, $limit));
}

public function detail($pegawaiId, Request $request)
    {
        $periodeId = $request->get('periode_id');
        
        $pegawai = Pegawai::with(['departemen', 'jabatan'])
            ->where('id_pegawai', $pegawaiId)
            ->first();
        $nama_jabatan = $pegawai->jabatan->nama_jabatan;
        $nama_departemen = $pegawai->departemen->nama_departemen;

        if (!$pegawai) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $periode = PeriodePenilaian::find($periodeId);
        if (!$periode) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        // Get semua penilaian untuk pegawai ini di periode tertentu
        $daftarPenilaian = Penilaian::with(['penilaiPegawai.departemen', 'penilaiPegawai.jabatan'])
            ->where('dinilai_pegawai_id', $pegawaiId)
            ->where('periode_id', $periodeId)
            ->get();

        // Hitung statistik detail menggunakan method yang konsisten
        $nilaiData = $this->hitungNilaiRataRataPegawai($pegawaiId, $periodeId);
        
        $statistikDetail = [
            'total_penilai' => $daftarPenilaian->count(),
            'penilaian_selesai' => $daftarPenilaian->where('status', 'selesai')->count(),
            'rata_nilai' => $nilaiData['rata_nilai'],
            'nilai_tertinggi' => $nilaiData['nilai_tertinggi'],
            'nilai_terendah' => $nilaiData['nilai_terendah'],
            'grade' => $this->hitungGrade($nilaiData['rata_nilai'] > 0 ? ($nilaiData['rata_nilai'] / 5 * 100) : 0)
        ];

        // Get detail jawaban per kategori (ambil dari salah satu penilaian yang selesai)
        $detailJawabanKategori = null;
        $penilaianSample = $daftarPenilaian->where('status', 'selesai')->first();
        
        if ($penilaianSample) {
            $jawaban = JawabanKuisioner::with(['kuisioner'])
                ->where('penilaian_id', $penilaianSample->id)
                ->get();
            
            // Group jawaban by kategori
            $jawabanByKategori = $jawaban->groupBy(function($item) {
                return $item->kuisioner->kategori ?? 'Umum';
            });
            
            // Hitung statistik per kategori
            $detailJawabanKategori = [];
            foreach ($jawabanByKategori as $kategori => $jawabanList) {
                $totalSkor = $jawabanList->sum('skor');
                $jumlahSoal = $jawabanList->count();
                $maxSkor = $jumlahSoal * 5; // Asumsi skala 1-5
                
                // Prevent division by zero
                $rata2 = $jumlahSoal > 0 ? $totalSkor / $jumlahSoal : 0;
                $persentase = $maxSkor > 0 ? ($totalSkor / $maxSkor) * 100 : 0;

                $detailJawabanKategori[$kategori] = [
                    'total_soal' => $jumlahSoal,
                    'total_skor' => $totalSkor,
                    'max_skor' => $maxSkor,
                    'rata_rata' => round($rata2, 2),
                    'persentase' => round($persentase, 1),
                    'detail' => $jawabanList
                ];
            }
        }

        return view('kepala.rekap.detail', compact(
            'pegawai',
            'periode',
            'daftarPenilaian',
            'statistikDetail',
            'detailJawabanKategori',
            'nama_departemen'
        ));
    }

    public function export(Request $request)
    {
        $periodeId = $request->get('periode_id');
        $departemenId = $request->get('departemen_id');
        $format = $request->get('format', 'excel'); // excel, pdf, csv

        $periode = PeriodePenilaian::find($periodeId);
        if (!$periode) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $rekapData = $this->buildRekapQueryWithCorrectAverage($periodeId, $departemenId);

        switch ($format) {
            case 'pdf':
                return $this->exportToPDF($rekapData, $periode, $departemenId);
            case 'csv':
                return $this->exportToCSV($rekapData, $periode, $departemenId);
            default:
                return $this->exportToExcel($rekapData, $periode, $departemenId);
        }
    }

    private function exportToPDF($data, $periode, $departemenId)
    {
        // Implementation untuk export PDF
        // Bisa menggunakan library seperti DomPDF atau TCPDF
        // Return download response
    }

    private function exportToExcel($data, $periode, $departemenId)
    {
        // Implementation untuk export Excel
        // Bisa menggunakan library seperti PhpSpreadsheet atau Laravel Excel
        // Return download response
    }

    private function exportToCSV($data, $periode, $departemenId)
    {
        $filename = "rekap_penilaian_sdm_{$periode->tahun}_{$periode->semester}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'ID Pegawai', 'Nama Pegawai', 'Departemen', 'Jabatan', 
                'Total Penilaian', 'Penilaian Selesai', 'Rata-rata Nilai',
                'Nilai Tertinggi', 'Nilai Terendah', 'Status'
            ]);
            
            // Data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id_pegawai,
                    $row->nama,
                    $row->nama_departemen,
                    $row->nama_jabatan,
                    $row->total_penilaian,
                    $row->penilaian_selesai,
                    round($row->rata_rata_nilai, 2),
                    $row->nilai_tertinggi,
                    $row->nilai_terendah,
                    $row->status_keseluruhan
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getMaxPossibleScore()
    {
        // Asumsi maksimal skor berdasarkan jumlah kuisioner aktif * 5
        // Anda bisa sesuaikan dengan logic aplikasi
        $totalKuisioner = Kuisioner::where('aktif', 1)->count(); // 1 = aktif, 0 = tidak aktif
        return $totalKuisioner * 5;
    }

    private function hitungGrade($persentase)
    {
        if ($persentase >= 90) return ['grade' => 'A', 'label' => 'Sangat Baik', 'color' => 'success'];
        if ($persentase >= 80) return ['grade' => 'B', 'label' => 'Baik', 'color' => 'primary'];
        if ($persentase >= 70) return ['grade' => 'C', 'label' => 'Cukup', 'color' => 'warning'];
        if ($persentase >= 60) return ['grade' => 'D', 'label' => 'Kurang', 'color' => 'danger'];
        return ['grade' => 'E', 'label' => 'Tidak Baik', 'color' => 'dark'];
    }

    // API endpoints untuk AJAX calls
    public function getRekapData(Request $request)
    {
        $periodeId = $request->get('periode_id');
        $departemenId = $request->get('departemen_id');
        
        if (!$periodeId) {
            return response()->json(['error' => 'Periode ID diperlukan'], 400);
        }

        $rekapQuery = $this->buildRekapQuery($periodeId, $departemenId);
        $rekapData = $rekapQuery->get();

        return response()->json([
            'success' => true,
            'data' => $rekapData,
            'count' => $rekapData->count()
        ]);
    }

    public function getStatistikDashboard(Request $request)
    {
        $periodeId = $request->get('periode_id');
        
        if (!$periodeId) {
            return response()->json(['error' => 'Periode ID diperlukan'], 400);
        }

        $statistik = $this->hitungStatistikKeseluruhan($periodeId);
        $statistikDepartemen = $this->hitungStatistikDepartemen($periodeId);
        
        return response()->json([
            'success' => true,
            'statistik_keseluruhan' => $statistik,
            'statistik_departemen' => $statistikDepartemen
        ]);
    }

}