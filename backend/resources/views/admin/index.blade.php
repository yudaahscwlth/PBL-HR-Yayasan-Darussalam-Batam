{{-- resources/views/admin/index.blade.php --}}
@extends('admin.master')

@section('title', 'Home | Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <!-- Card Karyawan Masuk Hari Ini -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1 fw-normal">Masuk Hari Ini</h6>
                            <h3 class="mb-0 text-success fw-bold">{{ $masukHariIni }}</h3>
                            <small class="text-muted">dari {{ $totalKaryawanAktif }} karyawan</small>
                        </div>
                        <div class="text-success opacity-75">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Karyawan Cuti -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1 fw-normal">Cuti Hari Ini</h6>
                            <h3 class="mb-0 text-warning fw-bold">{{ $cutiHariIni }}</h3>
                            <small class="text-muted">karyawan</small>
                        </div>
                        <div class="text-warning opacity-75">
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Terlambat -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1 fw-normal">Terlambat</h6>
                            <h3 class="mb-0 text-danger fw-bold">{{ $terlambat }}</h3>
                            <small class="text-muted">karyawan</small>
                        </div>
                        <div class="text-danger opacity-75">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Tidak Masuk -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1 fw-normal">Tidak Masuk</h6>
                            <h3 class="mb-0 text-secondary fw-bold">{{ $tidakMasuk }}</h3>
                            <small class="text-muted">karyawan</small>
                        </div>
                        <div class="text-secondary opacity-75">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Attendance Row -->
    <div class="row">
        <!-- Chart Pegawai Berdasarkan Jabatan -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">Pegawai Berdasarkan Jabatan</h5>
                    <small class="text-muted">Total: {{ $totalKaryawanAktif}} orang</small>
                </div>
                <div class="card-body">
                    <div id="jabatan-chart" style="height: 300px;"></div>
                    
                    <!-- Detail List Jabatan -->
                    <div class="mt-3" style="max-height: 200px; overflow-y: auto;">
                        @foreach($jabatanData as $index => $jabatan)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2" 
                                     style="width: 10px; height: 10px; background-color: {{ ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'][$index % 8] }};"></div>
                                <span class="fw-medium">{{ $jabatan['nama'] }}</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary rounded-pill">{{ $jabatan['total'] }}</span>
                                <small class="text-muted d-block">({{ $jabatan['percentage'] }}%)</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Pegawai Berdasarkan Departemen -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">Pegawai Berdasarkan Departemen</h5>
                    <small class="text-muted">Total: {{ $totalKaryawanAktif }} orang</small>
                </div>
                <div class="card-body">
                    <div id="departemen-chart" style="height: 300px;"></div>
                    
                    <!-- Detail List Departemen -->
                    <div class="mt-3" style="max-height: 200px; overflow-y: auto;">
                        @foreach($departemenData as $index => $departemen)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2" 
                                     style="width: 10px; height: 10px; background-color: {{ ['#e74c3c', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'][$index % 8] }};"></div>
                                <span class="fw-medium">{{ $departemen['nama'] }}</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success rounded-pill">{{ $departemen['total'] }}</span>
                                <small class="text-muted d-block">({{ $departemen['percentage'] }}%)</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Absen dengan Multiple Lokasi -->
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
    .attendance-btn {
        position: relative;
        transition: all 0.3s ease;
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

    #current-time {
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 10px;
    }

    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

// Geolocation and Attendance functionality
let userLocation = null;
let selectedOfficeLocation = null;

// Helper functions
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
    
    messageEl.innerHTML = `${message}`;
}

function hideAlert() {
    document.getElementById('location-alert').classList.add('d-none');
}

function enableAttendanceButtons() {
    const buttons = document.querySelectorAll('.attendance-btn');
    buttons.forEach(btn => {
        btn.disabled = false;
    });
}

function disableAttendanceButtons() {
    const buttons = document.querySelectorAll('.attendance-btn');
    buttons.forEach(btn => {
        btn.disabled = true;
    });
}

// Get user's current location
function getUserLocation() {
    if (!navigator.geolocation) {
        showAlert('error', 'Browser Anda tidak mendukung geolocation');
        return;
    }
    
    showAlert('info', 'Mengambil lokasi Anda...');
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            userLocation = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            
            console.log('User location:', userLocation);
            
            // Update hidden form fields
            document.getElementById('user-latitude').value = userLocation.latitude;
            document.getElementById('user-longitude').value = userLocation.longitude;
            
            // Check if user is within office radius
            checkLocationRadius();
        },
        function(error) {
            console.error('Geolocation error:', error);
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
            disableAttendanceButtons();
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

// Calculate distance between two coordinates (Haversine formula)
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI/180;
    const φ2 = lat2 * Math.PI/180;
    const Δφ = (lat2-lat1) * Math.PI/180;
    const Δλ = (lon2-lon1) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // Distance in meters
}

// Check if user is within office radius
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
    
    console.log('Distance to office:', distance, 'meters');
    console.log('Office radius:', selectedOfficeLocation.radius_meter, 'meters');
    
    if (distance <= selectedOfficeLocation.radius_meter) {
        showAlert('success', `Anda berada dalam radius kantor (${Math.round(distance)}m dari ${selectedOfficeLocation.nama_lokasi})`);
        enableAttendanceButtons();
    } else {
        showAlert('warning', `Anda berada di luar radius kantor. Jarak: ${Math.round(distance)}m (maks: ${selectedOfficeLocation.radius_meter}m)`);
        disableAttendanceButtons();
    }
}

// Office location selection handler
document.getElementById('office-location').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const officeId = this.value;
    
    if (officeId) {
        // Show selected location info
        document.getElementById('location-name').textContent = selectedOption.dataset.nama;
        document.getElementById('location-address').textContent = selectedOption.dataset.alamat;
        document.getElementById('location-radius').textContent = `Radius: ${selectedOption.dataset.radius} meter`;
        document.getElementById('selected-location-info').classList.remove('d-none');
        
        // Set selected office ID
        document.getElementById('selected-office-id').value = officeId;
        
        // Get office location data from select option (simplified approach)
        selectedOfficeLocation = {
            id: parseInt(officeId),
            nama_lokasi: selectedOption.dataset.nama,
            alamat: selectedOption.dataset.alamat,
            latitude: parseFloat(selectedOption.dataset.latitude),
            longitude: parseFloat(selectedOption.dataset.longitude),
            radius_meter: parseInt(selectedOption.dataset.radius)
        };
        
        console.log('Selected office location:', selectedOfficeLocation);
        
        // Validate office location data
        if (!selectedOfficeLocation.latitude || !selectedOfficeLocation.longitude) {
            showAlert('error', 'Data koordinat lokasi tidak lengkap');
            disableAttendanceButtons();
            return;
        }
        
        // Get user location
        getUserLocation();
        
    } else {
        document.getElementById('selected-location-info').classList.add('d-none');
        document.getElementById('selected-office-id').value = '';
        hideAlert();
        disableAttendanceButtons();
        selectedOfficeLocation = null;
    }
});

// Attendance form submission
document.getElementById('attendance-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate required data
    if (!userLocation) {
        showAlert('error', 'Lokasi Anda belum terdeteksi');
        return;
    }
    
    if (!selectedOfficeLocation) {
        showAlert('error', 'Pilih lokasi kantor terlebih dahulu');
        return;
    }
    
    // Check location again before submission
    const distance = calculateDistance(
        userLocation.latitude,
        userLocation.longitude,
        selectedOfficeLocation.latitude,
        selectedOfficeLocation.longitude
    );
    
    if (distance > selectedOfficeLocation.radius_meter) {
        showAlert('error', `Anda berada di luar radius kantor. Jarak: ${Math.round(distance)}m`);
        return;
    }
    
    const formData = new FormData(this);
    const action = e.submitter.value;
    formData.append('action', action);
    
    const submitBtn = e.submitter;
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    spinner.classList.remove('d-none');
    btnText.textContent = 'Memproses...';
    
    fetch('/hrd/absen', {
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
            showAlert('success', data.message);
            // Optionally refresh page or update UI
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mengirim data absen');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
        spinner.classList.add('d-none');
        btnText.textContent = action === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
    });
});
  // Chart.js configurations tetap sama...
    const chartColors = [
        '#3498db', '#2ecc71', '#f1c40f', '#e74c3c', 
        '#9b59b6', '#1abc9c', '#e67e22', '#34495e'
    ];
    
    // Chart Jabatan
    const jabatanData = @json($jabatanData);
    if (jabatanData.length > 0) {
        const jabatanCtx = document.createElement('canvas');
        document.getElementById('jabatan-chart').appendChild(jabatanCtx);
        
        new Chart(jabatanCtx, {
            type: 'doughnut',
            data: {
                labels: jabatanData.map(item => item.nama),
                datasets: [{
                    data: jabatanData.map(item => item.total),
                    backgroundColor: chartColors.slice(0, jabatanData.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' orang (' + 
                                       jabatanData[context.dataIndex].percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Chart Departemen
    const departemenData = @json($departemenData);
    if (departemenData.length > 0) {
        const departemenCtx = document.createElement('canvas');
        document.getElementById('departemen-chart').appendChild(departemenCtx);
        
        new Chart(departemenCtx, {
            type: 'doughnut',
            data: {
                labels: departemenData.map(item => item.nama),
                datasets: [{
                    data: departemenData.map(item => item.total),
                    backgroundColor: ['#e74c3c', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'].slice(0, departemenData.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' orang (' + 
                                       departemenData[context.dataIndex].percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush