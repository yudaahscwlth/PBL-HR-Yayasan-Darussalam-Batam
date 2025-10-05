@extends('admin.master')

@section('title', 'Edit Pengajuan Cuti')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<style>
    .stat-card {
        transition: transform 0.3s;
        border-radius: 10px;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    .btn-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
    }
    .btn-warning:hover {
        background: linear-gradient(135deg, #ed88f7 0%, #f34c5c 100%);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid mt-4">
    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.cuti.index') }}" class="text-decoration-none">
                            <i class="ti ti-calendar-event me-1"></i>Pengajuan Cuti
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Pengajuan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="ti ti-edit me-2"></i>Edit Pengajuan Cuti #{{ $cuti->id_cuti }}
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.cuti.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card bg-light">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <div class="text-muted">Jatah Tahunan</div>
                                        <h3 class="mb-0">{{ $jatah }} Hari</h3>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="ti ti-calendar text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card bg-light">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <div class="text-muted">Cuti Terpakai</div>
                                        <h3 class="mb-0">{{ $cutiTerpakai }} Hari</h3>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="ti ti-clock text-danger fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card bg-light">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <div class="text-muted">Sisa Cuti</div>
                                        <h3 class="mb-0">{{ $sisaCuti }} Hari</h3>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="ti ti-check text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card bg-light">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <div class="text-muted">Status Saat Ini</div>
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="ti ti-clock me-1"></i>{{ $cuti->status_cuti }}
                                        </span>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="ti ti-hourglass text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit -->
                    <form action="{{ route('admin.cuti.update', $cuti->id_cuti) }}" method="POST" id="editCutiForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informasi Pengajuan -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Informasi Pengajuan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">ID Pengajuan</label>
                                        <input type="text" class="form-control" value="#{{ $cuti->id_cuti }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tanggal Pengajuan</label>
                                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($cuti->tanggal_pengajuan)->format('d/m/Y H:i') }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Status</label>
                                        <input type="text" class="form-control" value="{{ $cuti->status_cuti }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Pegawai -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-user me-2"></i>Informasi Pegawai</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Pegawai</label>
                                        <input type="text" class="form-control" value="{{ $pegawai->nama ?? Auth::user()->name }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Departemen</label>
                                        <input type="text" class="form-control" value="{{ $nama_departemen }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Cuti -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="ti ti-edit me-2"></i>Edit Detail Cuti</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                                        <select class="form-select @error('id_jenis_cuti') is-invalid @enderror" name="id_jenis_cuti" id="jenisCuti" required>
                                            <option value="">-- Pilih Jenis Cuti --</option>
                                            @foreach($jenisCuti as $jenis)
                                            <option value="{{ $jenis->id_jenis_cuti }}" 
                                                data-max="{{ $jenis->max_hari_cuti }}"
                                                {{ old('id_jenis_cuti', $cuti->id_jenis_cuti) == $jenis->id_jenis_cuti ? 'selected' : '' }}>
                                                {{ $jenis->nama_jenis_cuti }} (Max: {{ $jenis->max_hari_cuti }} hari)
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('id_jenis_cuti')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Maksimal Hari</label>
                                        <input type="text" id="maxHari" class="form-control" readonly 
                                               value="{{ $cuti->jenisCuti->max_hari_cuti }} hari">
                                    </div>
                                </div>

 <div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai" 
               class="form-control @error('tanggal_mulai') is-invalid @enderror" 
               value="{{ old('tanggal_mulai', $cuti->tanggal_mulai ? \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('Y-m-d') : '') }}" required>
        @error('tanggal_mulai')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
        <input type="date" id="tanggal_selesai" name="tanggal_selesai" 
               class="form-control @error('tanggal_selesai') is-invalid @enderror" 
               value="{{ old('tanggal_selesai', $cuti->tanggal_selesai ? \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('Y-m-d') : '') }}" required>
        @error('tanggal_selesai')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Hari Kerja</label>
                                        <input type="number" id="jumlahHari" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sisa Cuti Anda</label>
                                        <input type="text" class="form-control" value="{{ $sisaCuti }} hari" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Keterangan/Alasan Cuti <span class="text-danger">*</span></label>
                                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                              rows="4" placeholder="Jelaskan alasan dan keperluan cuti Anda..." 
                                              required maxlength="500">{{ old('keterangan', $cuti->keterangan) }}</textarea>
                                    <div class="form-text">Maksimal 500 karakter</div>
                                    @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Konfirmasi -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input @error('konfirmasi') is-invalid @enderror" 
                                           id="konfirmasi" name="konfirmasi" required>
                                    <label class="form-check-label" for="konfirmasi">
                                        <strong>Saya menyatakan bahwa perubahan data yang saya lakukan adalah benar dan dapat dipertanggungjawabkan. 
                                        Saya bersedia menerima konsekuensi apabila terdapat kesalahan dalam perubahan ini.</strong>
                                    </label>
                                    @error('konfirmasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.cuti.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i>Batal
                            </a>
                            <button type="button" class="btn btn-warning" id="updateBtn">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_mulai').min = today;
    document.getElementById('tanggal_selesai').min = today;
    
    // Calculate initial working days
    calculateWorkingDays();
    
    // Handle leave type selection
    document.getElementById('jenisCuti').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const maxDays = selectedOption.getAttribute('data-max');
        
        if (maxDays) {
            document.getElementById('maxHari').value = maxDays + ' hari';
        } else {
            document.getElementById('maxHari').value = '';
        }
        
        // Recalculate working days with new limits
        calculateWorkingDays();
    });
    
    // Calculate working days when dates change
    function calculateWorkingDays() {
        const startDate = document.getElementById('tanggal_mulai').value;
        const endDate = document.getElementById('tanggal_selesai').value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end < start) {
                alert('Tanggal selesai tidak boleh sebelum tanggal mulai!');
                document.getElementById('tanggal_selesai').value = '';
                document.getElementById('jumlahHari').value = '';
                return;
            }
            
            // Calculate working days (exclude weekends)
            let count = 0;
            let current = new Date(start);
            
            while (current <= end) {
                const dayOfWeek = current.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday (0) or Saturday (6)
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }
            
            document.getElementById('jumlahHari').value = count;
            
            // Check max days for selected leave type
            const jenisCuti = document.getElementById('jenisCuti');
            if (jenisCuti.selectedIndex > 0) {
                const maxDays = jenisCuti.options[jenisCuti.selectedIndex].getAttribute('data-max');
                
                if (maxDays && count > parseInt(maxDays)) {
                    alert(`Jumlah hari cuti (${count} hari kerja) melebihi batas maksimal (${maxDays} hari) untuk jenis cuti ini.`);
                    document.getElementById('tanggal_selesai').value = '';
                    document.getElementById('jumlahHari').value = '';
                    return;
                }
            }
            
            // Check remaining leave quota
            const sisaCuti = {{ $sisaCuti }};
            if (count > sisaCuti) {
                alert(`Jumlah hari cuti (${count} hari kerja) melebihi sisa cuti Anda (${sisaCuti} hari).`);
                document.getElementById('tanggal_selesai').value = '';
                document.getElementById('jumlahHari').value = '';
            }
        }
    }
    
    // Add event listeners for date calculation
    document.getElementById('tanggal_mulai').addEventListener('change', function() {
        // Update minimum date for end date
        document.getElementById('tanggal_selesai').min = this.value;
        calculateWorkingDays();
    });
    
    document.getElementById('tanggal_selesai').addEventListener('change', calculateWorkingDays);
    
    // Form submission
    document.getElementById('updateBtn').addEventListener('click', function() {
        const form = document.getElementById('editCutiForm');
        
        if (form.checkValidity()) {
            // Double check validations
            const jumlahHari = parseInt(document.getElementById('jumlahHari').value);
            const sisaCuti = {{ $sisaCuti }};
            
            if (jumlahHari > sisaCuti) {
                alert('Jumlah hari cuti melebihi sisa cuti Anda!');
                return;
            }
            
            // Show confirmation
            if (confirm('Apakah Anda yakin ingin menyimpan perubahan pengajuan cuti ini?')) {
                this.disabled = true;
                this.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Menyimpan...';
                form.submit();
            }
        } else {
            form.reportValidity();
        }
    });
});
</script>
@endpush