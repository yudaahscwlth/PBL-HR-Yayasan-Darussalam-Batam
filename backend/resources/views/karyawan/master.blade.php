<!-- resources/views/layouts/master.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <title>@yield('title', 'Data Karyawan') | Yayasan Darussalam</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
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

  @include('layouts.secSidebar')
  @include('layouts.topbar')

  <div class="pc-container">
    <div class="pc-content">
      @include('layouts.breadcrumb') <!-- Memanggil breadcrumb dari file terpisah -->
      @yield('content') <!-- Konten halaman utama di sini -->
    </div>
  </div>

  @include('layouts.footer')
  @include('layouts.footer-js')

  @stack('scripts') <!-- Stack untuk menambahkan custom script per halaman -->
</body>
</html>
