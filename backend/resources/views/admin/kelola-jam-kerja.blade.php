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
            <h1 class="text-capitalize">Kelola Jam Kerja</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Jam Kerja {{ $dataJamKerja->first()->jabatan->nama_jabatan ?? 'Tidak diketahui' }}</h5>
                            @can('manajemen_jam_kerja.create')
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
                                        <th scope="col">Hari</th>
                                        <th scope="col">Jam Masuk</th>
                                        <th scope="col">Jam Pulang</th>
                                        <th scope="col">Libur</th>
                                        <th scope="col">Keterangan</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataJamKerja as $data )
                                        <tr>
                                            <td class="text-capitalize">{{ $data->hari }}</td>
                                            <td class="text-capitalize">{{ $data->jam_masuk }}</td>
                                            <td class="text-capitalize">{{ $data->jam_pulang }}</td>
                                            <td class="text-capitalize">
                                                <span class="badge badge-sm {{ $data->is_libur == 0 ? 'text-bg-danger' : 'text-bg-success' }} ">
                                                    @if ($data->is_libur == 0)
                                                        False
                                                    @else
                                                        True
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="text-capitalize">{{ $data->keterangan }}</td>
                                            <td>
                                                @can('manajemen_jam_kerja.update')
                                                <button class="btn btn-warning btn-sm mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endcan
                                                @can('manajemen_jam_kerja.delete')
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
                    <form action="{{ route('jabatan.jam.kerja.store',['id_jabatan' => $id_jabatan]) }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="">Hari</label>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="senin" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Senin
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="selasa" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Selasa
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="rabu" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Rabu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="kamis" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Kamis
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="jumat" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Jumat
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="sabtu" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Sabtu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="hari[]" class="form-check-input" type="checkbox" value="minggu" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            Minggu
                                        </label>
                                    </div>
                                    @error('hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">Jam Masuk</label>
                                    <input type="time" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk') }}" required>
                                    @error('jam_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">Jam Pulang</label>
                                    <input type="time" name="jam_pulang" class="form-control @error('jam_pulang') is-invalid @enderror" value="{{ old('jam_pulang') }}" required>
                                    @error('jam_pulang')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">Apakah Libur?</label>
                                    <select name="is_libur" class="form-select @error('is_libur') is-invalid @enderror" required>
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                    @error('is_libur')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">keterangan</label>
                                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"></textarea>
                                    @error('keterangan')
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



    @foreach ($dataJamKerja as $index => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('jabatan.jam.kerja.update',['id_jabatan' => $id_jabatan,'id_jam_kerja' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="" class="form-label">Hari</label>
                                        <input type="text" class="form-control @error('hari') is-invalid @enderror" value="{{ $data->hari }}" disabled>
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">Jam Masuk</label>
                                        <input type="time" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk',$data->jam_masuk) }}" required>
                                        @error('jam_masuk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">Jam Pulang</label>
                                        <input type="time" name="jam_pulang" class="form-control @error('jam_pulang') is-invalid @enderror" value="{{ old('jam_pulang',$data->jam_pulang) }}" required>
                                        @error('jam_pulang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">Apakah Libur?</label>
                                        <select name="is_libur" class="form-select @error('is_libur') is-invalid @enderror" required>
                                            <option value="0" {{ $data->is_libur == 0 ? 'selected' : ''}}>Tidak</option>
                                            <option value="1"{{ $data->is_libur == 1 ? 'selected' : ''}}>Ya</option>
                                        </select>
                                        @error('is_libur')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">keterangan</label>
                                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan',$data->keterangan) }}</textarea>
                                        @error('keterangan')
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
                        <form action="{{ route('jabatan.jam.kerja.destroy',['id_jabatan' => $id_jabatan, 'id_jam_kerja' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <input type="hidden" name="id" id="hapusId">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger">menghapus data {{ $data->hari }}</span>
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
