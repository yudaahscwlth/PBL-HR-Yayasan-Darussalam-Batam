@extends('html.html')

@push('css')
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/select/3.0.0/css/select.bootstrap5.css" rel="stylesheet">
    <style>
        /* Jika ingin warna teks tetap hitam, gunakan ini */
        .table.dataTable tbody tr.selected td {
            color: black !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/select/3.0.0/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/3.0.0/js/select.bootstrap5.js"></script>
    {{-- datatables --}}
    <script>
        $(document).ready(function () {
            // Mendeklarasikan dan menginisialisasi variabel table dengan DataTable
            var table = $('.table').DataTable({
                columnDefs: [
                    {
                        orderable: false,
                        render: DataTable.render.select(),
                        targets: 0
                    }
                ],
                order: [],
                select: {
                    style: 'os',
                    selector: 'td:first-child'
                },
                info: true,
            });

            // Menangani klik pada tombol hapus
            $('#btnHapus').on('click', function () {
                // Mendapatkan data dari baris yang dipilih
                var selectedRows = table.rows({ selected: true }).data();

                // Mengecek apakah ada baris yang dipilih
                if (selectedRows.length === 0) {
                    Swal.fire({
                        text: 'Tidak ada data yang dipilih',
                        icon: 'warning',
                        confirmButtonText:'OK',
                        showCloseButton: true,
                        timer: 2000,
                    })
                } else {
                    // Mengambil ID dari baris yang dipilih
                    var selectedIds = [];
                    selectedRows.each(function (rowData) {
                        console.log(rowData);
                        selectedIds.push(rowData[0]); // ID pegawai ada di kolom pertama (index 0)
                    });

                    // Menyimpan ID yang dipilih ke input hidden dalam form
                    $('#hapusId').val(selectedIds.join(','));

                    // Menampilkan modal hapus
                    $('#hapusModal').modal('show');
                }
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
            <h1 class="text-capitalize">Kelola Permission</h1>
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
                            <h5 class="card-title">Kelola Permission</h5>
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                <div class="me-md-2 mb-2">
                                    <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                    </button>
                                </div>
                                <div class="me-md-2 mb-2">
                                    <button class="btn btn-danger" id="btnHapus">
                                        <i class="bi bi-trash"></i> Hapus Pilihan
                                    </button>
                                </div>
                            </div>
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>No</th>
                                        <th scope="col">Nama Permission</th>
                                        <th scope="col">Jumlah User</th>
                                        <th scope="col">Role Terkait</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($permissions as $index => $permission )
                                        <tr>
                                            <td>{{ $permission->id }}</td>
                                            <td>{{ $index+1 }}</td>
                                            <td class="text-capitalize">{{ $permission->name }}</td>
                                            <td>{{ $permission->users->count() }}</td>
                                            <td>
                                                @forelse ($permission->roles as $role  )
                                                    <span class="badge text-bg-success">{{ $role->name }}</span>
                                                @empty
                                                    Tidak ada role yang memiliki hak akses ini
                                                @endforelse
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $permission->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
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
                    <form action="{{ route('permission.store') }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="">Nama Permission</label>
                                    <input name="name" type="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <div class="row d-flex align-items-center">
                                        <div class="col-12">
                                            <h6 class="text-capitalize fw-bold my-2">Jenis permissions</h6>
                                        </div>
                                        <div class="col-12">
                                            <input type="checkbox" class="form-check-input selectAll" >
                                            <label class="form-check-label" for="checkDefault">
                                                Select all
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        @foreach($actions as $action)
                                            <input type="checkbox" name="permissions[]" class="form-check-input permission-checkbox @error('permissions') is-invalid @enderror" value="{{old('permissions',$action)}}">
                                            <label class="form-check-label text-capitalize" for="checkDefault">
                                                {{ $action }}
                                            </label>
                                        @endforeach
                                        @error('permissions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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

    {{-- hapus modal --}}
    <div class="modal fade" id="hapusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Hapus Permission</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('permission.destroy') }}" method="post">
                        @csrf @method('delete')
                        <div class="container-fluid">
                            <input type="hidden" name="id" id="hapusId">
                            <h4 class="text-capitalize">
                                Apakah anda yakin ingin <span class="text-danger">menghapus data</span> yang dipilih ?</span>
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


    @foreach ($permissions as $permission => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit permission</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('permission.update',['id_permission' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="">Nama Permission</label>
                                        <input type="hidden" name="action" value="{{ explode('.',$data->name)[1] }}">
                                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',explode('.', $data->name)[0]) }}" required>
                                        @error('name')
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
    @endforeach

    @include('components.footer')
@endsection
