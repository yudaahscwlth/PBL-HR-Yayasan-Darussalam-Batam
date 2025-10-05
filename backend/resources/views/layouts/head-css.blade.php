<!-- [Google Font] Family -->
<link rel="icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">

<!-- Google Font -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">

<!-- Tabler Icons -->
<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">

<!-- Feather Icons -->
<link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">

<!-- Material Icons -->
<link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

<!-- Template CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
<link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}">

<style>
    /* Mengubah warna teks dan latar belakang pagination */
    div.dataTables_wrapper div.dataTables_paginate ul.pagination {
        background-color: #f8f9fa;  /* Mengatur latar belakang pagination */
        color: #495057;  /* Mengatur warna teks */
    }

    /* Mengubah warna saat hover pada item pagination */
    div.dataTables_wrapper div.dataTables_paginate ul.pagination li a:hover {
        background-color: #0d6efd;  /* Mengubah warna latar belakang saat hover */
        color: #fff;  /* Mengubah warna teks saat hover */
    }

    /* Mengubah warna pagination aktif */
    div.dataTables_wrapper div.dataTables_paginate ul.pagination li.active a {
        background-color: #0d6efd;  /* Latar belakang aktif */
        color: #fff;  /* Warna teks aktif */
    }
</style>