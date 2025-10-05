{{-- resources/views/kepala/index.blade.php --}}
@extends('kepala.master')

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
                            <small class="text-muted">dari {{ $totalKaryawan }} karyawan</small>
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

    <!-- Charts Row -->
    <div class="row">
        <!-- Chart Pegawai Berdasarkan Jabatan -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">Pegawai Berdasarkan Jabatan</h5>
                    <small class="text-muted">Total: {{ $totalKaryawan }} orang</small>
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
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">Pegawai Berdasarkan Departemen</h5>
                    <small class="text-muted">Total: {{ $totalKaryawan }} orang</small>
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
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js configurations
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