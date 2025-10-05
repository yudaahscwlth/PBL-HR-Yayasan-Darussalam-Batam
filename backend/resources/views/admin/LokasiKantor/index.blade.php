@extends('admin.master')

@section('title', 'Manajemen Lokasi Kantor')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Lokasi Kantor</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Lokasi Kantor</h5>
                <a href="{{ route('admin.LokasiKantor.tambah') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Lokasi
                </a>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Koordinat</th>
                                <th>Radius (meter)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lokasiKantor as $index => $lokasi)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $lokasi->nama_lokasi }}</td>
                                <td>
                                    <small class="text-muted">
                                        Lat: {{ $lokasi->latitude }}<br>
                                        Lng: {{ $lokasi->longitude }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $lokasi->radius_meter }}m</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="showLokasiDetail({{ $lokasi->id }})" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetailLokasi">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.LokasiKantor.edit', $lokasi) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.LokasiKantor.destroy', $lokasi) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Belum ada lokasi kantor yang terdaftar
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

<!-- Modal Detail Lokasi -->
<div class="modal fade" id="modalDetailLokasi" tabindex="-1" aria-labelledby="modalDetailLokasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLokasiLabel">
                    <i class="fas fa-map-marker-alt me-2"></i>Detail Lokasi Kantor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-loading" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                <div id="modal-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-building me-2"></i>Informasi Lokasi
                                    </h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="40%"><strong>Nama Lokasi:</strong></td>
                                            <td id="detail-nama"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Latitude:</strong></td>
                                            <td id="detail-latitude"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Longitude:</strong></td>
                                            <td id="detail-longitude"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Radius:</strong></td>
                                            <td>
                                                <span class="badge bg-info" id="detail-radius"></span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openGoogleMaps()">
                                            <i class="fas fa-external-link-alt me-1"></i>Buka di Google Maps
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="copyCoordinates()">
                                            <i class="fas fa-copy me-1"></i>Copy Koordinat
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-map me-2"></i>Preview Peta
                                    </h6>
                                    <div id="map-container" class="text-center">
                                        <div class="bg-secondary rounded" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                            <span class="text-white">
                                                <i class="fas fa-map-marked-alt fa-3x mb-2"></i><br>
                                                Peta akan ditampilkan di sini
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Koordinat: <span id="koordinat-display"></span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Informasi Tambahan</h6>
                                <ul class="mb-0">
                                    <li>Radius menentukan jarak maksimal untuk absensi dari titik pusat</li>
                                    <li>Koordinat menggunakan sistem GPS (WGS84)</li>
                                    <li>Akurasi GPS bergantung pada kondisi sinyal dan perangkat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" onclick="editLokasi()">
                    <i class="fas fa-edit me-1"></i>Edit Lokasi
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Data lokasi untuk modal
let currentLokasiData = {};

// Fungsi untuk menampilkan detail lokasi di modal
function showLokasiDetail(lokasiId) {
    // Reset modal
    document.getElementById('modal-loading').style.display = 'block';
    document.getElementById('modal-content').style.display = 'none';
    
    // Data lokasi (dalam implementasi nyata, ambil dari server via AJAX)
    const lokasiData = @json($lokasiKantor->keyBy('id'));
    
    setTimeout(() => {
        const lokasi = lokasiData[lokasiId];
        if (lokasi) {
            currentLokasiData = lokasi;
            
            // Isi data ke modal
            document.getElementById('detail-nama').textContent = lokasi.nama_lokasi;
            document.getElementById('detail-latitude').textContent = lokasi.latitude;
            document.getElementById('detail-longitude').textContent = lokasi.longitude;
            document.getElementById('detail-radius').textContent = lokasi.radius_meter + ' meter';
            document.getElementById('koordinat-display').textContent = lokasi.latitude + ', ' + lokasi.longitude;
            
            // Sembunyikan loading dan tampilkan content
            document.getElementById('modal-loading').style.display = 'none';
            document.getElementById('modal-content').style.display = 'block';
        }
    }, 500); // Simulasi loading
}

// Fungsi untuk membuka Google Maps
function openGoogleMaps() {
    if (currentLokasiData.latitude && currentLokasiData.longitude) {
        const url = `https://www.google.com/maps?q=${currentLokasiData.latitude},${currentLokasiData.longitude}`;
        window.open(url, '_blank');
    }
}

// Fungsi untuk copy koordinat
function copyCoordinates() {
    if (currentLokasiData.latitude && currentLokasiData.longitude) {
        const coordinates = `${currentLokasiData.latitude}, ${currentLokasiData.longitude}`;
        navigator.clipboard.writeText(coordinates).then(() => {
            // Tampilkan notifikasi berhasil
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check me-2"></i>Koordinat berhasil disalin!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Hapus toast setelah selesai
            toast.addEventListener('hidden.bs.toast', () => {
                document.body.removeChild(toast);
            });
        }).catch(() => {
            alert('Gagal menyalin koordinat');
        });
    }
}

// Fungsi untuk edit lokasi
function editLokasi() {
    if (currentLokasiData.id) {
        window.location.href = `{{ url('admin/lokasi-kantor') }}/${currentLokasiData.id}/edit`;
    }
}

// Event listener untuk modal ketika ditutup
document.getElementById('modalDetailLokasi').addEventListener('hidden.bs.modal', function () {
    currentLokasiData = {};
});
</script>
@endpush