@extends('html.html')

@push('css')
    <style>
        .status-container {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .status {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .status-box {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }
        .deleted  { background-color: #f8d7da; }  /* Oranye */
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                info: true,
                order:[],
                dom: '<"row"<"col-sm-6 d-flex justify-content-center justify-content-sm-start mb-2 mb-sm-0"l><"col-sm-6 d-flex justify-content-center justify-content-sm-end"f>>rt<"row"<"col-sm-6 mt-0"i><"col-sm-6 mt-2"p>>'
            });
        });
    </script>

    {{-- autofill input latitude dan longitude --}}
    <script>
        var latitudeInputs = document.getElementsByClassName('latitude');
        var longitudeInputs = document.getElementsByClassName('longitude');

        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            });
        }else{
            Swal.fire({
                text: 'Geolocation tidak didukung oleh browser ini.',
                icon: 'warning',
                confirmButtonText:'OK',
                showCloseButton: true,
                timer: 5000,
            })
        }

        function successCallback(position) {
            for (let i = 0; i < latitudeInputs.length; i++) {
                latitudeInputs[i].value = position.coords.latitude;
                longitudeInputs[i].value = position.coords.longitude;
            }
        }


        function errorCallback(error){
            Swal.fire({
                title: 'Lokasi gagal didapatkan',
                html: 'Pastikan GPS Anda aktif dan browser mengizinkan akses lokasi. <br><a class="text-info" href="https://support.google.com/chrome/answer/142065" target="_blank">Cara mengaktifkan lokasi di browser</a>',
                icon: 'error',
                confirmButtonText:'OK',
                showCloseButton: true,
                // timer: 5000,
            }).then(() => {
                location.reload(); // Supaya bisa coba akses lokasi ulang
            });
        }
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Absensi Pribadi</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                {{-- tabel pengajuan cuti --}}
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Rekap Absensi Pribadi</h5>
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                @can('rekap_absensi_pribadi.create')
                                    <div class="me-md-2 mb-2">
                                        <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                        </button>
                                    </div>
                                @endcan
                            </div>
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Check In</th>
                                        <th scope="col">Check Out</th>
                                        <th scope="col">Presensi</th>
                                        <th scope="col">Keterangan</th>
                                        <th scope="col">File Pendukung</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataAbsensi as $index => $data)
                                        <tr class="{{ $data->deleted_at ? 'table-danger' : '' }}">
                                            <td>{{ $data->tanggal->translatedFormat('d F Y')  }}</td>
                                            <td>{{ $data->check_in }}</td>
                                            <td>{{ $data->check_out }}</td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'hadir' => 'text-bg-success',
                                                        'cuti' => 'text-bg-primary',
                                                        'alpa' => 'text-bg-danger',
                                                        'sakit' => 'text-bg-warning',
                                                        'terlambat' => 'text-bg-warning',
                                                    ][$data->status] ?? 'text-bg-secondary';
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $data->status }}</span>
                                            </td>
                                            <td>{{ $data->keterangan ?? '-' }}</td>
                                            <td>
                                                @if (!empty($data->file_pendukung))
                                                        <a href="{{ asset('storage/' . $data->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                            <i class="bi bi-eye"></i> Buka
                                                        </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-secondary" href="{{ route('absensi.log.page',['id_absensi' => $data->id]) }}">
                                                    <i class="bi bi-clock-history"></i> Log Absensi
                                                </a>
                                            </td>
                                        </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                            <div class="status-container">
                                <div class="status">
                                    <div class="status-box deleted"></div>
                                    <span class="text-capitalize">Data telah dihapus</span>
                                </div>
                            </div>
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
                    <h1 class="modal-title fs-5">Tambah Absensi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('rekap.absensi.pribadi.store') }}" method="post" enctype="multipart/form-data">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <input type="hidden" name="latitude" class="latitude">
                            <input type="hidden" name="longitude" class="longitude">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="exampleFormControlInput1" class="form-label">Tanggal Mulai</label>
                                    <input name="tanggal" type="date" class="form-control @error('tanggal') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="exampleFormControlInput1" class="form-label">Durasi Hari</label>
                                    <input name="durasi_hari" type="number" min="0" class="form-control @error('durasi_hari') is-invalid @enderror" placeholder="Jumlah hari absen (0 = hari ini saja)" required>
                                    @error('durasi_hari')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="exampleFormControlInput1" class="form-label">Status Absensi</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="sakit">Sakit</option>
                                        <option value="cuti">Cuti</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">File Pendukung</label>
                                    <input type="file" name="file_pendukung" class="form-control @error('file_pendukung') is-invalid @enderror" required>
                                    @error('file_pendukung')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">Keterangan Pendukung</label>
                                    <textarea type="keterangan" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Dapat dikosongkan jika tidak ada">{{ old('keterangan') }}</textarea>
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

    @include('components.footer')
@endsection
