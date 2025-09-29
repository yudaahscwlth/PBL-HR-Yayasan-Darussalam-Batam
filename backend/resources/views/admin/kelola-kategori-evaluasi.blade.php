@extends('html.html')

@push('css')

@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tangkap semua modal yang memiliki editor
            const allModals = document.querySelectorAll('.modal');

            allModals.forEach(function (modal) {
                modal.addEventListener('shown.bs.modal', function () {
                const editorContainer = modal.querySelector('.quill-editor-default');
                const inputTarget = modal.querySelector('.quill-input-target');

                if (editorContainer && !editorContainer.classList.contains('ql-container')) {
                    const quill = new Quill(editorContainer, {
                    theme: 'snow'
                    });

                    // Isi awal editor (jika input sudah punya value)
                    if (inputTarget.value) {
                        quill.root.innerHTML = inputTarget.value;
                    }

                    // Sinkronisasi isi editor ke input
                    quill.on('text-change', function () {
                        inputTarget.value = quill.root.innerHTML;
                    });
                }
                });
            });
        });
    </script>

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
            <h1 class="text-capitalize">Kelola Kategori Evaluasi</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Kategori Evaluasi</h5>
                            @can('manajemen_kategori_evaluasi.create')
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
                                        <th scope="col">Nama</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataKategori as $data )
                                        <tr>
                                            <td class="text-capitalize">{!! $data->nama !!}</td>
                                            <td>
                                                @can('manajemen_kategori_evaluasi.update')
                                                <button class="btn btn-warning btn-sm mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endcan
                                                @can('manajemen_kategori_evaluasi.delete')
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
                    <form action="{{ route('kategori.evaluasi.store') }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="">Nama Kategori</label>
                                    <div class="quill-editor-default" style="height: 300px"></div>
                                    <input name="nama" type="hidden" class="form-control quill-input-target @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                                    @error('nama')
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



    @foreach ($dataKategori as $index => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('kategori.evaluasi.update',['id_kategori' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="">Nama Kategori</label>
                                        <div class="quill-editor-default" style="height: 300px"></div>
                                        <input name="nama" type="hidden" class="form-control quill-input-target @error('nama') is-invalid @enderror" value="{{ old('nama',$data->nama) }}" required>
                                        @error('nama')
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
                        <form action="{{ route('kategori.evaluasi.destroy',['id_kategori' => $data->id]) }}" method="post">
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
