@include('layouts.head-css')
<!-- adminSidebar -->
<!-- [ Pre-loader ] End -->
 <!-- [ Sidebar Menu ] start -->
 <nav class="pc-sidebar">
  <div class="navbar-wrapper">
  <div class="m-header d-flex align-items-center">
  <a href="{{ route('kepala.index') }}" class="b-brand d-flex align-items-center text-decoration-none">
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
  <a href="{{route('kepala.index')}}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
    <span class="pc-mtext">Dashboard</span>
  </a>
</li>

<!-- Pengajuan -->
<li class="pc-item pc-caption">
  <label>Fitur</label>
  <i class="ti ti-hierarchy"></i>
</li>

<li class="pc-item">
  <a href="{{Route('kepala.listPengajuan')}}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-calendar-time"></i></span>
    <span class="pc-mtext">Pengajuan Cuti</span>
  </a>
</li>

<!-- REKAP PENILAIAN SDM -->
<li class="pc-item pc-caption">
  <label>Kinerja</label>
  <i class="ti ti-chart-bar"></i>
</li>
<li class="pc-item">
  <a href="{{Route('kepala.rekap.index')}}" class="pc-link">
     <span class="pc-micon"><i class="ti ti-report-analytics"></i></span>
    <span class="pc-mtext">Rekap Penilaian SDM</span>
  </a>
</li>

<!-- SISTEM -->
<li class="pc-item pc-caption">
  <label>Sistem</label>
  <i class="ti ti-settings"></i>
</li>
<li class="pc-item">
  <a href="{{ route('kepala.edit-profile') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-user"></i></span>
    <span class="pc-mtext">Profile</span>
  </a>
</li>

      </ul>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->