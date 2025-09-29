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

    {{-- chart jumlah pegawai --}}
    @if (isset($jumlahPegawaiPerTahun)) {{--jika data yang dibutuhkan ada maka tampilkan--}}
        <script>
            const golongan = document.getElementById('rekapTahunanSDM');

            new Chart(golongan, {
                type: 'line',
                data: {
                    labels: {!! json_encode($jumlahPegawaiPerTahun->keys()) !!},
                    datasets: [{
                        label: 'Jumlah Pegawai Masuk',
                        data: {!! json_encode($jumlahPegawaiPerTahun->values()) !!},
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    aspectRatio: 2, // 1 artinya tinggi = lebar
                }
            });
        </script>
    @endif

    {{-- chart status --}}
    @if (isset($chartStatusDatasets, $chartStatusLabels))
        <script>
            const statusLabels = @json($chartStatusLabels);
            const statusDatasets = @json($chartStatusDatasets);
            const statusChart = document.getElementById('rekapStatusTahunan');

            new Chart(statusChart, {
                type: 'line',
                data: {
                    labels: statusLabels,
                    datasets: statusDatasets
                },
                options: {
                    responsive: true,
                    aspectRatio: 2, // 1 artinya tinggi = lebar
                }
            });
        </script>
    @endif
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Kelola Pegawai</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Pegawai</h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                    @canany([
                                        'manajemen_user.create',
                                        'manajemen_tenaga_pendidik_kepsek.create',
                                        'manajemen_tenaga_pendidik_all.create',
                                    ])
                                    <div class="me-md-2 mb-2 mb-md-0">
                                        <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                        </button>
                                    </div>
                                    @endcanany

                                    @canany([
                                        'manajemen_user.delete',
                                        'manajemen_tenaga_pendidik_kepsek.delete',
                                        'manajemen_tenaga_pendidik_all.delete',
                                    ])
                                    <div class="me-md-2 mb-2 mb-md-0">
                                        <button class="btn btn-danger" id="btnHapus">
                                            <i class="bi bi-trash"></i> Hapus Pilihan
                                        </button>
                                    </div>
                                    @endcanany
                                </div>
                                <a class="btn btn-main" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
                                    Filter <i class="bi bi-filter-right"></i>
                                </a>
                            </div>
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Jabatan</th>
                                        <th scope="col">Tanggal Masuk</th>
                                        <th scope="col">Tahun Pengabdian</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataPegawai as $index => $data )
                                        <tr>
                                            <td>{{ $data->id }}</td>
                                            <td>{{ $data->profilePribadi->nama_lengkap }}</td>
                                            <td>{{ $data->profilePekerjaan->jabatan->nama_jabatan }}</td>
                                            <td>{{ $data->profilePekerjaan->tanggal_masuk }}</td>
                                            @php
                                                $selisih = \Carbon\Carbon::parse($data->profilePekerjaan->tanggal_masuk)->diff(\Carbon\Carbon::now());

                                                // Ambil tahun dan bulan
                                                $tahun = $selisih->y;
                                                $bulan = $selisih->m;
                                            @endphp
                                            <td>{{ $tahun }} tahun {{ $bulan }} bulan</td>
                                            <td>{{ $data->email }}</td>
                                            <td>{{ $data->profilePekerjaan->status }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn rounded-3" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @canany([
                                                            'manajemen_user.read',
                                                            'manajemen_tenaga_pendidik_kepsek.read',
                                                            'manajemen_tenaga_pendidik_all.read',
                                                        ])
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('kelola.pegawai.edit.page',['id_pegawai' => $data->id]) }}">
                                                                <i class="bi bi-pencil-square"></i> Detail Profil
                                                            </a>
                                                        </li>
                                                        @endcanany
                                                        @can('manajemen_rekap_absensi.read')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('kelola.pegawai.rekap.absen.page',['id_pegawai' => $data->id]) }}">
                                                                    <i class="bi bi-calendar-check"></i>Rekap Absensi
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        @can('manajemen_rekap_cuti_pegawai.read')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('kelola.pegawai.rekap.cuti.page',['id_pegawai' => $data->id]) }}">
                                                                    <i class="bi bi-calendar-event"></i>Rekap Cuti
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        @can('manajemen_rekap_evaluasi.read')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('evaluasi.rekap.pegawai.page',['id_pegawai' => $data->id]) }}">
                                                                    <i class="bi bi-ui-checks"></i>Rekap Evaluasi
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty

                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if (isset($jumlahPegawaiPerTahun, $chartStatusDatasets, $chartStatusLabels))
                    @can('manajemen_user.read')
                        <div class="col-12">
                            <div class="card p-3">
                                <h5 class="card-title">Rekap Tahunan SDM</h5>
                                <canvas id="rekapTahunanSDM"></canvas>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card p-3">
                                <h5 class="card-title">Rekap Status Tahunan</h5>
                                <canvas id="rekapStatusTahunan"></canvas>
                            </div>
                        </div>
                    @endcan
                @endif
            </div>
        </section>

    </main><!-- End #main -->

    {{-- filter offcanvas --}}
    <div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Filter Tabel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form method="get" action="{{ route('kelola.pegawai.page') }}">
                @csrf @method('get')
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex justify-content-start align-items-start flex-wrap gap-3">
                            <div class="collapse-group">
                                <a class="text-dark" data-bs-toggle="collapse" href="#kecamatan" role="button">
                                    Kecamatan <i class="bi bi-chevron-compact-down"></i>
                                </a>
                                <div class="collapse {{ count(request('kecamatan', [])) ? 'show' : '' }}" id="kecamatan">
                                    @forelse ($dataKecamatan as $kecamatan )
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="kecamatan[]" value="{{ $kecamatan['name'] }}" {{ in_array($kecamatan['name'], request('kecamatan', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="checkDefault">
                                                {{ $kecamatan['name'] }}
                                            </label>
                                        </div>
                                    @empty
                                        Tidak Ada data
                                    @endforelse
                                </div>
                            </div>
                            <div class="collapse-group">
                                <a class="text-dark" data-bs-toggle="collapse" href="#golonganDarah" role="button">
                                    Golongan Darah <i class="bi bi-chevron-compact-down"></i>
                                </a>
                                <div class="collapse {{ count(request('golongan_darah', [])) ? 'show' : '' }}" id="golonganDarah">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="golongan_darah[]" value="a" {{ in_array('a', request('golongan_darah', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="checkDefault">
                                            A
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="golongan_darah[]" value="b" {{ in_array('b', request('golongan_darah', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="checkDefault">
                                            B
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="golongan_darah[]" value="ab"  {{ in_array('ab', request('golongan_darah', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="checkDefault">
                                            AB
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="golongan_darah[]" value="o"  {{ in_array('o', request('golongan_darah', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="checkDefault">
                                            O
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="collapse-group">
                                <a class="text-dark" data-bs-toggle="collapse" href="#rentangUsia" role="button">
                                    Rentang Usia <i class="bi bi-chevron-compact-down"></i>
                                </a>
                                <div class="collapse {{ count(request('usia', [])) ? 'show' : '' }}" id="rentangUsia">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="usia[]" value="18-25" id="usia1" {{ in_array('18-25', request('usia', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="usia1">
                                            18 - 25 tahun
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="usia[]" value="26-35" id="usia2" {{ in_array('26-35', request('usia', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="usia2">
                                            26 - 35 tahun
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="usia[]" value="36-45" id="usia3" {{ in_array('36-45', request('usia', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="usia3">
                                            36 - 45 tahun
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="usia[]" value="46-55" id="usia4" {{ in_array('46-55', request('usia', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="usia4">
                                            46 - 55 tahun
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="usia[]" value="56+" id="usia5" {{ in_array('56+', request('usia', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="usia5">
                                            56 tahun ke atas
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-sm btn-main w-100 d-flex justify-content-center gap-1 fs-6">
                                <span class="d-none d-lg-block">Telusuri</span>
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- tambah modal --}}
    <div class="modal fade" id="tambahModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Tambah Pegawai</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kelola.pegawai.store') }}" method="post" enctype="multipart/form-data">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-12">
                                    <label for="" class="form-label">Email</label>
                                    <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Masukkan email" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Password</label>
                                    <input name="password" type="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" placeholder="Masukkan password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Nama Lengkap</label>
                                    <input name="nama_lengkap" type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap" required>
                                    @error('nama_lengkap')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Nomor Induk Kependudukan</label>
                                    <input name="nomor_induk_kependudukan" type="text" class="form-control @error('nomor_induk_kependudukan') is-invalid @enderror" value="{{ old('nomor_induk_kependudukan') }}" placeholder="Masukkan NIK" required>
                                    @error('nomor_induk_kependudukan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Nomor Induk Karyawan</label>
                                    <input name="nomor_induk_karyawan" type="text" class="form-control @error('nomor_induk_karyawan') is-invalid @enderror" value="{{ old('nomor_induk_karyawan') }}" placeholder="Masukkan NIK Karyawan" required>
                                    @error('nomor_induk_karyawan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Tanggal Bergabung</label>
                                    <input name="tanggal_masuk" type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" value="{{ old('tanggal_masuk') }}" placeholder="Pilih tanggal bergabung" required>
                                    @error('tanggal_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Jabatan</label>
                                    <select name="jabatan" class="form-select @error('jabatan') is-invalid @enderror" required>
                                        @foreach ($dataJabatan as $jabatan )
                                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                        @endforeach
                                    </select>
                                    @error('jabatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Departemen</label>
                                    <select name="departemen" class="form-select @error('departemen') is-invalid @enderror" required>
                                        @foreach ($dataDepartemen as $departemen )
                                            <option value="{{ $departemen->id }}">{{ $departemen->nama_departemen }}</option>
                                        @endforeach
                                    </select>
                                    @error('departemen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Tempat Bekerja</label>
                                    <select name="tempat_kerja" class="form-select @error('tempat_kerja') is-invalid @enderror" required>
                                        @foreach ($dataTempatKerja as $tempatKerja )
                                            <option value="{{ $tempatKerja->id }}">{{ $tempatKerja->nama_tempat }}</option>
                                        @endforeach
                                    </select>
                                    @error('tempat_kerja')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Status</label>
                                    <select name="status_karyawan" class="form-select @error('status_karyawan') is-invalid @enderror" required>
                                        <option value="" disabled selected>Pilih status karyawan</option>
                                        <option value="aktif" {{ old('status_karyawan') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="nonaktif" {{ old('status_karyawan') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                        <option value="kontrak" {{ old('status_karyawan') == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                                        <option value="tetap" {{ old('status_karyawan') == 'tetap' ? 'selected' : '' }}>Tetap</option>
                                        <option value="magang" {{ old('status_karyawan') == 'magang' ? 'selected' : '' }}>Magang</option>
                                        <option value="honorer" {{ old('status_karyawan') == 'honorer' ? 'selected' : '' }}>Honorer</option>
                                        <option value="pensiun" {{ old('status_karyawan') == 'pensiun' ? 'selected' : '' }}>Pensiun</option>
                                        <option value="cuti" {{ old('status_karyawan') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                        <option value="skorsing" {{ old('status_karyawan') == 'skorsing' ? 'selected' : '' }}>Skorsing</option>
                                    </select>
                                    @error('status_karyawan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="" class="form-label">Role</label>
                                    <select name="roles" id=""  class="form-select @error('roles') is-invalid @enderror">
                                        @foreach($dataRoles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('roles')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
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
    <div class="modal fade" id="hapusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Hapus Pegawai</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kelola.pegawai.mass.delete') }}" method="post">
                        @csrf @method('delete')
                        <div class="container-fluid">
                            <input type="hidden" name="id" id="hapusId">
                            <h4 class="text-capitalize">
                                Apakah anda yakin ingin <span class="text-danger fw-bold">menghapus data</span> yang dipilih ?</span>
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
    @include('components.footer')
@endsection
