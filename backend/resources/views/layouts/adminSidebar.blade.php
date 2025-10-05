@include('layouts.head-css')
<!-- adminSidebar -->
<!-- [ Pre-loader ] End -->
 <!-- [ Sidebar Menu ] start -->
 <nav class="pc-sidebar">
  <div class="navbar-wrapper">
  <div class="m-header d-flex align-items-center">
  <a href="{{ url('/dashboard') }}" class="b-brand d-flex align-items-center text-decoration-none">
    <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid me-2" style="height: 40px;" alt="Logo">

    <div class="vr me-3" style="height: 40px; background-color:black;"></div>

    <div class="text-start">
      <h6 class="mb-0 fw-bold text-uppercase" style="line-height: 1;">HR Yayasan</h6>
      <h6 class="mb-0 fw-bold text-uppercase" style="line-height: 1;">Darussalam</h6>
    </div>
  </a>
</div>


    <div class="navbar-content">
      <ul class="pc-navbar">
       <!-- DASHBOARD -->
<li class="pc-item">
  <a href="{{ route('admin.index') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
    <span class="pc-mtext">Dashboard</span>
  </a>
</li>

<!-- MANAJEMEN HR -->
<li class="pc-item pc-caption">
  <label>Manajemen HR</label>
  <i class="ti ti-users"></i>
</li>
<li class="pc-item">
  <a href="{{ route('admin.karyawan') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-users"></i></span>
    <span class="pc-mtext">Data Pegawai</span>
  </a>
</li>
<li class="pc-item">
  <a href="{{ route('admin.absensi') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-clock"></i></span>
    <span class="pc-mtext">Absensi</span>
  </a>
</li>
<li class="pc-item">
  <a href="{{route('admin.user.index')}}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-user"></i></span>
    <span class="pc-mtext">Data User</span>
  </a>
</li>
@php  
        $user = Auth::user();
        $pegawai = $user->pegawai; 
        $nama_jabatan = $pegawai->jabatan->nama_jabatan;
        $nama_departemen = $pegawai->departemen->nama_departemen;
@endphp
@if($nama_jabatan == 'Kepala' && $nama_departemen == 'Sumber Daya Manusia')
<li class="pc-item">
  <a href="{{ route('admin.listPengajuan') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-calendar-time"></i></span>
    <span class="pc-mtext">Data Pengajuan Cuti</span>
  </a>
</li>
@endif

<!-- Pengajuan -->
<li class="pc-item pc-caption">
  <label>Fitur</label>
  <i class="ti ti-hierarchy"></i>
</li>

<li class="pc-item">
  <a href="{{ route('admin.cuti.index') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-calendar-time"></i></span>
    <span class="pc-mtext">Pengajuan Cuti</span>
  </a>
</li>
<li class="pc-item">
  <a href="{{ route('admin.RiwayatAbsensi') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-clock"></i></span>
    <span class="pc-mtext">Riwayat Absensi</span>
  </a>
</li>
<!-- STRUKTUR ORGANISASI -->
{{-- <li class="pc-item pc-caption">
  <label>Struktur Organisasi</label>
  <i class="ti ti-hierarchy"></i>
</li> --}}
{{-- <li class="pc-item">
  <a href="{{ url('/jabatan') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-hierarchy"></i></span>
    <span class="pc-mtext">Jabatan & Departemen</span>
  </a>
</li> --}}

<li class="pc-item pc-caption">
  <label>Kinerja</label>
  <i class="ti ti-hierarchy"></i>
</li>
<li class="pc-item">
  <a href="{{ route('admin.kuisioner.index') }}" class="pc-link">
     <span class="pc-micon"><i class="ti ti-clipboard-check"></i></span>
    <span class="pc-mtext">Kelola Kuis</span>
  </a>
</li>
@php
$currentPeriode = \App\Models\PeriodePenilaian::where('status', 'aktif')->first();
@endphp

@if($currentPeriode)
<li class="pc-item">
  <a href="{{ route('periode.index', $currentPeriode->id) }}" class="pc-link">
     <span class="pc-micon"><i class="ti ti-edit"></i></span>
    <span class="pc-mtext">Kelola Periode</span>
  </a>
</li>
@endif

{{-- <!-- KEUANGAN -->
<li class="pc-item pc-caption">
  <label>Keuangan</label>
  <i class="ti ti-currency-dollar"></i>
</li> --}}
{{-- <li class="pc-item">
  <a href="{{ url('/gaji') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-currency-dollar"></i></span>
    <span class="pc-mtext">Gaji & Slip Gaji</span>
  </a>
</li> --}}
<!-- <li class="pc-item">
  <a href="{{ url('/pinjaman') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-cash-banknote"></i></span>
    <span class="pc-mtext">Pinjaman Karyawan</span>
  </a>
</li> -->

<!-- SISTEM -->
<li class="pc-item pc-caption">
  <label>Sistem</label>
  <i class="ti ti-settings"></i>
</li>
<!-- <li class="pc-item">
  <a href="{{ url('/laporan') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-report"></i></span>
    <span class="pc-mtext">Laporan</span>
  </a>
</li> -->
<!-- <li class="pc-item">
  <a href="{{ url('/users') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-settings"></i></span>
    <span class="pc-mtext">Manajemen User</span>
  </a>
</li> -->
<li class="pc-item">
  <a href="{{ route('admin.edit-profile') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-tools"></i></span>
    <span class="pc-mtext">Profile</span>
  </a>
</li>
<li class="pc-item">
  <a href="{{ route('admin.LokasiKantor.index') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-building"></i></span>
    <span class="pc-mtext">Lokasi Kantor</span>
  </a>
</li>



<li class="pc-item">
  <a href="{{ route('admin.log-sistem.index') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-file-text"></i></span>
    <span class="pc-mtext">Log Sistem</span>
  </a>
</li>
</nav>
<!-- [ Sidebar Menu ] end -->
