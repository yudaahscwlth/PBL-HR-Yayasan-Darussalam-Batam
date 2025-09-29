@extends('html.html')

@push('css')
    <style>
        .accordion {
            --bs-accordion-bg: transparent;
            --bs-accordion-btn-padding-x: 0.5rem;
            --bs-accordion-btn-padding-y: 0.4rem;
        }

        .accordion-button:not(.collapsed) {
            background-color: transparent !important;
            color: #000 !important;
        }
    </style>
@endpush

@section('content')

    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">
        <div class="pagetitle">
            <h1 class="text-capitalize">Log Absensi</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                {{-- nama Pegawai --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="mt-3 d-flex flex-column flex-lg-row justify-content-between align-items-center">
                                <div class="h2 text-center text-lg-start text-capitalize order-1 order-lg-0">
                                    Log Absensi {{ $dataAbsensi->user->profilePribadi->nama_lengkap }} / {{ $dataAbsensi->tanggal->format('d M Y') }}</Log>
                                </div>
                                <div class="img-container order-0 order-lg-1">
                                    <img src="{{ $dataAbsensi->user->profilePribadi->foto ? asset('storage/'.$dataAbsensi->user->profilePribadi->foto) : asset('assets/img/profile-img.jpg') }}" alt="" class="rounded-circle" height="120" width="120">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        @forelse ($dataAbsensi->logAktivitasAbsensi as $logAbsen )
                            <div class="p-2 text-capitalize  @if ($logAbsen->aksi == 'created' ) bg-success-subtle text-success-emphasis @elseif ($logAbsen->aksi == 'updated') bg-warning-subtle text-warning-emphasis @elseif ($logAbsen->aksi == 'deleted') bg-danger-subtle text-danger-emphasis @elseif ($logAbsen->aksi == 'restored') bg-primary-subtle text-primary-emphasis @else bg-secondary-subtle text-secondary-emphasis @endif">
                                [ {{ $logAbsen->created_at }} ] <span class="fw-semibold">{{ $logAbsen->aksi }}</span> Oleh <span class="fw-semibold">
                                    @if($logAbsen->user)
                                        {{ $logAbsen->user->profilePribadi->nama_lengkap ?? 'User tidak ditemukan' }}
                                    @else
                                        Sistem (Auto-generated)
                                    @endif
                                </span>
                                @if (!empty($logAbsen->data_lama || $logAbsen->data_baru))
                                    <div class="accordion mt-2" id="accordionExample">
                                        @if (!empty($logAbsen->data_lama))
                                            <div class="accordion-item @if ($logAbsen->aksi == 'created' ) border-success-subtle @elseif ($logAbsen->aksi == 'updated') border-warning-subtle @elseif ($logAbsen->aksi == 'deleted') border-danger-subtle @elseif ($logAbsen->aksi == 'restored') border-primary-subtle @else border-secondary-subtle @endif">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo{{ $logAbsen->id }}" aria-expanded="false" aria-controls="collapseTwo{{ $logAbsen->id }}">
                                                        Data Lama
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo{{ $logAbsen->id }}" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        @php
                                                            // Decode JSON ke array
                                                            $dataLama = json_decode($logAbsen->data_lama, true);

                                                            // Daftar key yang berisi tanggal/waktu
                                                            $dateKeys = ['tanggal', 'check_in', 'check_out', 'created_at', 'updated_at','deleted_at'];

                                                            // Format semua key tanggal ke zona waktu lokal
                                                            foreach ($dateKeys as $key) {
                                                                if (isset($dataLama[$key])) {
                                                                    try {
                                                                        // Konversi ke timezone Asia/Jakarta
                                                                        $dataLama[$key] = \Carbon\Carbon::parse($dataBaru[$key])
                                                                            ->timezone('Asia/Jakarta')
                                                                            ->format('Y-m-d H:i:s');
                                                                    } catch (\Exception $e) {
                                                                        // Abaikan jika format tidak bisa diparse
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{-- Tampilkan hasil JSON yang sudah diformat --}}
                                                        <pre>{{ json_encode($dataLama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (!empty($logAbsen->data_baru))
                                            <div class="accordion-item @if ($logAbsen->aksi == 'created' ) border-success-subtle @elseif ($logAbsen->aksi == 'updated') border-warning-subtle @elseif ($logAbsen->aksi == 'deleted') border-danger-subtle @elseif ($logAbsen->aksi == 'restored') border-primary-subtle @else border-secondary-subtle @endif">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne{{ $logAbsen->id }}" aria-expanded="true" aria-controls="collapseOne{{ $logAbsen->id }}">
                                                        Data Baru
                                                    </button>
                                                </h2>
                                                <div id="collapseOne{{ $logAbsen->id }}" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        @php
                                                            // Decode JSON ke array
                                                            $dataBaru = json_decode($logAbsen->data_baru, true);

                                                            // Daftar key yang berisi tanggal/waktu
                                                            $dateKeys = ['tanggal', 'check_in', 'check_out', 'created_at', 'updated_at','deleted_at'];

                                                            // Format semua key tanggal ke zona waktu lokal
                                                            foreach ($dateKeys as $key) {
                                                                if (isset($dataBaru[$key])) {
                                                                    try {
                                                                        // Konversi ke timezone Asia/Jakarta
                                                                        $dataBaru[$key] = \Carbon\Carbon::parse($dataBaru[$key])
                                                                            ->timezone('Asia/Jakarta')
                                                                            ->format('Y-m-d H:i:s');
                                                                    } catch (\Exception $e) {
                                                                        // Abaikan jika format tidak bisa diparse
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{-- Tampilkan hasil JSON yang sudah diformat --}}
                                                        <pre>{{ json_encode($dataBaru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-2 text-capitalize bg-secondary-subtle text-secondary-emphasis">
                                Tidak Ada Log Aktivitas
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </section>
    </main>

    @include('components.footer')
@endsection
