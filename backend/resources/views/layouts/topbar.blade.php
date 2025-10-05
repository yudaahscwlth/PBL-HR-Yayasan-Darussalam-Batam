<!-- [ Header Topbar ] start -->
<header class="pc-header">
  <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
<div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <!-- ======= Menu collapse Icon ===== -->
    <li class="pc-h-item pc-sidebar-collapse">
      <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="dropdown pc-h-item d-inline-flex d-md-none">
      <a
        class="pc-head-link dropdown-toggle arrow-none m-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <!-- <i class="ti ti-search"></i> -->
      </a>
      <!-- <div class="dropdown-menu pc-h-dropdown drp-search">
        <form class="px-3">
          <div class="form-group mb-0 d-flex align-items-center">
            <i data-feather="search"></i>
            <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . .">
          </div>
        </form>
      </div>
    </li>
    <li class="pc-h-item d-none d-md-inline-flex">
      <form class="header-search">
        <i data-feather="search" class="icon-search"></i>
        <input type="search" class="form-control" placeholder="Search here. . .">
      </form>
    </li>
  </ul> -->
</div>
<!-- [Mobile Media Block end] -->
<div class="ms-auto">
  <ul class="list-unstyled">
    <li class="dropdown pc-h-item header-user-profile">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        data-bs-auto-close="outside"
        aria-expanded="false"
      >
        <img src="{{ asset('uploads/pegawai/' . $pegawai->foto) }}" alt="user-image" class="user-profile-circle">
        <span>{{$pegawai->nama}}</span>
      </a>
      <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header">
          <div class="d-flex mb-1">
            <div class="flex-shrink-0">
              <img src="{{ asset('uploads/pegawai/' . $pegawai->foto) }}" alt="user-image" class="user-profile-circle">
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="mb-1">{{$pegawai->nama}}</h6>
              <span>{{$nama_departemen}}</span>
            </div>
            <a href="#!" class="pc-head-link bg-transparent"><i class="ti ti-power text-danger"></i></a>
          </div>
        </div>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="drp-tab-2" role="tabpanel">
            <a href="#!" class="dropdown-item">
              <i class="ti ti-user"></i>
              <span>Account Settings</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline" id="logoutForm">
              @csrf
              <a href="javascript:void(0)" onclick="confirmLogout()" class="dropdown-item">
                  <i class="ti ti-power"></i>
                  <span>Logout</span>
              </a>
          </form>
          </div>
        </div>
      </div>
    </li>
  </ul>
</div>
</div>
</header>
<!-- [ Header ] end -->

<style>
/* CSS untuk foto profil lingkaran yang konsisten */
.user-profile-circle {
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
    border: 2px solid #dee2e6 !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
    background-color: #f8f9fa !important;
    transition: border-color 0.3s ease !important;
}

.user-profile-circle:hover {
    border-color: #007bff !important;
}

/* Override semua styling lain */
.user-avtar,
.user-avtar.wid-35,
.dropdown-header .user-avtar {
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
    border: 2px solid #dee2e6 !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
    background-color: #f8f9fa !important;
}

/* Styling untuk header user profile */
.header-user-profile .pc-head-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    border-radius: 20px;
    transition: background-color 0.3s ease;
}

.header-user-profile .pc-head-link:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.header-user-profile span {
    font-weight: 500;
    color: #495057;
}

.dropdown-header {
    padding: 1rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.dropdown-header h6 {
    color: #212529;
    font-weight: 600;
}

.dropdown-header .flex-grow-1 span {
    color: #6c757d;
    font-size: 0.875rem;
}
</style>

<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

<script>

function confirmLogout() {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success ms-3',
            cancelButton: 'btn btn-danger ms-3 '
        },
        buttonsStyling: false
    });
    
    swalWithBootstrapButtons
        .fire({
            title: 'Keluar dari sistem?',
            text: "Anda akan keluar dari sistem Yayasan Darussalam",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        })
        .then((result) => {
            if (result.isConfirmed) {
                // Submit the logout form
                document.getElementById('logoutForm').submit();
            }
        });
}
</script>