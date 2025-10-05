@extends('admin.master')

@section('title', 'Kelola Kuisioner Periode - ' . $periode->nama_periode)

@section('content')
<!-- Header Info -->
<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mt-3">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
<div class="mb-3">
    <a href="{{route('periode.index')}}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4 class="text-white">{{ $periode->nama_periode }}</h4>
                <p class="mb-0">
                    Semester {{ $periode->semester }} - {{ $periode->tahun }} | 
                    Status: {{ ucwords($periode->status) }}
                </p>
                <div class="mt-2">
                    <span class="badge bg-light text-dark">{{ $kuisionerDipilih }} Terpilih</span>
                    <span class="badge bg-light text-dark">{{ $totalKuisioner }} Total</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Pilih Berdasarkan Kategori</h6>
            </div>
            <div class="card-body">
                <!-- Auto Select by Category -->
                <form action="{{ route('periode.kuisioner.auto-select', $periode->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="mb-2">
                        <select name="kategori" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <option value="kinerja">Kinerja</option>
                            <option value="kedisiplinan">Kedisiplinan</option>
                            <option value="komunikasi">Komunikasi</option>
                            <option value="kerjasama">Kerjasama</option>
                            <option value="kepemimpinan">Kepemimpinan</option>
                            <option value="inovasi">Inovasi</option>
                            <option value="pelayanan">Pelayanan</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button type="submit" name="action" value="add" class="btn btn-success btn-sm w-100">Tambah</button>
                        </div>
                        <div class="col-6">
                            <button type="submit" name="action" value="replace" class="btn btn-warning btn-sm w-100">Ganti</button>
                        </div>
                    </div>
                </form>
                
                <!-- Reset Button -->
                @if($kuisionerDipilih > 0)
                <form action="{{ route('periode.kuisioner.reset', $periode->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100" 
                            onclick="return confirm('Yakin ingin menghapus semua kuisioner?')">
                        Reset Semua
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Simpan Perubahan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('periode.kuisioner.update', $periode->id) }}" method="POST" id="kuisionerForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Pilih/Hapus Semua
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Simpan Perubahan</button>
                    
                    <div class="mt-2 text-center">
                        <small class="text-muted">
                            <span id="selectedCount">{{ $kuisionerDipilih }}</span> kuisioner terpilih
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Kuisioner List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Kuisioner</h5>
            </div>
            <div class="card-body">
                @if($kuisionerByKategori->count() > 0)
                    @foreach($kuisionerByKategori as $kategori => $kuisioners)
                        <div class="mb-4">
                            <h6><span class="badge bg-info">{{ ucwords($kategori) }}</span> ({{ $kuisioners->count() }})</h6>
                            
                            <div class="row">
                                @foreach($kuisioners as $kuisioner)
                                    <div class="col-md-6 mb-3">
                                        <div class="card {{ in_array($kuisioner->id, $kuisionerTerpilih) ? 'border-success' : '' }}">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input kuisioner-checkbox" 
                                                           type="checkbox" 
                                                           name="kuisioner_ids[]" 
                                                           value="{{ $kuisioner->id }}"
                                                           id="kuisioner_{{ $kuisioner->id }}"
                                                           {{ in_array($kuisioner->id, $kuisionerTerpilih) ? 'checked' : '' }}
                                                           form="kuisionerForm">
                                                    <label class="form-check-label w-100" for="kuisioner_{{ $kuisioner->id }}">
                                                        <p class="mb-0 small">{{ $kuisioner->pertanyaan }}</p>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if(!$loop->last)<hr>@endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <h5 class="text-muted">Belum Ada Kuisioner Aktif</h5>
                        <p class="text-muted">Silakan buat kuisioner terlebih dahulu.</p>
                        <a href="{{ route('kuisioner.index') }}" class="btn btn-primary">Buat Kuisioner</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const kuisionerCheckboxes = document.querySelectorAll('.kuisioner-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Update selected count
    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.kuisioner-checkbox:checked').length;
        selectedCountSpan.textContent = checkedCount;
        
        // Update card borders
        kuisionerCheckboxes.forEach(checkbox => {
            const card = checkbox.closest('.card');
            if (checkbox.checked) {
                card.classList.add('border-success');
            } else {
                card.classList.remove('border-success');
            }
        });
        
        // Update select all checkbox state
        if (selectAllCheckbox) {
            const totalCheckboxes = kuisionerCheckboxes.length;
            if (checkedCount === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedCount === totalCheckboxes) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }
    }

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            kuisionerCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Individual checkbox change
    kuisionerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
        });
    });

    // Initial count update
    updateSelectedCount();
});
</script>
@endpush

<style>
.card.border-success {
    border-color: #198754 !important;
    border-width: 2px !important;
}

.form-check-input:indeterminate {
    background-color: #6c757d;
    border-color: #6c757d;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10h8'/%3e%3c/svg%3e");
}
</style>