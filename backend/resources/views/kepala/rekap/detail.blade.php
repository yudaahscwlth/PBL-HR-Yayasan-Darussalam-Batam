@extends('kepala.master')
@php
    use App\Models\Kuisioner;
@endphp
@section('title', 'Detail Rekap Penilaian - ' . $pegawai->nama)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('kepala.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kepala.rekap.index') }}">Rekap Penilaian SDM</a></li>
            <li class="breadcrumb-item active">{{ $pegawai->nama }}</li>
        </ol>
    </nav>

    <!-- Header Employee Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    @if($pegawai->foto)
                        <img src="{{ asset('uploads/pegawai/' . $pegawai->foto) }}" 
                             alt="{{ $pegawai->nama }}" 
                             class="rounded-circle img-fluid" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <h2 class="mb-2">{{ $pegawai->nama }}</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Departemen:</strong> {{ $pegawai->departemen->nama_departemen ?? '-' }}</p>
                            <p class="mb-1"><strong>Jabatan:</strong> {{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                            <p class="mb-1"><strong>Periode:</strong> {{ $periode->tahun }} - {{ $periode->semester }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Email:</strong> {{ $pegawai->email ?? '-' }}</p>
                            <p class="mb-1"><strong>Telepon:</strong> {{ $pegawai->telepon ?? '-' }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-{{ $pegawai->status_aktif ? 'success' : 'secondary' }}">
                                    {{ $pegawai->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <div class="btn-group-vertical">
                        <a href="{{ route('kepala.rekap.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        {{-- <button class="btn btn-outline-primary btn-sm mb-2" onclick="window.print()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download"></i> Export
                        </button> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $statistikDetail['total_penilai'] }}</h3>
                            <p class="mb-0">Total Penilai</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $statistikDetail['penilaian_selesai'] }}</h3>
                            <p class="mb-0">Penilaian Selesai</p>
                            <small class="opacity-75">
                                {{ $statistikDetail['total_penilai'] > 0 ? round(($statistikDetail['penilaian_selesai'] / $statistikDetail['total_penilai']) * 100, 1) : 0 }}%
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ round($statistikDetail['rata_nilai'], 1) }}</h3>
                            <p class="mb-0">Rata-rata Nilai</p>
                            <small class="opacity-75">
                                Tertinggi: {{ round($statistikDetail['nilai_tertinggi'], 1) }} | 
                                Terendah: {{ round($statistikDetail['nilai_terendah'], 1) }}
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-{{ $statistikDetail['grade']['color'] }} text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $statistikDetail['grade']['grade'] }}</h3>
                            <p class="mb-0">Grade</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis per Kategori -->
    @if($detailJawabanKategori)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Analisis Penilaian per Kategori</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($detailJawabanKategori as $kategori => $data)
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $kategori }}</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Soal:</span>
                                                <strong>{{ $data['total_soal'] }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Skor:</span>
                                                <strong>{{ $data['total_skor'] }}/{{ $data['max_skor'] }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between">
                                                <span>Rata-rata:</span>
                                                <strong>{{ $data['rata_rata'] }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Persentase:</span>
                                                <strong>{{ $data['persentase'] }}%</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $data['persentase'] >= 80 ? 'success' : ($data['persentase'] >= 60 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $data['persentase'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Daftar Penilai -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Daftar Penilai</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Penilai</th>
                            <th class="text-center">Departemen</th>
                            <th class="text-center">Jabatan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Tanggal Penilaian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftarPenilaian as $penilaian)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($penilaian->penilaiPegawai && $penilaian->penilaiPegawai->foto)
                                                <img src="{{ asset('uploads/pegawai/' . $penilaian->penilaiPegawai->foto) }}" 
                                                     alt="{{ $penilaian->penilaiPegawai->nama }}" 
                                                     class="rounded-circle" 
                                                     width="40" height="40">
                                            @else
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $penilaian->penilaiPegawai->nama ?? 'N/A' }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $penilaian->penilaiPegawai->departemen->nama_departemen ?? '-' }}</td>
                                <td class="text-center">{{ $penilaian->penilaiPegawai->jabatan->nama_jabatan ?? '-' }}</td>
                                <td class="text-center">
                                    @switch($penilaian->status)
                                        @case('selesai')
                                            <span class="badge bg-success">Selesai</span>
                                            @break
                                        @case('sedang_proses')
                                            <span class="badge bg-warning">Sedang Proses</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Belum Mulai</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    @if($penilaian->updated_at)
                                        {{ $penilaian->updated_at->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($penilaian->status == 'selesai')
                                        <button class="btn btn-outline-info btn-sm" 
                                                onclick="showDetailPenilaian({{ $penilaian->id }})"
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                                        <p class="mb-0">Belum ada data penilaian untuk pegawai ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Jawaban per Kategori -->
    @if($detailJawabanKategori)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> Detail Jawaban per Kategori</h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionKategori">
                    @foreach($detailJawabanKategori as $kategori => $data)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $loop->index }}" 
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                        aria-controls="collapse{{ $loop->index }}">
                                    <strong>{{ $kategori }}</strong>
                                    <span class="badge bg-{{ $data['persentase'] >= 80 ? 'success' : ($data['persentase'] >= 60 ? 'warning' : 'danger') }} ms-2">
                                        {{ $data['persentase'] }}%
                                    </span>
                                </button>
                            </h2>
                            <div id="collapse{{ $loop->index }}" 
                                 class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $loop->index }}" 
                                 data-bs-parent="#accordionKategori">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Pertanyaan</th>
                                                    <th class="text-center">Skor</th>
                                                    <th class="text-center">Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['detail'] as $jawaban)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $jawaban->kuisioner->pertanyaan ?? 'N/A' }}</td>
                                                        <td class="text-center">{{ $jawaban->skor }}</td>
                                                        <td class="text-center">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $jawaban->skor ? 'text-warning' : 'text-muted' }}"></i>
                                                            @endfor
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Detail Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="pegawai_id" value="{{ $pegawai->id_pegawai }}">
                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Format Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="pdf" id="pdf" checked>
                            <label class="form-check-label" for="pdf">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="excel" id="excel">
                            <label class="form-check-label" for="excel">
                                <i class="fas fa-file-excel text-success"></i> Excel (.xlsx)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Penilaian Modal -->
<div class="modal fade" id="detailPenilaianModal" tabindex="-1" aria-labelledby="detailPenilaianModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailPenilaianModalLabel">Detail Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailPenilaianContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showDetailPenilaian(penilaianId) {
    $('#detailPenilaianModal').modal('show');
    
    // Load detail via AJAX
    $.get(`/kepala/rekap/detail-penilaian/${penilaianId}`, function(data) {
        $('#detailPenilaianContent').html(data);
    }).fail(function() {
        $('#detailPenilaianContent').html('<div class="alert alert-danger">Gagal memuat data penilaian</div>');
    });
}

// Print functionality
window.addEventListener('beforeprint', function() {
    document.body.classList.add('print-mode');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('print-mode');
});
</script>
@endpush

@push('styles')
<style>
@media print {
    .btn, .breadcrumb, .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .accordion-button {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    .progress-bar {
        background-color: #007bff !important;
    }
}
</style>
@endpush
@endsection