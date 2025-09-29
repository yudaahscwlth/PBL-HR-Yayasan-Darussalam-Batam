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

    <script>
        $(document).ready(function () {
            // Saat "Select All" diklik
            $('.selectAll').on('change', function () {
                const $modal = $(this).closest('.modal');
                const isChecked = $(this).is(':checked');
                $modal.find('.permission-checkbox').prop('checked', isChecked);
            });

            // Saat salah satu checkbox permission diubah
            $('.permission-checkbox').on('change', function () {
                const $modal = $(this).closest('.modal');
                const total = $modal.find('.permission-checkbox').length;
                const checked = $modal.find('.permission-checkbox:checked').length;

                $modal.find('.selectAll').prop('checked', total === checked);
            });

            // Inisialisasi saat halaman dimuat
            $('.modal').each(function () {
                const $modal = $(this);
                const total = $modal.find('.permission-checkbox').length;
                const checked = $modal.find('.permission-checkbox:checked').length;

                $modal.find('.selectAll').prop('checked', total === checked);
            });
        });
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Kelola Roles</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="">Home</a></li>
                    <li class="breadcrumb-item active text-capitalize">
                        {{ ucwords(str_replace('/', ' / ', Request::path())) }}
                    </li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Roles</h5>
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                <div class="me-md-2 mb-2">
                                    <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                    </button>
                                </div>
                            </div>
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Nama Role</th>
                                        <th scope="col">Jumlah User</th>
                                        <th scope="col">Hak Akses</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($roles as $role )
                                        <tr>
                                            <td class="text-capitalize">{{ $role->name }}</td>
                                            <td>{{ $role->users->count() }}</td>
                                            <td>
                                                {{ $role->permissions->count() }}
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $role->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $role->id }}">
                                                    <i class="bi bi-trash"></i> hapus
                                                </button>
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
                    <h1 class="modal-title fs-5">Tambah role</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('role.store') }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="">Nama role</label>
                                    <input name="name" type="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <h6 class="text-capitalize fw-bold my-2">
                                        Administrator access
                                        <i class="bi bi-exclamation-circle-fill" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Akan memberikan semua akses"></i>
                                    </h6>
                                    <input type="checkbox" class="form-check-input selectAll">
                                    <label class="form-check-label" for="checkDefault">
                                        Select all
                                    </label>
                                </div>
                                @forelse($permissions as $module => $actions)
                                    <div class="col-6">
                                        <div class="fw-bold">
                                            {{ ucwords(str_replace('_', ' ', $module)) }}
                                        </div>
                                        <div class="d-flex flex-column">
                                            @foreach($actions as $action)
                                            <div>
                                                <input type="checkbox" name="permissions[]" class="form-check-input permission-checkbox" value="{{ $module . '.' . $action }}" >
                                                <label class="form-check-label text-capitalize" for="checkDefault">
                                                    {{ $action }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    belum ada data permission
                                @endforelse
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



    @foreach ($roles as $role => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Role</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('role.update',['id_role' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="">Nama role</label>
                                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$data->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <h6 class="text-capitalize fw-bold my-2">
                                            Administrator access
                                            <i class="bi bi-exclamation-circle-fill" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Akan memberikan semua akses"></i>
                                        </h6>
                                        <input type="checkbox" class="form-check-input selectAll">
                                        <label class="form-check-label" for="checkDefault">
                                            Select all
                                        </label>
                                    </div>
                                    @forelse($permissions as $module => $actions)
                                        <div class="col-6">
                                            <div class="fw-bold">
                                                {{ ucwords(str_replace('_', ' ', $module)) }}
                                            </div>
                                            <div class="d-flex flex-column">
                                                @foreach($actions as $action)
                                                <div>
                                                    <input type="checkbox" name="permissions[]" class="form-check-input permission-checkbox" value="{{ $module . '.' . $action }}"  {{ $data->permissions->pluck('name')->contains($module . '.' . $action) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-capitalize" for="checkDefault">
                                                        {{ $action }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        belum ada data permission
                                    @endforelse
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
                        <h1 class="modal-title fs-5">Hapus Role</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('role.destroy',['id_role' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <input type="hidden" name="id" id="hapusId">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger">menghapus role</span> {{ $data->name }}</span>
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
