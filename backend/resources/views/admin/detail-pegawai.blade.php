@extends('admin.master')

@section('title', 'Edit Pegawai')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Pegawai</h4>
                    <div>
                        <a href="{{ route('pegawai.edit', $pegawai->id_pegawai) }}" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.karyawan') }}" 
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Foto Pegawai -->
                        <div class="col-md-3 text-center mb-4">
                            @if($pegawai2->foto)
                                <img src="{{ asset('uploads/pegawai/' . $pegawai2->foto) }}" 
                                     alt="Foto {{ $pegawai2->nama }}" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 250px;">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center text-white" 
                                     style="width: 200px; height: 250px;">
                                    <i class="fas fa-user fa-5x"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Data Pegawai -->
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Nama Lengkap</strong></td>
                                            <td>: {{ $pegawai->nama }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tempat, Tanggal Lahir</strong></td>
                                            <td>: {{ $pegawai->tempat_lahir }}, {{ \Carbon\Carbon::parse($pegawai->tanggal_lahir)->format('d F Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Umur</strong></td>
                                            <td>: {{ $umur }} tahun</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis Kelamin</strong></td>
                                            <td>: {{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. HP</strong></td>
                                            <td>: {{ $pegawai->no_hp }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>: {{ $pegawai->email }}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Jabatan</strong></td>
                                            <td>: {{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Departemen</strong></td>
                                            <td>: {{ $pegawai->departemen->nama_departemen ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Masuk</strong></td>
                                            <td>: {{ \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('d F Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Masa Kerja</strong></td>
                                            <td>: {{ $masaKerja }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jatah Cuti Tahunan</strong></td>
                                            <td>: {{ $pegawai->jatahtahunan ?? 0 }} hari</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Alamat -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <strong>Alamat:</strong>
                                    <p class="mt-2">{{ $pegawai->alamat }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection