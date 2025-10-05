@extends('karyawan.master')

@section('title', 'Isi Kuisioner')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <a href="{{ route('kuisioner.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <div>
                    <h4 class="mb-0">{{ $periode->tahun ?? 'N/A' }} - {{ $periode->semester ?? 'N/A' }}</h4>
                    <p class="text-muted mb-0">Menilai: {{ $dinilai->nama_pegawai ?? 'N/A' }}</p>
                    <small class="text-muted">Penilai: {{ $pegawai->nama_pegawai ?? 'N/A' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Debug Info (remove in production) -->
    @if(config('app.debug'))
        <div class="card mb-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">Debug Information</h6>
            </div>
            <div class="card-body">
                <small>
                    Periode: {{ $periode->nama_periode ?? 'NULL' }}<br>
                    Dinilai: {{ $dinilai->nama_pegawai ?? 'NULL' }}<br>
                    Penilai: {{ $pegawai->nama ?? 'NULL' }}<br>
                    Kuisioner Categories: {{ isset($kuisionerByKategori) ? $kuisionerByKategori->keys()->implode(', ') : 'NULL' }}<br>
                    Existing Answers: {{ isset($existingAnswers) ? count($existingAnswers) : 0 }}
                </small>
            </div>
        </div>
    @endif

    <!-- Check if we have kuisioner data -->
    @if(isset($kuisionerByKategori) && $kuisionerByKategori->count() > 0)
        <!-- Form -->
        <form action="{{ route('kuisioner.store', [$periode->id ?? 0, $dinilai->id_pegawai ?? 0]) }}" method="POST">
            @csrf
            
            @foreach($kuisionerByKategori as $kategori => $kuisionerList)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white">{{ ucwords($kategori) }}</h5>
                        <small>{{ $kuisionerList->count() }} pertanyaan</small>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Kriteria</th>
                                        <th class="text-center">Sangat Baik</th>
                                        <th class="text-center">Baik</th>
                                        <th class="text-center">Cukup</th>
                                        <th class="text-center">Kurang Baik</th>
                                        <th class="text-center">Tidak Baik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kuisionerList as $index => $kuisioner)
                                        <tr>
                                            <td class="ps-4 py-3">{{ $kuisioner->pertanyaan }}</td>
                                            @for($i = 5; $i >= 1; $i--)
                                                <td class="text-center py-3">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="jawaban[{{ $kuisioner->id }}]" 
                                                           value="{{ $i }}"
                                                           {{ isset($existingAnswers[$kuisioner->id]) && $existingAnswers[$kuisioner->id] == $i ? 'checked' : '' }}
                                                           required>
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary">Simpan Jawaban</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </div>
                        <div>
                            <a href="{{ route('kuisioner.reset', [$periode->id ?? 0, $dinilai->id_pegawai ?? 0]) }}" 
                               class="btn btn-outline-danger"
                               onclick="return confirm('Yakin ingin menghapus semua jawaban?')">
                                Hapus Semua
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        <!-- No kuisioner available -->
        <div class="card">
            <div class="card-body text-center">
                <div class="alert alert-warning">
                    <h5>Tidak Ada Kuisioner</h5>
                    <p>Tidak ada kuisioner aktif yang tersedia untuk periode ini.</p>
                    <p class="mb-0">
                        <strong>Debug Info:</strong><br>
                        Kuisioner data: {{ isset($kuisionerByKategori) ? 'Available but empty' : 'Not available' }}<br>
                        Total categories: {{ isset($kuisionerByKategori) ? $kuisionerByKategori->count() : 0 }}
                    </p>
                    <hr>
                    <a href="{{ route('kuisioner.index') }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    padding: 12px 8px;
}

.table td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.form-check-input {
    width: 20px;
    height: 20px;
}

.bg-light {
    background-color: #f8f9fa !important;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 14px;
    }
    
    .table th, .table td {
        padding: 8px 4px;
    }
    
    .table th {
        font-size: 12px;
    }
}
</style>
@endsection