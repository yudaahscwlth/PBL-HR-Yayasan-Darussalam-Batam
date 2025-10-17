@extends('html.html')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/styles/index.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/styles/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#search-name').select2({
                dropdownParent: $('#tambahModal')
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                info: true,
                order: [],
                dom: '<"row"<"col-sm-6 d-flex justify-content-center justify-content-sm-start mb-2 mb-sm-0"l><"col-sm-6 d-flex justify-content-center justify-content-sm-end"f>>rt<"row"<"col-sm-6 mt-0"i><"col-sm-6 mt-2"p>>'
            });
        });
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Rekap Cuti</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                {{-- nama Pegawai --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="my-3">
                                <h1 class="text-capitalize fw-bold">Rekap Cuti Pegawai</h1>
                                <hr>
                                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                                    <img src="{{ $dataUser->profilePribadi->foto ? asset('storage/'.$dataUser->profilePribadi->foto) : asset('assets/img/profile-img.jpg') }}" alt="" class="rounded-circle" height="120" width="120">
                                    <h2 class="text-capitalize fw-semibold">{{ $dataUser->profilePribadi->nama_lengkap }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- tabel Rekap cuti --}}
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Rekap Cuti</h5>
                            @can('manajemen_rekap_cuti_pegawai.create')
                                <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                    <div class="me-md-2 mb-2">
                                        <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                        </button>
                                    </div>
                                </div>
                            @endcan
                            <div class="table-responsive">
                                <table class="table table-striped table-hover border table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Tanggal Pengajuan</th>
                                            <th scope="col">Nama</th>
                                            <th scope="col">Tipe Pengajuan</th>
                                            <th scope="col">Durasi</th>
                                            <th scope="col">Alasan Pendukung</th>
                                            <th scope="col">File Pendukung</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Komentar</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dataCuti as $index => $data)
                                            <tr>
                                                <td>{{ $data->created_at->format('d/m/Y') }}</td>
                                                <td>{{ $data->user->profilePribadi->nama_lengkap }}</td>
                                                <td>{{ $data->tipe_cuti }}</td>
                                                <td>{{ $data->tanggal_mulai->format('d M') }} - {{ $data->tanggal_selesai->format('d M') }}</td>
                                                <td>{{ $data->alasan_pendukung ?? 'Tidak ada alasan pendukung' }}</td>
                                                <td>
                                                    @if (!empty($data->file_pendukung))
                                                        <a href="{{ asset('storage/' . $data->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                            <i class="bi bi-eye"></i> Buka
                                                        </a>
                                                    @else
                                                        Tidak ada file pendukung yang ditambahkan
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge text-wrap
                                                        {{ str_contains($data->status_pengajuan, 'menunggu tinjauan') ? 'text-bg-warning' :
                                                        (str_contains($data->status_pengajuan, 'disetujui') ? 'text-bg-success' :
                                                        (str_contains($data->status_pengajuan, 'ditolak') ? 'text-bg-danger' : 'text-bg-warning')) }}">
                                                        {{ $data->status_pengajuan }}
                                                    </span>
                                                </td>
                                                <td>{{ $data->komentar ?? 'Tidak ada komentar yang ditambahkan'}}</td>
                                                <td>
                                                    @can('manajemen_rekap_cuti_pegawai.update')
                                                        <button class="btn btn-sm btn-warning text-nowrap mb-2"  data-bs-toggle="modal" data-bs-target="#editModal{{ $data->id }}">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </button>
                                                    @endcan
                                                    @can('manajemen_rekap_cuti_pegawai.delete')
                                                        <button class="btn btn-sm btn-danger text-nowrap"  data-bs-toggle="modal" data-bs-target="#hapusModal{{ $data->id }}">
                                                            <i class="bi bi-trash"></i> Hapus
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

            </div>
        </section>

    </main><!-- End #main -->

    {{-- tambah modal --}}
    <div class="modal fade" id="tambahModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Tambah Rekap</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kelola.pegawai.rekap.cuti.store',['id_pegawai' => $dataUser->id]) }}" method="post" enctype="multipart/form-data">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-6">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input name="tanggal_mulai" type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input name="tanggal_selesai" type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Tipe Cuti</label>
                                    <select name="tipe_cuti" class="form-select @error('tipe_cuti') is-invalid @enderror" required>
                                        <option disabled selected>-- Pilih Tipe Cuti --</option>
                                        <option value="cuti tahunan">Cuti Tahunan</option>
                                        <option value="cuti melahirkan">Cuti Melahirkan</option>
                                        <option value="cuti nikah">Cuti Nikah</option>
                                        <option value="cuti kematian">Cuti Kematian</option>
                                        <option value="cuti bersama">Cuti Bersama</option>
                                        <option value="cuti pemotongan gaji">Cuti Pemotongan Gaji</option>
                                        <option value="cuti lainnya">Cuti Lainnya</option>
                                    </select>
                                    @error('tipe_cuti')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">File Pendukung</label>
                                    <input name="file_pendukung" type="file" class="form-control @error('file_pendukung') is-invalid @enderror">
                                    @error('file_pendukung')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alasan Cuti</label>
                                    <textarea name="alasan_pendukung" class="form-control @error('alasan_pendukung') is-invalid @enderror" placeholder="Isi dengan alasan jika ada"></textarea>
                                    @error('alasan_pendukung')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Verifikasi</label>
                                    <select name="status_pengajuan" class="form-select @error('status_pengajuan') is-invalid @enderror" required>
                                        <option disabled selected>-- Pilih Status Pengajuan --</option>
                                        <option value="ditinjau kepala sekolah">Ditinjau Kepala Sekolah</option>
                                        <option value="disetujui kepala sekolah">Disetujui Kepala Sekolah</option>
                                        <option value="disetujui kepala sekolah menunggu tinjauan dirpen">Disetujui Kepala Sekolah (Menunggu Dirpen)</option>
                                        <option value="ditolak kepala sekolah">Ditolak Kepala Sekolah</option>
                                        <option value="ditinjau hrd">Ditinjau HRD</option>
                                        <option value="disetujui hrd">Disetujui HRD</option>
                                        <option value="disetujui hrd menunggu tinjauan dirpen">Disetujui HRD (Menunggu Dirpen)</option>
                                        <option value="ditolak hrd">Ditolak HRD</option>
                                        <option value="ditinjau kepala hrd">Ditinjau Kepala HRD</option>
                                        <option value="disetujui kepala hrd">Disetujui Kepala HRD</option>
                                        <option value="disetujui kepala hrd menunggu tinjauan dirpen">Disetujui Kepala HRD (Menunggu Dirpen)</option>
                                        <option value="ditolak kepala hrd">Ditolak Kepala HRD</option>
                                        <option value="ditinjau dirpen">Ditinjau Dirpen</option>
                                        <option value="disetujui dirpen">Disetujui Dirpen</option>
                                        <option value="ditolak dirpen">Ditolak Dirpen</option>
                                    </select>
                                    @error('status_pengajuan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Komentar</label>
                                    <textarea name="komentar" class="form-control @error('komentar') is-invalid @enderror" placeholder="Isi dengan komentar jika ada"></textarea>
                                    @error('komentar')
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

    @foreach ($dataCuti as $index => $data )
        {{-- editModal --}}
        <div class="modal fade" id="editModal{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Rekap</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('kelola.pegawai.rekap.cuti.update',['id_pengajuan' => $data->id]) }}" method="post" enctype="multipart/form-data">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Tanggal Pengajuan</label>
                                        <input name="tanggal_pengajuan" type="date" class="form-control @error('tanggal_pengajuan') is-invalid @enderror" value="{{ $data->created_at->format('Y-m-d') }}" disabled>
                                        @error('tanggal_pengajuan')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Tanggal Mulai</label>
                                        <input name="tanggal_mulai" type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ $data->tanggal_mulai->format('Y-m-d') }}" required>
                                        @error('tanggal_mulai')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Tanggal Selesai</label>
                                        <input name="tanggal_selesai" type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ $data->tanggal_selesai->format('Y-m-d') }}" required>
                                        @error('tanggal_selesai')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Tipe Cuti</label>
                                        <select name="tipe_cuti" class="form-select @error('tipe_cuti') is-invalid @enderror" required>
                                            <option value="cuti tahunan" {{ $data->tipe_cuti == 'cuti tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                                            <option value="cuti melahirkan" {{ $data->tipe_cuti == 'cuti melahirkan' ? 'selected' : '' }}>Cuti Melahirkan</option>
                                            <option value="cuti nikah" {{ $data->tipe_cuti == 'cuti nikah' ? 'selected' : '' }}>Cuti Nikah</option>
                                            <option value="cuti kematian" {{ $data->tipe_cuti == 'cuti kematian' ? 'selected' : '' }}>Cuti Kematian</option>
                                            <option value="cuti bersama" {{ $data->tipe_cuti == 'cuti bersama' ? 'selected' : '' }}>Cuti Bersama</option>
                                            <option value="cuti pemotongan gaji" {{ $data->tipe_cuti == 'cuti pemotongan gaji' ? 'selected' : '' }}>Cuti Pemotongan Gaji</option>
                                            <option value="cuti lainnya" {{ $data->tipe_cuti == 'cuti lainnya' ? 'selected' : '' }}>Cuti Lainnya</option>
                                        </select>
                                        @error('tipe_cuti')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">File Pendukung</label>
                                        <input name="file_pendukung" type="file" class="form-control @error('file_pendukung') is-invalid @enderror">
                                        @error('file_pendukung')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        @if (!empty($data->file_pendukung))
                                        <div class="d-flex flex-column">

                                            <label class="form-label">Lihat File</label>
                                            <a href="{{ asset('storage/' . $data->file_pendukung) }}" target="_blank" class="btn btn-main">
                                                <i class="bi bi-eye"></i> Buka
                                            </a>
                                        </div>
                                        @else
                                            Tidak ada file pendukung yang ditambahkan !
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Alasan Cuti</label>
                                        <textarea name="alasan_pendukung" class="form-control @error('alasan_pendukung') is-invalid @enderror">{{ $data->alasan_pendukung }}</textarea>
                                        @error('alasan_pendukung')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Verifikasi</label>
                                        <select name="status_pengajuan" class="form-select @error('status_pengajuan') is-invalid @enderror" required>
                                            <option value="ditinjau kepala sekolah" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditinjau kepala sekolah' ? 'selected' : '' }}>Ditinjau Kepala Sekolah</option>
                                            <option value="disetujui kepala sekolah" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui kepala sekolah' ? 'selected' : '' }}>Disetujui Kepala Sekolah</option>
                                            <option value="disetujui kepala sekolah menunggu tinjauan dirpen" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui kepala sekolah menunggu tinjauan dirpen' ? 'selected' : '' }}>Disetujui Kepala Sekolah (Menunggu Dirpen)</option>
                                            <option value="ditolak kepala sekolah" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditolak kepala sekolah' ? 'selected' : '' }}>Ditolak Kepala Sekolah</option>
                                            <option value="ditinjau hrd" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditinjau hrd' ? 'selected' : '' }}>Ditinjau HRD</option>
                                            <option value="disetujui hrd" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui hrd' ? 'selected' : '' }}>Disetujui HRD</option>
                                            <option value="disetujui hrd menunggu tinjauan dirpen" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui hrd menunggu tinjauan dirpen' ? 'selected' : '' }}>Disetujui HRD (Menunggu Dirpen)</option>
                                            <option value="ditolak hrd" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditolak hrd' ? 'selected' : '' }}>Ditolak HRD</option>
                                            <option value="ditinjau kepala hrd" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditinjau kepala hrd' ? 'selected' : '' }}>Ditinjau Kepala HRD</option>
                                            <option value="disetujui kepala hrd" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui kepala hrd' ? 'selected' : '' }}>Disetujui Kepala HRD</option>
                                            <option value="disetujui kepala hrd menunggu tinjauan dirpen" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui kepala hrd menunggu tinjauan dirpen' ? 'selected' : '' }}>Disetujui Kepala HRD (Menunggu Dirpen)</option>
                                            <option value="ditolak kepala hrd" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditolak kepala hrd' ? 'selected' : '' }}>Ditolak Kepala HRD</option>
                                            <option value="ditinjau dirpen" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditinjau dirpen' ? 'selected' : '' }}>Ditinjau Dirpen</option>
                                            <option value="disetujui dirpen" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'disetujui dirpen' ? 'selected' : '' }}>Disetujui Dirpen</option>
                                            <option value="ditolak dirpen" {{ old('status_pengajuan', $data->status_pengajuan ?? '') == 'ditolak dirpen' ? 'selected' : '' }}>Ditolak Dirpen</option>
                                        </select>
                                        @error('status_pengajuan')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Komentar</label>
                                        <textarea name="komentar" class="form-control @error('komentar') is-invalid @enderror" placeholder="Isi dengan komentar jika ada">{{ $data->komentar }}</textarea>
                                        @error('komentar')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
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
                        <form action="{{ route('kelola.pegawai.rekap.cuti.destroy',['id_pengajuan' => $data->id]) }}" method="post">
                            @csrf @method('delete')
                            <div class="container-fluid">
                                <h4 class="text-capitalize">
                                    Apakah anda yakin ingin <span class="text-danger fw-bold">menghapus Rekap Cuti {{ $data->created_at }}?</span>
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
