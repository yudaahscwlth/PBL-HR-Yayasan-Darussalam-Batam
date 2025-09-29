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

    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                info: false,
                dom: '<"row"<"col-sm-6 d-flex justify-content-center justify-content-sm-start mb-2 mb-sm-0"l><"col-sm-6 d-flex justify-content-center justify-content-sm-end"f>>rt<"row"<"col-sm-6 mt-0"i><"col-sm-6 mt-2"p>>'
            });
        });
    </script>

{{-- chart absen --}}
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

@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Selamat Datang, {{ Auth::user()->getRoleNames()->implode(',') }}</h1>
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
                </div><!-- End Right side columns -->

                {{-- Horizontal chart --}}
                <div class="col-lg-6 mt-3 mt-lg-0 order-2 order-lg-2">
                    <div class="card p-3">
                        <h5 class="card-title">Kehadiran</h5>
                        <canvas id="myChart"></canvas>
                    </div>
                </div>

                {{-- absensi --}}
                <div class="col-lg-6 order-1 order-lg-3">
                    @include('components.absensi-button')
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    @include('components.footer')
@endsection
