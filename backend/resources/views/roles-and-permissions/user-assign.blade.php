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
            <h1 class="text-capitalize">Assign user</h1>
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
                            <h5 class="card-title">Assign user</h5>
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Jumlah Hak Akses</th>
                                        <th scope="col">Jumlah Hak Akses Via Roles</th>
                                        <th scope="col">Jumlah Roles</th>
                                        <th scope="col">Roles</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $index => $user)
                                        <tr>
                                            <td class="text-capitalize">{{ $index+1 }}</td>
                                            <td class="text-capitalize">{{ $user->profilePribadi->nama_lengkap }}</td>
                                            <td>{{ $user->permissions->count() }}</td>
                                            <td>{{ $user->getPermissionsViaRoles()->count() }}</td>
                                            <td>
                                                {{ $user->roles->count() }}
                                            </td>
                                            <td>
                                                @forelse ($user->roles as $role  )
                                                    <span class="badge text-bg-success">{{ $role->name }}</span>
                                                @empty
                                                    Tidak ada role yang dimiliki user ini
                                                @endforelse
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editPermissionModal{{ $user->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit permissions
                                                </button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editRolesModal{{ $user->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit Roles
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



    @foreach ($users as $user => $data )
    {{-- edit permission modal --}}
        <div class="modal fade" id="editPermissionModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Permission</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user.assign.permissions.update',['id' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="">Nama</label>
                                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$data->name) }}" disabled>
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

    {{-- edit role modal --}}
        <div class="modal fade" id="editRolesModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Roles</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user.assign.roles.update',['id' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="">Nama</label>
                                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$data->name) }}" disabled>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="row d-flex align-items-center">
                                            <h6 class="text-capitalize fw-bold my-2">Roles</h6>
                                            <div>
                                                <input type="checkbox" class="form-check-input selectAll">
                                                <label class="form-check-label" for="checkDefault">
                                                    Select all
                                                </label>
                                            </div>
                                            @foreach($roles as $role)
                                                <div>
                                                    <input type="checkbox" name="roles[]" class="form-check-input permission-checkbox" value="{{ $role->name }}"  {{ $data->roles->contains($role->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-capitalize" for="checkDefault">
                                                        {{ $role->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
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
    @endforeach

    @include('components.footer')
@endsection
