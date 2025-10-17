@extends('html.html')

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/@jarstone/dselect/dist/css/dselect.css">
@endpush

@push('js')
    <script src="https://unpkg.com/@jarstone/dselect/dist/js/dselect.js"></script>
    <script>
        $(document).ready(function () {
            // Mendeklarasikan dan menginisialisasi variabel table dengan DataTable
            var table = $('.table').DataTable({
                order: [],
                info: true,
            });
    });
    </script>
    <script>
        $(document).ready(function() {
            $('.search-name').each(function() {
                var $this = $(this);

                // Terapkan dselect
                dselect(this);
            });
        });
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Kelola Departemen</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Departemen</h5>
                            @can('manajemen_departemen.create')
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
                                        <th scope="col">Nama departemen</th>
                                        <th scope="col">Nama kepala departemen</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataDepartemen as $data )
                                        <tr>
                                            <td class="text-capitalize">{{ $data->nama_departemen }}</td>
                                            <td class="text-capitalize">{{ $data->kepala->profilePribadi->nama_lengkap ?? 'Belum ada kepala departemen' }}</td>
                                            <td>
                                                @can('manajemen_departemen.update')
                                                <button class="btn btn-warning btn-sm mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endcan
                                                @can('manajemen_departemen.delete')
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
                    <form action="{{ route('departemen.store') }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="formFile" class="form-label">Kepala Departemen</label>
                                    <select name="id_kepala_departemen" class="form-select search-name @error('id_kepala_departemen') is-invalid @enderror" data-dselect-search="true">
                                        <option value="" disabled selected>Pilih Data</option>
                                        @foreach ($dataUser as $user)
                                            <option value="{{ $user->id }}">{{ $user->profilePribadi->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_kepala_departemen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="">Nama Departemen</label>
                                    <input name="nama_departemen" type="text" class="form-control @error('nama_departemen') is-invalid @enderror" value="{{ old('nama_departemen') }}" required>
                                    @error('nama_departemen')
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



    @foreach ($dataDepartemen as $index => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('departemen.update',['id_departemen' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                <div class="col-12">
                                    <label for="formFile" class="form-label">Kepala Departemen</label>
                                    <select name="id_kepala_departemen" class="form-select search-name @error('id_kepala_departemen') is-invalid @enderror" data-dselect-search="true">
                                        <option value="" disabled {{ $data->id_kepala_departemen ? '' : 'selected' }}>Pilih Data</option>
                                        @foreach ($dataUser as $user)
                                            <option value="{{ $user->id }}" {{ $data->id_kepala_departemen == $user->id ? 'selected' : '' }}>{{ $user->profilePribadi->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_kepala_departemen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="">Nama Departemen</label>
                                    <input name="nama_departemen" type="text" class="form-control @error('nama_departemen') is-invalid @enderror" value="{{ old('nama_departemen',$data->nama_departemen) }}" required>
                                    @error('nama_departemen')
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
                        <form action="{{ route('departemen.destroy',['id_departemen' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <input type="hidden" name="id" id="hapusId">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger">menghapus data</span> {{ $data->nama_departemen }}
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
