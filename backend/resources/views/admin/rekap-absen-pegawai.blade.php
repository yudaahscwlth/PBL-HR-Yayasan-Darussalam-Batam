@extends('html.html')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/styles/index.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- dselect --}}
    <script>
        $(document).ready(function() {
            $('#search-name').select2({
                dropdownParent: $('#tambahModal')
            });
        });
    </script>

{{-- chart bar --}}
    <script>
        const ctx = document.getElementById('myChart');

        const labels = @json($statusCounts->keys());
        const data = @json($statusCounts->values());

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    axis: 'y',
                    label: 'Rekap Absensi',
                    data: data,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',    // hadir - hijau
                        'rgba(255, 205, 86, 0.2)',    // terlambat - kuning
                        'rgba(255, 159, 64, 0.2)',    // sakit - orange
                        'rgba(54, 162, 235, 0.2)',    // cuti - biru
                        'rgba(255, 99, 132, 0.2)'     // alpa - merah
                    ],
                    borderColor: [
                        'rgb(75, 192, 192)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 159, 64)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>


{{-- DataTable --}}
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
            <h1 class="text-capitalize">Absensi</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                {{-- nama Pegawai --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="my-3">
                                <h1 class="text-capitalize fw-bold">Rekap Absensi</h1>
                                <hr>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $dataUser->profilePribadi->foto ? asset('storage/'.$dataUser->profilePribadi->foto) : asset('assets/img/profile-img.jpg') }}" alt="" class="rounded-circle" height="120" width="120">
                                    <h2 class="text-capitalize fw-semibold">{{ $dataUser->profilePribadi->nama_lengkap }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Horizontal chart --}}
                <div class="col-lg-6">
                    <div class="card p-3">
                        <h5 class="card-title">Kehadiran</h5>
                        <canvas id="myChart"></canvas>
                    </div>
                </div>

                {{-- tabel pengajuan cuti --}}
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Rekap Absensi</h5>
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                @can('manajemen_rekap_absensi.create')
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
                                            <td>{{ $data->tanggal->format('d/m/Y')  }}</td>
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
                                                <div class="d-flex flex-wrap gap-1 text-nowrap">
                                                    @if (is_null($data->deleted_at))
                                                        @can('manajemen_rekap_absensi.update')
                                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                                <i class="bi bi-pencil-square"></i> Edit
                                                            </button>
                                                        @endcan
                                                    @endif
                                                    @can('manajemen_rekap_absensi.delete')
                                                        @if (is_null($data->deleted_at))
                                                            {{-- Tombol Hapus --}}
                                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $data->id }}">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        @else
                                                            {{-- Tombol Restore --}}
                                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#restoreModal{{ $data->id }}">
                                                                <i class="bi bi-arrow-clockwise"></i> Restore
                                                            </button>
                                                        @endif
                                                    @endcan
                                                    <a class="btn btn-sm btn-secondary" href="{{ route('absensi.log.page',['id_absensi' => $data->id]) }}">
                                                        <i class="bi bi-clock-history"></i> Log Absensi
                                                    </a>
                                                </div>
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
                    <form action="{{ route('kelola.pegawai.rekap.absen.store',['id_pegawai' => $dataUser->id]) }}" method="post" enctype="multipart/form-data">
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
                                        <option value="hadir">Hadir</option>
                                        <option value="terlambat">Terlambat</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="cuti">Cuti</option>
                                        <option value="alpa">Alpa</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="" class="form-label">File Pendukung</label>
                                    <input type="file" name="file_pendukung" class="form-control @error('file_pendukung') is-invalid @enderror">
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

    @foreach ($dataAbsensi as $index => $data )
        {{-- editModal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Absensi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('rekap.absensi.update',['id_absensi' => $data->id]) }}" method="post" enctype="multipart/form-data">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Jam Masuk</label>
                                        <input name="check_in" type="datetime-local" class="form-control @error('check_in') is-invalid @enderror" value="{{ old('check_in',$data->check_in ?? '') }}">
                                        @error('check_in')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Jam Keluar</label>
                                        <input name="check_out" type="datetime-local" class="form-control @error('check_out') is-invalid @enderror" value="{{ old('check_out',$data->check_out ?? '') }}">
                                        @error('check_out')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Latitude Masuk</label>
                                        <input name="latitude_in" type="text" class="form-control @error('latitude_in') is-invalid @enderror" value="{{ old('latitude_in',$data->latitude_in) }}">
                                        @error('latitude_in')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Longitude Masuk</label>
                                        <input name="longitude_in" type="text" class="form-control @error('longitude_in') is-invalid @enderror" value="{{ old('latitude_in',$data->longitude_in) }}">
                                        @error('longitude_in')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Latitude Keluar</label>
                                        <input name="latitude_out" type="text" class="form-control @error('latitude_out') is-invalid @enderror" value="{{ old('latitude_out',$data->latitude_out ?? '') }}">
                                        @error('latitude_out')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Longitude Keluar</label>
                                        <input name="longitude_out" type="text" class="form-control @error('longitude_out') is-invalid @enderror" value="{{ old('longitude_out',$data->longitude_out ?? '') }}">
                                        @error('longitude_out')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Status Absen</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="hadir" {{ $data->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="sakit" {{ $data->status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="cuti" {{ $data->status == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                            <option value="terlambat" {{ $data->status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                            <option value="alpa" {{ $data->status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="form-label">File Pendukung</label>
                                        <input type="file" name="file_pendukung" class="form-control @error('file_pendukung') is-invalid @enderror">
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

        {{-- hapus modal --}}
        <div class="modal fade" id="hapusModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Hapus Absensi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('rekap.absensi.delete',['id_absensi' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger fw-bold">menghapus absensi {{ $data->user->profilePribadi->nama_lengkap }}/{{ $data->tanggal->format('d-m- Y') }}</span>
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

        {{-- restore modal --}}
        <div class="modal fade" id="restoreModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Pulihkan Absensi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('rekap.absensi.restore',['id_absensi' => $data->id]) }}" method="post">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-success fw-bold">memulihkan absensi {{ $data->user->profilePribadi->nama_lengkap }}/{{ $data->tanggal->format('d-m- Y') }}</span>
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
