@extends('karyawan.master')

@section('title', 'Home | Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Welcome Message -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg overflow-hidden bg-primary">
                <div class="card-body py-4 position-relative">
                    <!-- Background Pattern -->
                    <div class="position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-user-clock text-white" style="font-size: 8rem;"></i>
                    </div>
                    
                    <div class="d-flex align-items-center position-relative">
                        <div class="me-4">
                           <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center shadow-lg overflow-hidden" 
                              style="width: 70px; height: 70px; backdrop-filter: blur(10px);">
                              @if($pegawai->foto)
                                  <img src="{{ asset('uploads/pegawai/' . $pegawai->foto) }}" 
                                      alt="User Profile" 
                                      class="img-fluid rounded-circle" 
                                      style="width: 70px; height: 70px; object-fit: cover;">
                              @else
                                  <img src="{{ asset('assets/images/default-avatar.png') }}" 
                                      alt="Default Profile" 
                                      class="img-fluid rounded-circle" 
                                      style="width: 70px; height: 70px; object-fit: cover;">
                              @endif
                          </div>
                        </div>
                        <div class="text-white">
                            <h2 class="mb-2 fw-bold text-white">Selamat Datang, {{ $pegawai->nama }} !</h2>
                            <p class="mb-1 opacity-90 fs-5 text-white">{{ $nama_departemen }}</p>
                            <p class="mb-0 opacity-75 text-white">
                                <i class="fas fa-calendar-alt me-2 text-white"></i>
                                {{ now()->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Attendance Card -->
{{-- Form Absen dengan Lokasi - BAGIAN YANG DIPERBAIKI --}}
<div class="col-lg-4 col-md-12 mb-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold">Absensi Hari Ini</h5>
            <div id="location-status" class="text-muted small">
                <i class="fas fa-map-marker-alt me-1"></i>
                <span>Pilih lokasi kantor</span>
            </div>
        </div>
        <div class="card-body d-flex flex-column justify-content-center">
            <!-- Current Time Display -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 bg-primary bg-opacity-10" 
                     style="width: 80px; height: 80px; border: 3px solid #0d6efd;">
                    <i class="fas fa-clock fa-2x text-primary"></i>
                </div>
                <h4 class="mb-1 fw-bold text-primary" id="current-time">{{ now()->format('H:i:s') }}</h4>
                <p class="text-muted mb-0 small">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            
            <!-- Location Selection -->
            <div class="mb-4">
                <label for="office-location" class="form-label fw-medium">
                    <i class="fas fa-building me-2"></i>Pilih Lokasi Kantor
                </label>
                <select class="form-select" id="office-location" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($lokasiKantorList as $lokasi)
                        <option value="{{ $lokasi->id }}" 
                                data-nama="{{ $lokasi->nama_lokasi }}"
                                data-alamat="{{ $lokasi->alamat }}"
                                data-latitude="{{ $lokasi->latitude }}"
                                data-longitude="{{ $lokasi->longitude }}"
                                data-radius="{{ $lokasi->radius_meter }}">
                            {{ $lokasi->nama_lokasi }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Pilih lokasi kantor sesuai dengan tempat Anda bekerja hari ini
                </small>
            </div>
            
            <!-- Location Info Display -->
            <div id="selected-location-info" class="alert alert-light border d-none mb-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-map-marker-alt text-primary me-2 mt-1"></i>
                    <div class="flex-grow-1">
                        <strong id="location-name" class="d-block mb-1"></strong>
                        <small id="location-address" class="text-muted d-block mb-1"></small>
                        <small id="location-radius" class="text-info"></small>
                    </div>
                </div>
            </div>
            
            <!-- Location Alert -->
            <div id="location-alert" class="alert alert-info d-none mb-3" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <span id="location-message">Memverifikasi lokasi Anda...</span>
            </div>
            
            <!-- Attendance Form -->
            <form id="attendance-form">
                @csrf
                <input type="hidden" id="user-latitude" name="latitude">
                <input type="hidden" id="user-longitude" name="longitude">
                <input type="hidden" id="selected-office-id" name="lokasi_kantor_id">
                
                <div class="d-grid gap-3">
                    <button type="submit" name="action" value="masuk" 
                            class="btn btn-primary btn-lg py-3 attendance-btn rounded-3" 
                            disabled>
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <span class="btn-text">Absen Masuk</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>

                    <button type="submit" name="action" value="pulang" 
                            class="btn btn-outline-primary btn-lg py-3 attendance-btn rounded-3"
                            disabled>
                        <i class="fas fa-sign-out-alt me-2"></i>
                        <span class="btn-text">Absen Pulang</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </div>
            </form>
            
            <!-- Office Hours Info -->
            <div class="mt-4 pt-3 border-top">
                <div class="row text-center">
                    <div class="col">
                        <small class="text-muted d-block mb-1">Jam Masuk</small>
                        <strong class="text-success h6">08:00</strong>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block mb-1">Jam Pulang</small>
                        <strong class="text-danger h6">17:00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #f77c00 100%);
    }

    .attendance-btn {
        position: relative;
        transition: all 0.3s ease;
        font-weight: 600;
        letter-spacing: 0.5px;
        transform: translateY(0);
    }

    .attendance-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .attendance-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .attendance-btn.loading .btn-text {
        opacity: 0.7;
    }

    .attendance-btn.loading .spinner-border {
        display: inline-block !important;
    }

    .btn-shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .attendance-btn:hover .btn-shine {
        left: 100%;
    }

    .card {
        transition: all 0.3s ease;
        border-radius: 15px !important;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }

    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .animate-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.6s ease-out;
    }

    .card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .card:nth-child(3) {
        animation-delay: 0.2s;
    }

    .card:nth-child(4) {
        animation-delay: 0.3s;
    }

    /* Alert Styles */
    .alert-info {
        background: linear-gradient(135deg, #cce7ff 0%, #e3f2fd 100%);
        border: 1px solid #b3d9ff;
        color: #0c5460;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1eddb 0%, #c3e6cb 100%);
        border: 1px solid #a3d9a3;
        color: #0f5132;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffdf7e;
        color: #664d03;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
        border: 1px solid #ea868f;
        color: #58151c;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }
        
        .btn-lg {
            padding: 12px 20px;
            font-size: 0.95rem;
        }
        
        .h3 {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update current time every second
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('current-time').textContent = timeString;
    }
    setInterval(updateCurrentTime, 1000);

    // Global variables
    let userLocation = null;
    let selectedOfficeLocation = null;

    // Alert functions
    function showAlert(type, message) {
        const alertEl = document.getElementById('location-alert');
        const messageEl = document.getElementById('location-message');
        
        alertEl.className = `alert d-block mb-3`;
        
        switch(type) {
            case 'success':
                alertEl.classList.add('alert-success');
                break;
            case 'error':
                alertEl.classList.add('alert-danger');
                break;
            case 'warning':
                alertEl.classList.add('alert-warning');
                break;
            default:
                alertEl.classList.add('alert-info');
        }
        
        messageEl.innerHTML = message;
    }

    function hideAlert() {
        document.getElementById('location-alert').classList.add('d-none');
    }

    // Button state functions
    function enableAttendanceButtons() {
        document.querySelectorAll('.attendance-btn').forEach(btn => {
            btn.disabled = false;
        });
    }

    function disableAttendanceButtons() {
        document.querySelectorAll('.attendance-btn').forEach(btn => {
            btn.disabled = true;
        });
    }

    function updateLocationStatus(message, type = 'info') {
        const statusEl = document.getElementById('location-status').querySelector('span');
        statusEl.textContent = message;
        statusEl.className = type === 'success' ? 'text-success' : type === 'error' ? 'text-danger' : 'text-warning';
    }

    // Get user location
    function getUserLocation() {
        if (!navigator.geolocation) {
            showAlert('error', 'Browser Anda tidak mendukung geolocation');
            updateLocationStatus('Geolocation tidak didukung', 'error');
            return;
        }
        
        showAlert('info', 'Mengambil lokasi Anda...');
        updateLocationStatus('Mencari lokasi...', 'warning');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                document.getElementById('user-latitude').value = userLocation.latitude;
                document.getElementById('user-longitude').value = userLocation.longitude;
                
                checkLocationRadius();
            },
            function(error) {
                let errorMessage = 'Gagal mendapatkan lokasi Anda. ';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Izin lokasi ditolak. Silakan aktifkan GPS dan izinkan akses lokasi.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Timeout saat mengambil lokasi.';
                        break;
                    default:
                        errorMessage += 'Terjadi kesalahan yang tidak diketahui.';
                }
                
                showAlert('error', errorMessage);
                updateLocationStatus('Gagal mendapatkan lokasi', 'error');
                disableAttendanceButtons();
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    }

    // Calculate distance using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    // Check location radius
    function checkLocationRadius() {
        if (!userLocation || !selectedOfficeLocation) {
            return;
        }
        
        const distance = calculateDistance(
            userLocation.latitude,
            userLocation.longitude,
            selectedOfficeLocation.latitude,
            selectedOfficeLocation.longitude
        );
        
        if (distance <= selectedOfficeLocation.radius_meter) {
            showAlert('success', `✅ Anda berada dalam radius kantor (${Math.round(distance)}m dari ${selectedOfficeLocation.nama_lokasi})`);
            updateLocationStatus(`Dalam radius (${Math.round(distance)}m)`, 'success');
            enableAttendanceButtons();
        } else {
            showAlert('warning', `⚠️ Anda berada di luar radius kantor. Jarak: ${Math.round(distance)}m (maks: ${selectedOfficeLocation.radius_meter}m)`);
            updateLocationStatus(`Di luar radius (${Math.round(distance)}m)`, 'error');
            disableAttendanceButtons();
        }
    }

    // Office location selection
    document.getElementById('office-location').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const officeId = this.value;
        
        if (officeId) {
            document.getElementById('location-name').textContent = selectedOption.dataset.nama;
            document.getElementById('location-address').textContent = selectedOption.dataset.alamat;
            document.getElementById('location-radius').textContent = `Radius: ${selectedOption.dataset.radius} meter`;
            document.getElementById('selected-location-info').classList.remove('d-none');
            document.getElementById('selected-office-id').value = officeId;
            
            selectedOfficeLocation = {
                id: parseInt(officeId),
                nama_lokasi: selectedOption.dataset.nama,
                alamat: selectedOption.dataset.alamat,
                latitude: parseFloat(selectedOption.dataset.latitude),
                longitude: parseFloat(selectedOption.dataset.longitude),
                radius_meter: parseInt(selectedOption.dataset.radius)
            };
            
            if (!selectedOfficeLocation.latitude || !selectedOfficeLocation.longitude) {
                showAlert('error', 'Data koordinat lokasi tidak lengkap');
                updateLocationStatus('Koordinat tidak lengkap', 'error');
                disableAttendanceButtons();
                return;
            }
            
            getUserLocation();
            
        } else {
            document.getElementById('selected-location-info').classList.add('d-none');
            document.getElementById('selected-office-id').value = '';
            hideAlert();
            updateLocationStatus('Pilih lokasi kantor', 'info');
            disableAttendanceButtons();
            selectedOfficeLocation = null;
        }
    });

    // Form submission
    document.getElementById('attendance-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!userLocation) {
            showAlert('error', 'Lokasi Anda belum terdeteksi');
            return;
        }
        
        if (!selectedOfficeLocation) {
            showAlert('error', 'Pilih lokasi kantor terlebih dahulu');
            return;
        }
        
        const distance = calculateDistance(
            userLocation.latitude,
            userLocation.longitude,
            selectedOfficeLocation.latitude,
            selectedOfficeLocation.longitude
        );
        
        if (distance > selectedOfficeLocation.radius_meter) {
            showAlert('error', `❌ Anda berada di luar radius kantor. Jarak: ${Math.round(distance)}m`);
            return;
        }
        
        const formData = new FormData(this);
        const action = e.submitter.value;
        formData.append('action', action);
        
        const submitBtn = e.submitter;
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        spinner.classList.remove('d-none');
        btnText.textContent = 'Memproses...';
        
        fetch('/pegawai/absen', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', '✅ ' + data.message);
                // Show success animation
                submitBtn.classList.add('btn-success');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert('error', '❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '❌ Terjadi kesalahan saat mengirim data absen');
        })
        .finally(() => {
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                spinner.classList.add('d-none');
                btnText.textContent = action === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
            }, 1000);
        });
    });
});
</script>
@endpush