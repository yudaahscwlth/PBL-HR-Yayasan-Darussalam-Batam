@extends('admin.master')


@section('title', 'Home | Struktur Jabatan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Struktur Jabatan</li>
@endsection

@section('content')
<div class="container py-5">
  <h3 class="text-center mb-5">Struktur Organisasi Yayasan Sekolah</h3>

  <div class="d-flex justify-content-center mb-4">
    <div class="text-center">
      <div class="p-3 bg-primary text-white rounded shadow">
        <h5 class="mb-0">Direktur Utama</h5>
        <small>Andi Pratama</small>
      </div>
    </div>
  </div>

  <div class="row justify-content-center mb-4">
    <div class="col-md-3 text-center">
      <div class="p-3 bg-success text-white rounded shadow">
        <h6 class="mb-0">Kepala HRD</h6>
        <small>Siti Nurhaliza</small>
      </div>
    </div>
    <div class="col-md-3 text-center">
      <div class="p-3 bg-warning text-dark rounded shadow">
        <h6 class="mb-0">Kepala Keuangan</h6>
        <small>Budi Santoso</small>
      </div>
    </div>
    <div class="col-md-3 text-center">
      <div class="p-3 bg-danger text-white rounded shadow">
        <h6 class="mb-0">Kepala Operasional</h6>
        <small>Rina Melati</small>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-3 text-center">
      <div class="p-2 bg-light border rounded shadow-sm">
        <strong>Staff HRD</strong><br>
        Dewi Anggraini
      </div>
    </div>
    <div class="col-md-3 text-center">
      <div class="p-2 bg-light border rounded shadow-sm">
        <strong>Staff Keuangan</strong><br>
        Rama Wijaya
      </div>
    </div>
    <div class="col-md-3 text-center">
      <div class="p-2 bg-light border rounded shadow-sm">
        <strong>Staff Operasional</strong><br>
        Aliyah Fitri
      </div>
    </div>
  </div>
</div>
@endsection
