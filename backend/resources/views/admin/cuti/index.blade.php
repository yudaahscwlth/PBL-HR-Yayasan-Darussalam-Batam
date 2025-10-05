@extends('admin.master')

@section('title', 'Pengajuan Cuti')

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
    .status-badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
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
</style>
@endsection

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Pengajuan Cuti</h4>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#cutiModal">
                            <i class="ti ti-plus me-1"></i> Ajukan Cuti Baru
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Statistics Cards -->
                   @php
                        $jatah = $pegawai->jatah_tahunan ?? 12; // Default 12 hari jika tidak ada

                        $cutiTerpakai = $cuti->where('status_cuti', 'Disetujui')
                            ->sum(function($item) {
                                return \Carbon\Carbon::parse($item->tanggal_mulai)
                                    ->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai)) + 1;
                            });

                        $sisaCuti = $jatah - $cutiTerpakai;
                        $cutiMenunggu = $cuti->where('status_cuti', 'Menunggu')->count();
                    @endphp

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card stat-card bg-light">
            <div class="card-body d-flex align-items-center">
                <div>
                    <div class="text-muted">Jatah Tahunan</div>
                    <h2 class="mb-0">{{ $jatah }} Hari</h2>
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
                    <h2 class="mb-0">{{ $cutiTerpakai }} Hari</h2>
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
                    <h2 class="mb-0">{{ $sisaCuti }} Hari</h2>
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
                    <div class="text-muted">Menunggu Persetujuan</div>
                    <h3 class="mb-0">{{ $cutiMenunggu }}</h3>
                </div>
                <div class="ms-auto">
                    <i class="ti ti-hourglass text-warning fs-4 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>


                    <!-- Data Table -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="ti ti-list me-2"></i>Riwayat Pengajuan Cuti</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Jenis Cuti</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Periode Cuti</th>
                                            <th>Durasi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cuti as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $item->jenisCuti->nama_jenis_cuti ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d/m/Y') }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }} 
                                                    <i class="ti ti-arrow-right mx-1"></i> 
                                                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                @php
                                                    $start = \Carbon\Carbon::parse($item->tanggal_mulai);
                                                    $end = \Carbon\Carbon::parse($item->tanggal_selesai);
                                                    $diffInDays = $start->diffInDays($end) + 1;
                                                @endphp
                                                <span class="badge bg-info">{{ $diffInDays }} hari</span>
                                            </td>
                                            <td>
                                                @if($item->status_cuti == 'Disetujui')
                                                    <span class="badge bg-success status-badge">
                                                        <i class="ti ti-check me-1"></i>Disetujui
                                                    </span>
                                                @elseif($item->status_cuti == 'Ditolak')
                                                    <span class="badge bg-danger status-badge">
                                                        <i class="ti ti-x me-1"></i>Ditolak
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark status-badge">
                                                        <i class="ti ti-clock me-1"></i>Menunggu
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" onclick="showDetail('{{ $item->id_cuti }}')" title="Lihat Detail">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    @if($item->status_cuti == 'Menunggu')
                                                    <a href="{{ route('admin.cuti.edit', $item->id_cuti) }}" 
                                                    class="btn btn-sm btn-outline-warning" 
                                                    title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </a>

                                                    <form action="{{ route('admin.cuti.destroy', $item->id_cuti) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelLeave(this)" title="Batalkan">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ti ti-inbox fs-1"></i>
                                                    <p class="mt-2">Belum ada pengajuan cuti</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengajuan Cuti -->
<div class="modal fade" id="cutiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="ti ti-calendar-plus me-2"></i>Form Pengajuan Cuti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.cuti.store') }}" method="POST" id="cutiForm">
                    @csrf
                    
                    <!-- Informasi Pegawai -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Informasi Pegawai</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Pegawai</label>
                                    <input type="text" class="form-control" value="{{ $pegawai->nama ?? Auth::user()->name }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Departemen</label>
                                    <input type="text" class="form-control" value="{{ $nama_departemen ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Cuti -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Detail Pengajuan Cuti</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                                    <select class="form-select" name="id_jenis_cuti" id="jenisCuti" required>
                                        <option value="">-- Pilih Jenis Cuti --</option>
                                        @foreach($jenisCuti as $jenis)
                                        <option value="{{ $jenis->id_jenis_cuti }}" data-max="{{ $jenis->max_hari_cuti }}">
                                            {{ $jenis->nama_jenis_cuti }} (Max: {{ $jenis->max_hari_cuti }} hari)
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Maksimal Hari</label>
                                    <input type="text" id="maxHari" class="form-control" readonly placeholder="Pilih jenis cuti terlebih dahulu">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jumlah Hari Kerja</label>
                                    <input type="number" id="jumlahHari" class="form-control" readonly placeholder="Akan dihitung otomatis">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sisa Cuti Anda</label>
                                    <input type="text" class="form-control" value="{{ $sisaCuti }} hari" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan/Alasan Cuti <span class="text-danger">*</span></label>
                                <textarea name="keterangan" class="form-control" rows="4" placeholder="Jelaskan alasan dan keperluan cuti Anda..." required maxlength="500"></textarea>
                                <div class="form-text">Maksimal 500 karakter</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="konfirmasi" name="konfirmasi" required>
                        <label class="form-check-label" for="konfirmasi">
                            <strong>Saya menyatakan bahwa data yang saya isi adalah benar dan dapat dipertanggungjawabkan. 
                            Saya bersedia menerima konsekuensi apabila terdapat kesalahan dalam pengajuan ini.</strong>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" id="submitBtn">
                    <i class="ti ti-send me-1"></i>Kirim Pengajuan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="ti ti-file-text me-2"></i>Detail Pengajuan Cuti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Tutup
                </button>
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
    
    // Handle leave type selection
    document.getElementById('jenisCuti').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const maxDays = selectedOption.getAttribute('data-max');
        
        if (maxDays) {
            document.getElementById('maxHari').value = maxDays + ' hari';
        } else {
            document.getElementById('maxHari').value = '';
        }
        
        // Reset dates and calculation
        document.getElementById('tanggal_mulai').value = '';
        document.getElementById('tanggal_selesai').value = '';
        document.getElementById('jumlahHari').value = '';
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
    document.getElementById('submitBtn').addEventListener('click', function() {
        const form = document.getElementById('cutiForm');
        
        if (form.checkValidity()) {
            // Double check validations
            const jumlahHari = parseInt(document.getElementById('jumlahHari').value);
            const sisaCuti = {{ $sisaCuti }};
            
            if (jumlahHari > sisaCuti) {
                alert('Jumlah hari cuti melebihi sisa cuti Anda!');
                return;
            }
            
            // Show confirmation
            if (confirm('Apakah Anda yakin ingin mengajukan cuti ini? Pengajuan akan dikirim untuk persetujuan atasan.')) {
                this.disabled = true;
                this.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Mengirim...';
                form.submit();
            }
        } else {
            form.reportValidity();
        }
    });
});

// Show detail function
function showDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const content = document.getElementById('detailContent');
    
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Memuat detail pengajuan...</p>
        </div>
    `;
    modal.show();
    
    // Fetch detail using AJAX
    fetch(`{{ url('hrd/cuti') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            let statusBadge = '';
            if (data.status_cuti === 'Disetujui') {
                statusBadge = '<span class="badge bg-success fs-6"><i class="ti ti-check me-1"></i>Disetujui</span>';
            } else if (data.status_cuti === 'Ditolak') {
                statusBadge = '<span class="badge bg-danger fs-6"><i class="ti ti-x me-1"></i>Ditolak</span>';
            } else {
                statusBadge = '<span class="badge bg-warning fs-6"><i class="ti ti-clock me-1"></i>Menunggu Persetujuan</span>';
            }
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><td class="fw-bold">ID Pengajuan:</td><td>#${data.id_cuti}</td></tr>
                            <tr><td class="fw-bold">Jenis Cuti:</td><td>${data.jenis_cuti.nama_jenis_cuti}</td></tr>
                            <tr><td class="fw-bold">Tanggal Pengajuan:</td><td>${new Date(data.tanggal_pengajuan).toLocaleDateString('id-ID')}</td></tr>
                            <tr><td class="fw-bold">Status:</td><td>${statusBadge}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><td class="fw-bold">Tanggal Mulai:</td><td>${new Date(data.tanggal_mulai).toLocaleDateString('id-ID')}</td></tr>
                            <tr><td class="fw-bold">Tanggal Selesai:</td><td>${new Date(data.tanggal_selesai).toLocaleDateString('id-ID')}</td></tr>
                            <tr><td class="fw-bold">Durasi:</td><td>${data.jumlah_hari} hari kerja</td></tr>
                            <tr><td class="fw-bold">Max Cuti:</td><td>${data.jenis_cuti.max_hari_cuti} hari</td></tr>
                        </table>
                    </div>
                </div>
                <div class="mt-3">
                    <h6 class="fw-bold">Keterangan/Alasan:</h6>
                    <div class="bg-light p-3 rounded">${data.keterangan}</div>
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ti ti-alert-circle me-2"></i>
                    Gagal memuat detail pengajuan. Silakan coba lagi.
                </div>
            `;
        });
}

// Cancel leave function  
function cancelLeave(button) {
    if (confirm('Apakah Anda yakin ingin membatalkan pengajuan cuti ini? Tindakan ini tidak dapat dibatalkan.')) {
        button.closest('form').submit();
    }
}
</script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('alert'))
        Swal.fire({
            icon: '{{ session('alert.type') ?? 'info' }}',
            title: '{{ session('alert.title') ?? 'Info' }}',
            html: `{!! session('alert.message') !!}`,
            @if(session('alert.errors'))
                footer: '<ul class="text-start list-unstyled mb-0">{!! collect(session('alert.errors'))->map(fn($e)=>"<li>• ".$e."</li>")->implode('') !!}</ul>',
            @endif
            showConfirmButton: true
        });
    @endif

    @if(session('notifikasi'))
        Swal.fire({
            icon: '{{ session('type') ?? 'info' }}',
            title: 'Pemberitahuan',
            text: '{{ session('notifikasi') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
</script>
@endpush