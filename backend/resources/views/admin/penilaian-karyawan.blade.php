@extends('admin.master')


@section('title', 'Form Penilaian Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.penilaian') }}">Penilaian</a></li>
    <li class="breadcrumb-item active" aria-current="page">Form</li>
@endsection


@section('content')
<div class="card">
    <div class="card-header">
        <h5>Form Penilaian - Ahmad Fauzi</h5>
    </div>
    <div class="card-body">
        <form>
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kriteria</th>
                        <th>Sangat Baik</th>
                        <th>Baik</th>
                        <th>Kurang Baik</th>
                        <th>Tidak Baik</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $kriteria = ['Disiplin', 'Etika Kerja', 'Tanggung Jawab', 'Komunikasi', 'Kerja Tim'];
                    @endphp
                    @foreach ($kriteria as $index => $k)
                    <tr>
                        <td class="text-start">{{ $k }}</td>
                        <td><input type="radio" name="nilai[{{ $index }}]" value="4" class="radio-nilai nilai-{{ $index }}"></td>
                        <td><input type="radio" name="nilai[{{ $index }}]" value="3" class="radio-nilai nilai-{{ $index }}"></td>
                        <td><input type="radio" name="nilai[{{ $index }}]" value="2" class="radio-nilai nilai-{{ $index }}"></td>
                        <td><input type="radio" name="nilai[{{ $index }}]" value="1" class="radio-nilai nilai-{{ $index }}"></td>
                    </tr>
                    @endforeach
                    <!-- Baris untuk Nilai Semua -->
                    <tr class="table-light fw-semibold">
                        <td>Nilai Semua:</td>
                        <td><input type="radio" name="all" value="4" onclick="isiSemua(4)"></td>
                        <td><input type="radio" name="all" value="3" onclick="isiSemua(3)"></td>
                        <td><input type="radio" name="all" value="2" onclick="isiSemua(2)"></td>
                        <td><input type="radio" name="all" value="1" onclick="isiSemua(1)"></td>
                    </tr>
                </tbody>
            </table>

            <div class="text-end">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function isiSemua(nilai) {
        const radios = document.querySelectorAll('.radio-nilai');
        radios.forEach(radio => {
            if (radio.value == nilai) {
                radio.checked = true;
            }
        });
    }
</script>
@endpush
