@extends('html.html')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/styles/index.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/styles/layout.css">
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
        .approved { background-color: #22bb33; } /* Biru */
        .rejected { background-color: #bb2124; }  /* Merah */
        .pending  { background-color: #f0ad4e; }  /* Oranye */
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/index.js" defer></script>

    {{-- kalender --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const { Calendar } = window.VanillaCalendarPro;

            const options = {
                selectedTheme: 'light' // Pastikan tema cocok dengan yang di-load di <head>
            };

            const calendar = new Calendar('#calendar', options);
            calendar.init();
        });
    </script>

    {{-- datatable --}}
    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                info: false,
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
            <h1 class="text-capitalize">Selamat Datang, {{ Auth::user()->getRoleNames()->implode(', ') }}</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Left side columns -->
                <div class="col-lg-8 order-0 order-lg-0">
                    @include('components.profile-card-dashboard')
                </div><!-- End Left side columns -->

                <!-- Right side columns -->
                <div class="col-lg-4 order-3 order-lg-1">
                    <div id="calendar"></div>

                </div>

                {{-- tabel pengajuan cuti --}}
                <div class="col-lg-8 order-2 order-lg-2" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Pengajuan Cuti</h5>
                            <table class="table table-striped table-hover border table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Tipe</th>
                                        <th scope="col">Durasi</th>
                                        <th scope="col">status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataPengajuanCuti as $index => $data )
                                    <tr>
                                        <td>{{ $data->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $data->user->profilePribadi->nama_lengkap  }}</td>
                                        <td>{{ $data->tipe_cuti }}</td>
                                        <td>{{ $data->tanggal_mulai->format('d-m-Y') }} - {{ $data->tanggal_selesai->format('d-m-Y') }}</td>
                                        <td>
                                            <span class="badge text-wrap
                                            {{ str_contains($data->status_pengajuan, 'menunggu tinjauan') ? 'text-bg-warning' :
                                            (str_contains($data->status_pengajuan, 'disetujui') ? 'text-bg-success' :
                                            (str_contains($data->status_pengajuan, 'ditolak') ? 'text-bg-danger' : 'text-bg-warning')) }}">
                                                {{ $data->status_pengajuan }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                            <div class="status-container">
                                <div class="status">
                                    <div class="status-box approved"></div>
                                    <span>Disetujui</span>
                                </div>
                                <div class="status">
                                    <div class="status-box rejected"></div>
                                    <span>Ditolak</span>
                                </div>
                                <div class="status">
                                    <div class="status-box pending"></div>
                                    <span>Menunggu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- absen button --}}
                <div class="col-lg-4 order-1 order-lg-3">
                    @include('components.absensi-button')
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    @include('components.footer')
@endsection
