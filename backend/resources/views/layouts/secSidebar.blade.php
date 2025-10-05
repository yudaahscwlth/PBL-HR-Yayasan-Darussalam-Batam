@include('layouts.head-css')

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
        <li class="pc-item pc-caption">
          <label>Umum</label>
          <i class="ti ti-dashboard"></i>
        </li>
        <li class="pc-item">
          <a href="{{ route('karyawan.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>

        <!-- MANAJEMEN DIRI -->
        <li class="pc-item pc-caption">
          <label>Manajemen Diri</label>
          <i class="ti ti-user-circle"></i>
        </li>
        <li class="pc-item">
          <a href="{{ route('karyawan.absensi') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-clock"></i></span>
            <span class="pc-mtext">Riwayat Absensi</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="{{ route('pegawai.cuti.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-calendar-time"></i></span>
            <span class="pc-mtext">Pengajuan Cuti</span>
          </a>
        </li>

        <!-- EVALUASI & SURVEY -->
        <li class="pc-item pc-caption">
          <label>Evaluasi & Survey</label>
          <i class="ti ti-clipboard-check"></i>
        </li>
        <li class="pc-item">
          <a href="{{ route('kuisioner.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-forms"></i></span>
            <span class="pc-mtext">Kuisioner</span>
          </a>
        </li>

        <!-- PENGATURAN -->
        <li class="pc-item pc-caption">
          <label>Pengaturan</label>
          <i class="ti ti-settings"></i>
        </li>
        <li class="pc-item">
          <a href="{{ route('karyawan.edit-profil') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-tools"></i></span>
            <span class="pc-mtext">Profile</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->