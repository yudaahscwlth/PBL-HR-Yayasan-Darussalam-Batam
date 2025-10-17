@extends('html.html')

@push('css')

@endpush

@push('js')
    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                info: true,
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
            <h1 class="text-capitalize">Pengajuan Cuti</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                {{-- tabel verifikasi cuti --}}
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Ajukan Cuti</h5>
                            <div class="d-flex flex-column flex-md-row justify-content-start mb-2">
                                @can('pengajuan_cuti_kepala_hrd.create')
                                    <div class="me-md-2 mb-2">
                                        <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Baru
                                        </button>
                                    </div>
                                @endcan
                            </div>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dataSedangDiproses as $index => $cutiProses)
                                            <tr>
                                                <td>{{ $cutiProses->created_at->format('d/m/Y') }}</td>
                                                <td>{{ $cutiProses->user->profilePribadi->nama_lengkap }}</td>
                                                <td>{{ $cutiProses->tipe_cuti }}</td>
                                                <td>{{ $cutiProses->tanggal_mulai->format('d M') }} - {{ $cutiProses->tanggal_selesai->format('d M') }}</td>
                                                <td>{{ $cutiProses->alasan_pendukung ?? 'Tidak ada alasan pendukung' }}</td>
                                                <td>
                                                    @if (!empty($cutiProses->file_pendukung))
                                                        <a href="{{ asset('storage/' . $cutiProses->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                            <i class="bi bi-eye"></i> Buka
                                                        </a>
                                                    @else
                                                    Tidak ada file pendukung yang ditambahkan
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge text-bg-warning text-wrap">{{ $cutiProses->status_pengajuan }}</span>
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

                {{-- tabel riwayat Pengajuan --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Riwayat Ajuan</h5>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dataSelesai as $index => $cutiSelesai)
                                            <tr>
                                                <td>{{ $cutiSelesai->created_at->format('d/m/Y') }}</td>
                                                <td>{{ $cutiSelesai->user->profilePribadi->nama_lengkap }}</td>
                                                <td>{{ $cutiSelesai->tipe_cuti }}</td>
                                                <td>{{ $cutiSelesai->tanggal_mulai->format('d M') }} - {{ $cutiSelesai->tanggal_selesai->format('d M') }}</td>
                                                <td>{{ $cutiSelesai->alasan_pendukung ?? 'Tidak ada alasan pendukung' }}</td>
                                                <td>
                                                    @if (!empty($cutiSelesai->file_pendukung))
                                                        <a href="{{ asset('storage/' . $cutiSelesai->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                            <i class="bi bi-eye"></i> Buka
                                                        </a>
                                                    @else
                                                        Tidak ada file pendukung yang ditambahkan
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge text-wrap {{ in_array($cutiSelesai->status_pengajuan, ['disetujui dirpen']) ? 'text-bg-success' : 'text-bg-danger' }}">
                                                        {{ $cutiSelesai->status_pengajuan }}
                                                    </span>
                                                </td>
                                                <td>{{ $cutiSelesai->komentar }}</td>
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
                    <h1 class="modal-title fs-5">Tambah Ajuan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('pengajuan.cuti.kepala.hrd.store') }}" method="post" enctype="multipart/form-data">
                        @csrf @method('post')
                        <div class="container-fluid">
                            <div class="row gy-2">
                                <div class="col-6">
                                    <label for="exampleFormControlInput1" class="form-label">Tanggal Mulai</label>
                                    <input name="tanggal_mulai" type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label for="exampleFormControlInput1" class="form-label">Tanggal Selesai</label>
                                    <input name="tanggal_selesai" type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="exampleFormControlInput1" class="form-label">Tipe Cuti</label>
                                    <select name="tipe_cuti" class="form-select @error('tipe_cuti') is-invalid @enderror" required>
                                        <option value="cuti tahunan">Cuti Tahunan</option>
                                        <option value="cuti melahirkan">Cuti Melahirkan</option>
                                        <option value="cuti nikah">Cuti Nikah</option>
                                        <option value="cuti kematian">Cuti Kematian</option>
                                        <option value="cuti bersama">Cuti Bersama</option>
                                        <option value="cuti pemotongan gaji">Cuti Pemotongan Gaji</option>
                                        <option value="cuti lainnya">Cuti Lainnya</option>
                                    </select>
                                    @error('tipe_cuti')
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
                                    <label for="" class="form-label">Alasan Pendukung</label>
                                    <textarea type="alasan_pendukung" name="alasan_pendukung" class="form-control @error('alasan_pendukung') is-invalid @enderror" placeholder="Dapat dikosongkan jika tidak ada">{{ old('alasan_pendukung') }}</textarea>
                                    @error('alasan_pendukung')
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
