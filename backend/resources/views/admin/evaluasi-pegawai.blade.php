@extends('html.html')

@push('css')
    <style>
        .disabled-text{
            pointer-events: none;
            cursor: default;
            opacity: 0.5;
        }
    </style>
@endpush

@push('js')
    <script>
        function checkAllRadio(nilai) {
            const radios = document.querySelectorAll(`input[type="radio"][value="${nilai}"]`);
            radios.forEach(radio => {
                radio.checked = true;
            });
        }
    </script>
@endpush


@section('content')
    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1 class="text-capitalize">Evaluasi</h1>
            @include('components.breadcrumb')
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <div class="col-12" id="kelola-admin">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body">
                            <h5 class="card-title">Evaluasi</h5>
                            <livewire:evaluasi-form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    @include('components.footer')
@endsection
