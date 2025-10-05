@extends('admin.master')

@section('title', 'Tambah Lokasi Kantor')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.LokasiKantor.index') }}">Lokasi Kantor</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tambah Lokasi Kantor</h5>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.LokasiKantor.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nama_lokasi" class="form-label">Nama Lokasi</label>
                        <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" 
                               id="nama_lokasi" name="nama_lokasi" 
                               value="{{ old('nama_lokasi') }}" required>
                        @error('nama_lokasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Metode 1: GPS Otomatis -->
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-crosshairs me-2"></i>Cara 1: Deteksi Lokasi Otomatis</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">Klik tombol di bawah untuk menggunakan lokasi GPS saat ini</p>
                            <button type="button" class="btn btn-success" onclick="getCurrentLocation()">
                                <i class="fas fa-location-arrow me-2"></i>Gunakan Lokasi Saat Ini
                            </button>
                            <div id="location-status" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <!-- Metode 2: Copy Paste dari Google Maps -->
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-map me-2"></i>Cara 2: Copy dari Google Maps</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Langkah-langkah:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Buka <a href="https://maps.google.com" target="_blank">Google Maps</a></li>
                                    <li>Cari lokasi yang diinginkan</li>
                                    <li>Klik kanan pada titik lokasi</li>
                                    <li>Copy koordinat yang muncul (contoh: -6.200000, 106.816666)</li>
                                    <li>Paste di kotak di bawah</li>
                                </ol>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control" id="coordinates-input" 
                                       placeholder="Contoh: -6.200000, 106.816666">
                                <button type="button" class="btn btn-info" onclick="parseCoordinates()">
                                    <i class="fas fa-paste me-1"></i>Parse
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Input Manual -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Koordinat</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input type="number" step="0.00000001" 
                                               class="form-control @error('latitude') is-invalid @enderror" 
                                               id="latitude" name="latitude" 
                                               value="{{ old('latitude') }}" required>
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="longitude" class="form-label">Longitude</label>
                                        <input type="number" step="0.00000001" 
                                               class="form-control @error('longitude') is-invalid @enderror" 
                                               id="longitude" name="longitude" 
                                               value="{{ old('longitude') }}" required>
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="radius_meter" class="form-label">Radius (meter)</label>
                        <input type="number" min="10" max="1000" 
                               class="form-control @error('radius_meter') is-invalid @enderror" 
                               id="radius_meter" name="radius_meter" 
                               value="{{ old('radius_meter', 50) }}" required>
                        @error('radius_meter')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Radius maksimal 1000 meter</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.LokasiKantor.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Preview Lokasi -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Preview Lokasi</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Koordinat Saat Ini:</strong><br>
                    <span id="coord-display">Belum diisi</span>
                </div>
                <div class="mb-3">
                    <strong>Radius:</strong><br>
                    <span id="radius-display">50</span> meter
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openInGoogleMaps()">
                        <i class="fas fa-external-link-alt me-1"></i>Lihat di Google Maps
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fungsi untuk mendapatkan lokasi GPS
function getCurrentLocation() {
    const statusDiv = document.getElementById('location-status');
    
    if (navigator.geolocation) {
        statusDiv.innerHTML = '<div class="alert alert-info">Mengambil lokasi...</div>';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                updateDisplay();
                
                statusDiv.innerHTML = '<div class="alert alert-success">Lokasi berhasil diperbarui!</div>';
                setTimeout(() => {
                    statusDiv.innerHTML = '';
                }, 3000);
            },
            function(error) {
                let errorMsg = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Akses lokasi ditolak. Silakan izinkan akses lokasi di browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Timeout dalam mengambil lokasi.';
                        break;
                    default:
                        errorMsg = 'Error dalam mengambil lokasi.';
                        break;
                }
                statusDiv.innerHTML = '<div class="alert alert-danger">' + errorMsg + '</div>';
            }
        );
    } else {
        statusDiv.innerHTML = '<div class="alert alert-warning">Geolocation tidak didukung di browser ini.</div>';
    }
}

// Fungsi untuk parse koordinat dari Google Maps
function parseCoordinates() {
    const input = document.getElementById('coordinates-input').value.trim();
    
    if (!input) {
        alert('Silakan masukkan koordinat terlebih dahulu');
        return;
    }
    
    // Parse berbagai format koordinat
    let lat, lng;
    
    // Format: -6.200000, 106.816666
    if (input.includes(',')) {
        const parts = input.split(',');
        if (parts.length === 2) {
            lat = parseFloat(parts[0].trim());
            lng = parseFloat(parts[1].trim());
        }
    }
    // Format: -6.200000 106.816666 (spasi)
    else if (input.includes(' ')) {
        const parts = input.split(' ');
        if (parts.length === 2) {
            lat = parseFloat(parts[0].trim());
            lng = parseFloat(parts[1].trim());
        }
    }
    
    if (isNaN(lat) || isNaN(lng)) {
        alert('Format koordinat tidak valid. Contoh: -6.200000, 106.816666');
        return;
    }
    
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('coordinates-input').value = '';
    
    updateDisplay();
    alert('Koordinat berhasil diperbarui!');
}

// Fungsi untuk update display preview
function updateDisplay() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    const radius = document.getElementById('radius_meter').value;
    
    document.getElementById('coord-display').textContent = 
        lat && lng ? `${lat}, ${lng}` : 'Belum diisi';
    document.getElementById('radius-display').textContent = 
        radius || '50';
}

// Fungsi untuk buka di Google Maps
function openInGoogleMaps() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    
    if (lat && lng) {
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, '_blank');
    } else {
        alert('Koordinat belum diisi');
    }
}

// Event listeners untuk update display
document.getElementById('latitude').addEventListener('input', updateDisplay);
document.getElementById('longitude').addEventListener('input', updateDisplay);
document.getElementById('radius_meter').addEventListener('input', updateDisplay);

// Initialize display
document.addEventListener('DOMContentLoaded', updateDisplay);
</script>
@endpush