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
            <h1 class="text-capitalize">Verifikasi Cuti</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                {{-- tabel verifikasi cuti --}}
                <div class="col-12" id="kelola-admin">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Verifikasi Cuti</h5>
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
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dataSedangDiproses as $index => $verifikasiProses)
                                            <tr>
                                                <td>{{ $verifikasiProses->created_at->format('d/m/Y') }}</td>
                                                <td>{{ $verifikasiProses->user->profilePribadi->nama_lengkap }}</td>
                                                <td>{{ $verifikasiProses->tipe_cuti }}</td>
                                                <td>{{ $verifikasiProses->tanggal_mulai->format('d M') }} - {{ $verifikasiProses->tanggal_selesai->format('d M') }}</td>
                                                <td>{{ $verifikasiProses->alasan_pendukung ?? 'Tidak ada alasan pendukung' }}</td>
                                                <td>
                                                    @if (!empty($verifikasiProses->file_pendukung))
                                                        <a href="{{ asset('storage/' . $verifikasiProses->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                            <i class="bi bi-eye"></i> Buka
                                                        </a>
                                                    @else
                                                        Tidak ada file pendukung yang ditambahkan
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge text-bg-warning text-wrap">{{ $verifikasiProses->status_pengajuan }}</span>
                                                </td>
                                                <td>
                                                    @can('verifikasi_cuti_staff_hrd.update')
                                                        <button class="btn btn-sm btn-primary text-nowrap"  data-bs-toggle="modal" data-bs-target="#konfirmasiModal{{ $verifikasiProses->id }}">
                                                            Konfirmasi <i class="bi bi-caret-right"></i>
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
                                        @forelse ($dataSelesai as $index => $verifikasiSelesai)
                                            <tr>
                                                <td>{{ $verifikasiSelesai->created_at->format('d/m/Y') }}</td>
                                                <td>{{ $verifikasiSelesai->user->profilePribadi->nama_lengkap }}</td>
                                                <td>{{ $verifikasiSelesai->tipe_cuti }}</td>
                                                <td>{{ $verifikasiSelesai->tanggal_mulai->format('d M') }} - {{ $verifikasiSelesai->tanggal_selesai->format('d M') }}</td>
                                                <td>{{ $verifikasiSelesai->alasan_pendukung ?? 'Tidak ada alasan pendukung' }}</td>
                                                <td>
                                                    @if (!empty($verifikasiSelesai->file_pendukung))
                                                        <a href="{{ asset('storage/' . $verifikasiSelesai->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                            <i class="bi bi-eye"></i> Buka
                                                        </a>
                                                    @else
                                                        Tidak ada file pendukung yang ditambahkan
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge text-wrap @if ($verifikasiSelesai->status_pengajuan =='disetujui hrd') text-bg-success @elseif ($verifikasiSelesai->status_pengajuan =='ditolak hrd') text-bg-danger @else text-bg-warning @endif">{{ $verifikasiSelesai->status_pengajuan }}</span>
                                                </td>
                                                <td>
                                                    {{ $verifikasiSelesai->komentar }}
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

    {{-- konfirmasiModal --}}
    @foreach ($dataSedangDiproses as $verifikasiProses)
        <div class="modal fade" id="konfirmasiModal{{ $verifikasiProses->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Konfirmasi Ajuan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('verifikasi.cuti.hrd.update',['id_pengajuan' => $verifikasiProses->id]) }}" method="post">
                            @csrf @method('put')
                            <div class="container-fluid">
                                <div class="row gy-2">
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Tanggal Pengajuan</label>
                                        <input name="tanggal_pengajuan" type="date" class="form-control @error('tanggal_pengajuan') is-invalid @enderror" value="{{ $verifikasiProses->created_at->format('Y-m-d') }}" disabled>
                                        @error('tanggal_pengajuan')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Nama</label>
                                        <input name="nama_lengkap" type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ $verifikasiProses->user->profilePribadi->nama_lengkap }}" disabled>
                                        @error('nama_lengkap')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Tanggal Mulai</label>
                                        <input name="tanggal_mulai" type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ $verifikasiProses->tanggal_mulai->format('Y-m-d') }}" disabled>
                                        @error('tanggal_mulai')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label for="exampleFormControlInput1" class="form-label">Tanggal Selesai</label>
                                        <input name="tanggal_selesai" type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ $verifikasiProses->tanggal_selesai->format('Y-m-d') }}" disabled>
                                        @error('tanggal_selesai')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Tipe Cuti</label>
                                        <input name="tipe_cuti" type="text" class="form-control @error('tipe_cuti') is-invalid @enderror" value="{{ $verifikasiProses->tipe_cuti }}" disabled>
                                        @error('tipe_cuti')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Alasan Cuti</label>
                                        <textarea name="alasan_pendukung" class="form-control @error('alasan_pendukung') is-invalid @enderror" disabled>{{ $verifikasiProses->alasan_pendukung ?? 'Tidak ada alasan yang ditambahkan'}}</textarea>
                                        @error('alasan_pendukung')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        @if (!empty($verifikasiProses->file_pendukung))
                                        <div class="d-flex flex-column">
                                            <label for="exampleFormControlInput1" class="form-label">File Pendukung</label>
                                            <a href="{{ asset('storage/' . $verifikasiProses->file_pendukung) }}" target="_blank" class="btn btn-main btn-sm">
                                                <i class="bi bi-eye"></i> Buka
                                            </a>
                                        </div>
                                        @else
                                            Tidak ada file pendukung yang ditambahkan
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Verifikasi</label>
                                        <select name="status_pengajuan" class="form-select @error('status_pengajuan') is-invalid @enderror">
                                            <option value="disetujui hrd menunggu tinjauan dirpen">Setuju</option>
                                            <option value="ditolak hrd">Tolak</option>
                                        </select>
                                        @error('status_pengajuan')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="exampleFormControlInput1" class="form-label">Komentar</label>
                                        <textarea name="komentar" class="form-control @error('komentar') is-invalid @enderror" placeholder="Isi dengan komentar jika ada"></textarea>
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
    @endforeach

    @include('components.footer')
@endsection
