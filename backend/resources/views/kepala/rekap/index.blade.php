@extends('kepala.master')
@php
    use App\Models\Kuisioner;
@endphp
@section('title', 'Rekap Penilaian SDM')

@section('content')
<div class="container-fluid">
    <!-- Header & Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Rekap Penilaian SDM</h4>
                    <small class="text-muted">
                        Periode: {{ $periodeAktif->tahun ?? 'N/A' }} - {{ $periodeAktif->semester ?? 'N/A' }}
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        {{-- <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download"></i> Export
                        </button> --}}
                        <button class="btn btn-outline-info btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> {{ $error }}
        </div>
    @else
        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $statistikKeseluruhan['total_pegawai'] }}</h3>
                                <p class="mb-0">Total Pegawai</p>
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
                                <h3 class="mb-0">{{ $statistikKeseluruhan['penilaian_selesai'] }}</h3>
                                <p class="mb-0">Penilaian Selesai</p>
                                <small class="opacity-75">{{ $statistikKeseluruhan['persentase_selesai'] }}%</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $statistikKeseluruhan['penilaian_proses'] }}</h3>
                                <p class="mb-0">Dalam Proses</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-{{ $statistikKeseluruhan['grade_keseluruhan']['color'] }} text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $statistikKeseluruhan['grade_keseluruhan']['grade'] }}</h3>
                                <p class="mb-0">Rata-rata Grade</p>
                                <small class="opacity-75">{{ round($statistikKeseluruhan['rata_nilai_keseluruhan'], 1) }} poin</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-star fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top & Bottom Performers -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-trophy"></i> Top Performers</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($topPerformers->count() > 0)
                            @foreach($topPerformers as $index => $performer)
                                <div class="d-flex align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="me-3">
                                        <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }} rounded-pill">
                                            #{{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $performer->nama }}</h6>
                                        <small class="text-muted">{{ $performer->nama_departemen }} - {{ $performer->nama_jabatan }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $performer->grade['color'] }}">{{ $performer->grade['grade'] }}</span>
                                        <br>
                                        <small class="text-muted">{{ round($performer->rata_nilai, 1) }} pts</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-3 text-center text-muted">
                                <i class="fas fa-info-circle"></i> Belum ada data penilaian
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Perlu Perhatian</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($bottomPerformers->count() > 0)
                            @foreach($bottomPerformers as $performer)
                                <div class="d-flex align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $performer->nama }}</h6>
                                        <small class="text-muted">{{ $performer->nama_departemen }} - {{ $performer->nama_jabatan }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $performer->grade['color'] }}">{{ $performer->grade['grade'] }}</span>
                                        <br>
                                        <small class="text-muted">{{ round($performer->rata_nilai, 1) }} pts</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-3 text-center text-muted">
                                <i class="fas fa-info-circle"></i> Semua pegawai berkinerja baik
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik per Departemen -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-building"></i> Statistik per Departemen</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th class="text-center">Total Pegawai</th>
                                <th class="text-center">Penilaian Selesai</th>
                                <th class="text-center">Progress</th>
                                <th class="text-center">Rata-rata Nilai</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statistikDepartemen as $dept)
                                <tr>
                                    <td>{{ $dept->nama_departemen }}</td>
                                    <td class="text-center">{{ $dept->total_pegawai }}</td>
                                    <td class="text-center">{{ $dept->penilaian_selesai }}</td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $dept->persentase_selesai >= 80 ? 'success' : ($dept->persentase_selesai >= 50 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $dept->persentase_selesai }}%">
                                                {{ $dept->persentase_selesai }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ round($dept->rata_nilai, 1) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $dept->grade['color'] }}">{{ $dept->grade['grade'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Rekap Detail -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="mb-0"><i class="fas fa-table"></i> Detail Rekap Penilaian</h6>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary {{ $sortBy == 'nama' ? 'active' : '' }}" 
                            onclick="changeSort('nama')">
                        <i class="fas fa-sort-alpha-{{ $sortBy == 'nama' && $sortOrder == 'desc' ? 'down' : 'up' }}"></i> Nama
                    </button>
                    <button type="button" class="btn btn-outline-secondary {{ $sortBy == 'departemen' ? 'active' : '' }}" 
                            onclick="changeSort('departemen')">
                        <i class="fas fa-sort-alpha-{{ $sortBy == 'departemen' && $sortOrder == 'desc' ? 'down' : 'up' }}"></i> Departemen
                    </button>
                    <button type="button" class="btn btn-outline-secondary {{ $sortBy == 'rata_nilai' ? 'active' : '' }}" 
                            onclick="changeSort('rata_nilai')">
                        <i class="fas fa-sort-numeric-{{ $sortBy == 'rata_nilai' && $sortOrder == 'desc' ? 'down' : 'up' }}"></i> Nilai
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pegawai</th>
                        <th class="text-center">Departemen</th>
                        <th class="text-center">Jabatan</th>
                        <th class="text-center">Penilaian</th>
                        <th class="text-center">Rata-rata Nilai</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapData as $data)
                        @php
                            $grade = null;
                            if ($data->rata_rata_nilai > 0) {
                                // PERBAIKAN: Hitung persentase dari rata-rata nilai (asumsi skala 1-5)
                                $persentase = ($data->rata_rata_nilai / 5) * 100;
                                
                                // Atau jika menggunakan skala 1-4:
                                // $persentase = ($data->rata_rata_nilai / 4) * 100;
                                
                                if ($persentase >= 90) $grade = ['grade' => 'A', 'color' => 'success'];
                                elseif ($persentase >= 80) $grade = ['grade' => 'B', 'color' => 'primary'];
                                elseif ($persentase >= 70) $grade = ['grade' => 'C', 'color' => 'warning'];
                                elseif ($persentase >= 60) $grade = ['grade' => 'D', 'color' => 'danger'];
                                else $grade = ['grade' => 'E', 'color' => 'dark'];
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($data->foto)
                                            <img src="{{ asset('uploads/pegawai/' . $data->foto) }}" 
                                                 alt="{{ $data->nama }}" 
                                                 class="rounded-circle" 
                                                 width="40" height="40">
                                        @else
                                         <img src="{{ asset('assets/images/user/avatar-1.jpg') }}" alt="Default Profile"  class="rounded-circle" 
                                                 width="40" height="40">
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $data->nama }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $data->nama_departemen ?? '-' }}</td>
                            <td class="text-center">{{ $data->nama_jabatan ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $data->penilaian_selesai }}/{{ $data->total_penilaian }}</span>
                            </td>
                            <td class="text-center">
                                @if($data->rata_rata_nilai > 0)
                                    <strong>{{ round($data->rata_rata_nilai, 1) }}</strong>
                                    <small class="text-muted d-block">/5.0</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($grade)
                                    <span class="badge bg-{{ $grade['color'] }}">{{ $grade['grade'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @switch($data->status_keseluruhan)
                                    @case('selesai')
                                        <span class="badge bg-success">Selesai</span>
                                        @break
                                    @case('sebagian_selesai')
                                        <span class="badge bg-warning">Sebagian</span>
                                        @break
                                    @case('sedang_proses')
                                        <span class="badge bg-info">Proses</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">Belum Dinilai</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('kepala.rekap.detail', ['pegawaiId' => $data->id_pegawai, 'periode_id' => $periodeAktif->id]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($data->total_penilaian > 0)
                                        <button class="btn btn-outline-info btn-sm" 
                                                onclick="showHistory({{ $data->id_pegawai }})"
                                                title="Riwayat Penilaian">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p class="mb-0">Tidak ada data rekap penilaian untuk periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
    @endif
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Rekap Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('kepala.rekap.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Periode Penilaian</label>
                        <select name="periode_id" class="form-select">
                            <option value="">Pilih Periode</option>
                            @foreach($periodeList as $periode)
                                <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                    {{ $periode->tahun }} - {{ $periode->semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Departemen</label>
                        <select name="departemen_id" class="form-select">
                            <option value="all">Semua Departemen</option>
                            @foreach($departemenList as $dept)
                                <option value="{{ $dept->id }}" {{ $departemenId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->nama_departemen }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Penilaian</label>
                        <select name="status" class="form-select">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="selesai" {{ $status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="belum_dinilai" {{ $status == 'belum_diisi' ? 'selected' : '' }}>Belum Dinilai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Rekap Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('kepala.rekap.export') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="periode_id" value="{{ $periodeAktif->id ?? '' }}">
                    <input type="hidden" name="departemen_id" value="{{ $departemenId ?? 'all' }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Format Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="excel" id="excel" checked>
                            <label class="form-check-label" for="excel">
                                <i class="fas fa-file-excel text-success"></i> Excel (.xlsx)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="pdf" id="pdf">
                            <label class="form-check-label" for="pdf">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="csv" id="csv">
                            <label class="form-check-label" for="csv">
                                <i class="fas fa-file-csv text-info"></i> CSV
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

@push('scripts')
<script>
function refreshData() {
    location.reload();
}

function changeSort(sortBy) {
    const currentUrl = new URL(window.location.href);
    const currentSortBy = currentUrl.searchParams.get('sort_by');
    const currentSortOrder = currentUrl.searchParams.get('sort_order');
    
    let newSortOrder = 'asc';
    if (currentSortBy === sortBy && currentSortOrder === 'asc') {
        newSortOrder = 'desc';
    }
    
    currentUrl.searchParams.set('sort_by', sortBy);
    currentUrl.searchParams.set('sort_order', newSortOrder);
    
    window.location.href = currentUrl.toString();
}

function showHistory(pegawaiId) {
    // Implementasi untuk menampilkan riwayat penilaian
    // Bisa menggunakan modal atau redirect ke halaman detail
    window.open(`{{ route('kepala.rekap.detail', ['pegawaiId' => '__ID__', 'periode_id' => $periodeAktif->id ?? '']) }}`.replace('__ID__', pegawaiId), '_blank');
}

// Auto refresh setiap 5 menit
setInterval(function() {
    // Bisa ditambahkan AJAX call untuk update statistik tanpa reload
}, 300000);
</script>
@endpush
@endsection