@extends('html.html')

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
        document.addEventListener('DOMContentLoaded', function () {
            @foreach ($dataTempatKerja as $index => $data)
                var map{{ $index }} = L.map('map-{{ $index }}').setView([{{ $data->latitude }}, {{ $data->longitude }}], 16);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map{{ $index }});

                L.marker([{{ $data->latitude }}, {{ $data->longitude }}]).addTo(map{{ $index }})
            @endforeach
        });
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Kelola Kantor</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Kantor</h5>
                            @can('manajemen_tempat_kerja.create')
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                <div class="me-md-2 mb-2">
                                    <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                    </button>
                                </div>
                                <div class="me-md-2 mb-2">
                                    <a class="btn btn-primary" href="https://support.google.com/maps/answer/18539?hl=en&co=GENIE.Platform%3DDesktop" target="_blank" >
                                        <i class="bi bi-question-circle"></i> Latitude & Longitude
                                    </a>
                                </div>
                            </div>
                            @endcan
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Latitude</th>
                                        <th scope="col">Longitude</th>
                                        <th scope="col">Map</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataTempatKerja as $index => $data )
                                        <tr>
                                            <td class="text-capitalize">{{ $index+1 }}</td>
                                            <td class="text-capitalize">{{ $data->nama_tempat }}</td>
                                            <td class="text-capitalize">{{ $data->latitude }}</td>
                                            <td class="text-capitalize">{{ $data->longitude }}</td>
                                            <td>
                                                <div id="map-{{ $index }}" style="height: 180px; width: 180px;"></div>
                                            </td>
                                            <td>
                                                @can('manajemen_tempat_kerja.update')
                                                <button class="btn btn-warning btn-sm mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endcan
                                                @can('manajemen_tempat_kerja.delete')
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
                    <form action="{{ route('kantor.store') }}" method="post">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="">Nama</label>
                                    <input name="nama_tempat" type="text" class="form-control @error('nama_tempat') is-invalid @enderror" value="{{ old('nama_tempat') }}" required>
                                    @error('nama_tempat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="">Latitude</label>
                                    <input name="latitude" type='text' class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" required>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="">Longitude</label>
                                    <input name="longitude" type="text" class="form-control @error('nama_platform') is-invalid @enderror" value="{{ old('longitude') }}" required>
                                    @error('longitude')
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



    @foreach ($dataTempatKerja as $index => $data )
    {{-- edit modal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('kantor.update',['id_tempat_kerja' => $data->id]) }}" method="POST">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                    <label for="">Nama</label>
                                    <input name="nama_tempat" type="text" class="form-control @error('nama_tempat') is-invalid @enderror" value="{{ old('nama_tempat',$data->nama_tempat) }}" required>
                                    @error('nama_tempat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="">Latitude</label>
                                    <input name="latitude" type='text' class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude',$data->latitude) }}" required>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="">Longitude</label>
                                    <input name="longitude" type="text" class="form-control @error('nama_platform') is-invalid @enderror" value="{{ old('longitude',$data->longitude) }}" required>
                                    @error('longitude')
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
                        <form action="{{ route('kantor.destroy',['id_tempat_kerja' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <input type="hidden" name="id" id="hapusId">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger">menghapus data</span> {{ $data->nama_tempat }}
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
