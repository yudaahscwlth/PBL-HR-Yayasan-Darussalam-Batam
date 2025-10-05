<!-- resources/views/layouts/master.blade.php -->
 
<!DOCTYPE html>
<html lang="en">
<head>
  <title>@yield('title') | Yayasan Darussalam</title>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="{{ asset('assets/css/plugins/dataTables.bootstrap5.min.css') }}">
  @include('layouts.head-css')
  <style>
    .pc-footer p {
        text-align: left !important;
    }
    
    .pc-footer ul {
        text-align: right;
    }
  </style>
</head>
<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
  <!-- Pre-loader -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  @include('layouts.KepalaSidebar')
  @include('layouts.topbar', ['pegawai' => $pegawai])


  <div class="pc-container">
    <div class="pc-content">
      @include('layouts.breadcrumb') <!-- Memanggil breadcrumb dari file terpisah -->
      @yield('content') <!-- Konten halaman utama di sini -->
    </div>
  </div>

  @include('layouts.footer')
  @include('layouts.footer-js')

  @stack('scripts') <!-- Stack untuk menambahkan custom script per halaman -->
  @stack('styles')
</body>
</html>
<!-- Di bagian head untuk CSS (opsional) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- Di bagian sebelum closing body -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Script SweetAlert yang sudah saya buat di atas
</script>