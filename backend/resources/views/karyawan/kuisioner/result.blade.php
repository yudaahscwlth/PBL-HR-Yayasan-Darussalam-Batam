@extends('karyawan.master')

@section('title', 'Hasil Kuisioner')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <a href="{{ route('kuisioner.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <div>
                        <h4 class="mb-0">Hasil Penilaian</h4>
                        <p class="text-muted mb-0">{{ $periode->tahun ?? 'N/A' }} - {{ $periode->semester ?? 'N/A' }}</p>
                        <small class="text-muted">
                            Dinilai: <strong>{{ $dinilai->nama_pegawai ?? 'N/A' }}</strong> | 
                            Penilai: <strong>{{ $pegawai->nama_pegawai ?? 'N/A' }}</strong>
                        </small>
                    </div>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-print"></i> Print
                    </button>
                    {{-- Uncomment jika ada fitur export PDF
                    <a href="{{ route('kuisioner.export-pdf', [$periode->id, $dinilai->id_pegawai]) }}" 
                       class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Score Card -->
    <div class="card mb-4 border-{{ $grade['color'] }}">
        <div class="card-header bg-{{ $grade['color'] }} text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-0 text-white">Skor Keseluruhan</h5>
                    <small>Total {{ isset($jawaban) ? $jawaban->count() : 0 }} pertanyaan telah dijawab</small>
                </div>
                <div class="col-md-4 text-end">
                    <h2 class="mb-0 text-white">{{ $grade['grade'] }}</h2>
                    <small>{{ $grade['label'] }}</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h4 class="text-{{ $grade['color'] }}">{{ $totalKeseluruhan }}</h4>
                    <small class="text-muted">Total Skor</small>
                </div>
                <div class="col-md-3">
                    <h4 class="text-{{ $grade['color'] }}">{{ $maxKeseluruhan }}</h4>
                    <small class="text-muted">Skor Maksimal</small>
                </div>
                <div class="col-md-3">
                    <h4 class="text-{{ $grade['color'] }}">{{ round($rataKeseluruhan, 1) }}</h4>
                    <small class="text-muted">Rata-rata</small>
                </div>
                <div class="col-md-3">
                    <h4 class="text-{{ $grade['color'] }}">{{ round($persentaseKeseluruhan, 1) }}%</h4>
                    <small class="text-muted">Persentase</small>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-3">
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-{{ $grade['color'] }}" 
                         role="progressbar" 
                         style="width: {{ $persentaseKeseluruhan }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics by Category -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Statistik per Kategori</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($statistikKategori as $kategori => $stat)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="card-title">{{ ucwords($kategori) }}</h6>
                                <h4 class="text-primary">{{ $stat['persentase'] }}%</h4>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: {{ $stat['persentase'] }}%">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ $stat['total_skor'] }}/{{ $stat['max_skor'] }} 
                                    ({{ $stat['total_soal'] }} soal)
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detailed Answers -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Detail Jawaban</h5>
        </div>
        <div class="card-body p-0">
            @foreach($jawabanByKategori as $kategori => $jawabanList)
                <div class="border-bottom">
                    <div class="p-3 bg-light">
                        <h6 class="mb-0">{{ ucwords($kategori) }}</h6>
                        <small class="text-muted">{{ $jawabanList->count() }} pertanyaan</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Pertanyaan</th>
                                    <th width="15%" class="text-center">Skor</th>
                                    <th width="20%" class="text-center">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jawabanList as $index => $jawab)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $jawab->kuisioner->pertanyaan ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $jawab->skor >= 4 ? 'success' : ($jawab->skor == 3 ? 'warning' : 'danger') }}">
                                                {{ $jawab->skor }}/5
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                @if($jawab->skor == 5) Sangat Baik
                                                @elseif($jawab->skor == 4) Baik
                                                @elseif($jawab->skor == 3) Cukup
                                                @elseif($jawab->skor == 2) Kurang Baik
                                                @else Tidak Baik
                                                @endif
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Information Footer -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Informasi Penilaian</h6>
                    <small class="text-muted">
                        Status: <span class="badge bg-success">{{ ucfirst($penilaian->status ?? 'N/A') }}</span><br>
                        Tanggal: {{ $penilaian->updated_at ? $penilaian->updated_at->format('d/m/Y H:i') : 'N/A' }}<br>
                        Total Pertanyaan: {{ isset($jawaban) ? $jawaban->count() : 0 }}
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <h6>Skala Penilaian</h6>
                    <small class="text-muted">
                        5 = Sangat Baik | 4 = Baik | 3 = Cukup<br>
                        2 = Kurang Baik | 1 = Tidak Baik
                    </small>
                </div>
            </div>
            
            <hr class="my-3">
            
            <div class="text-center">
                <a href="{{ route('kuisioner.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header .btn, .text-end .btn {
        display: none !important;
    }
    
    .container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
    }
}

.progress {
    background-color: #e9ecef;
}

.badge {
    font-size: 0.875em;
}

.card h4, .card h5, .card h6 {
    margin-bottom: 0.5rem;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
    vertical-align: middle;
}
</style>
@endsection