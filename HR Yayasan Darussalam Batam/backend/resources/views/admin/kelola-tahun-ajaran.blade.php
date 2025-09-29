@extends('html.html')

@push('css')

@endpush

@push('js')
    <script>
        $(document).ready(function () {
            // Mendeklarasikan dan menginisialisasi variabel table dengan DataTable
            var table = $('.table').DataTable({
                order: [],
                info: true,
            });
    });
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Kelola Tahun Ajaran</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Tahun Ajaran</h5>
                            @can('manajemen_tahun_ajaran.create')
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                <div class="me-md-2 mb-2">
                                    <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                    </button>
                                </div>
                            </div>
                            @endcan
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Nama Tahun Ajaran</th>
                                        <th scope="col">Semester</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataTahunAjaran as $data )
                                        <tr>
                                            <td class="text-capitalize">{{ $data->nama }}</td>
                                            <td class="text-capitalize">{{ $data->semester }}</td>
                                            <td><span class="badge badge-sm @if ($data->is_aktif != 0) text-bg-success @else text-bg-danger @endif">{{ $data->is_aktif != 0 ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                            <td>
                                                @can('manajemen_tahun_ajaran.update')
                                                <button class="btn btn-warning btn-sm mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endcan
                                                @can('manajemen_tahun_ajaran.delete')
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $data->id }}">
                                                    <i class="bi bi-trash"></i> hapus
                                                </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    {{-- tambah modal --}}
    <div class="modal fade" id="tambahModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tahun.ajaran.store') }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="" class="form-label">Tahun Ajaran</label>
                                    <input name="nama" type="text" class="form-control @error('nama') is-invalid @enderror" placeholder="2025/2026" value="{{ old('nama') }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">Semester</label>
                                    <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                        <option value="ganjil">Ganjil</option>
                                        <option value="genap">Genap</option>
                                    </select>
                                    @error('semester')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="0">Tidak Aktif</option>
                                        <option value="1">True</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-main">Simpan</button>
                    </form>
                </div>
        </div>
        </div>
    </div>



    @foreach ($dataTahunAjaran as $index => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('tahun.ajaran.update',['id_tahun_ajaran' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="" class="form-label">Tahun Ajaran</label>
                                        <input name="nama" type="text" class="form-control @error('nama') is-invalid @enderror" placeholder="2025/2026" value="{{ old('nama',$data->nama) }}" required>
                                        @error('nama')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">Semester</label>
                                        <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                            <option value="ganjil" @if ($data->semester == 'ganjil') selected @endif>Ganjil</option>
                                            <option value="genap" @if ($data->semester == 'genap') selected @endif>Genap</option>
                                        </select>
                                        @error('semester')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="0" @if ($data->is_aktif == 0) selected @endif>Tidak Aktif</option>
                                            <option value="1" @if ($data->is_aktif == 1) selected @endif>Aktif</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                            </div>
                            </div>
                        </div>
                    <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-main">Simpan</button>
                        </form>
                    </div>
            </div>
            </div>
        </div>

    {{-- hapus modal --}}
        <div class="modal fade" id="hapusModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Hapus data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('tahun.ajaran.destroy',['id_tahun_ajaran' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <input type="hidden" name="id" id="hapusId">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger">menghapus data</span> {{ $data->nama }}
                                </h4>
                            </div>
                        </div>
                    <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-main">Simpan</button>
                        </form>
                    </div>
            </div>
            </div>
        </div>
    @endforeach

    @include('components.footer')
@endsection
