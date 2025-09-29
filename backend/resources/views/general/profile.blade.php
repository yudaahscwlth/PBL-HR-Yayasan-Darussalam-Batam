@extends('html.html')

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/@jarstone/dselect/dist/css/dselect.css">
    <style>
        .danger-tooltip {
            --bs-tooltip-bg: #dc3545;
            --bs-tooltip-color: var(--bs-white);
        }
    </style>
@endpush

@push('js')
    <script src="https://unpkg.com/@jarstone/dselect/dist/js/dselect.js"></script>

{{-- search select --}}
<script>
    $(document).ready(function() {
        $('.search-name').each(function() {
            var $this = $(this);

            // Terapkan dselect
            dselect(this);

            // Disable elemen select asli
            $this.prop('disabled', true);

            // Disable elemen kustom yang dibuat oleh dselect
            $this.siblings('.dselect-wrapper').find('button').prop('disabled', true);
        });
    });
</script>

{{-- sunting button --}}
    <script>
        $(document).ready(function () {
            let isEditMode = false;

            $('.toggle-edit').on('click', function () {
                let $form = $('#form-edit');

                if (!isEditMode) {
                    // Masuk ke mode edit
                    $form.find('input:not(.unedit), select:not(.unedit), textarea:not(.unedit), .dselect-wrapper button')
                        .prop('disabled', false)
                        .prop('readonly', false);
                    $(this).html('<i class="bi bi-x"></i> Batal Sunting');
                } else {
                    // Kembali ke mode tampilan (read-only)
                    $form.find('input, select, textarea, .dselect-wrapper button')
                        .prop('disabled', true)
                        .prop('readonly', true);
                    $(this).html('<i class="bi bi-pencil"></i> Sunting');
                }

                isEditMode = !isEditMode;
            });
        });
    </script>

{{-- tambah keluarga button --}}
    <script>
        $(document).ready(function () {
            $('#tambah-anggota').on('click', function () {
                let index = $('#container-keluarga .anggota-keluarga').length + 1;

                let newAnggota = `
                <div class="row mt-2 anggota-keluarga">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-capitalize">keluarga ke ${index}</h6>
                        <i class="bi bi-dash-circle text-danger fs-4 hapus-baris" role="button" data-bs-toggle="tooltip" data-bs-title="Hapus Baris" data-bs-custom-class="danger-tooltip"></i>
                    </div>
                    <input type="hidden" name="id_keluarga[]" value="">
                    <div class="col-6 mb-3">
                        <label class="form-label">Nama</label>
                        <input name="nama[]" class="form-control" type="text" disabled required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Hubungan</label>
                        <select name="hubungan[]" class="form-select" disabled required>
                            <option value="suami">Suami</option>
                            <option value="istri">Istri</option>
                            <option value="anak">Anak</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input name="tanggal_lahir_keluarga[]" class="form-control" type="date" disabled required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Pekerjaan</label>
                        <input name="pekerjaan[]" class="form-control" type="text" disabled required>
                    </div>
                </div>`;

                $('#container-keluarga').append(newAnggota);

                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            //hapus-baris
            $('#container-keluarga').on('click', '.hapus-baris', function () {
                // Temukan elemen i (icon) tempat tooltip aktif
                const tooltipEl = this;

                // Dispose tooltip sebelum elemen dihapus
                const tooltipInstance = bootstrap.Tooltip.getInstance(tooltipEl);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                }

                $(this).closest('.anggota-keluarga').remove();
            });
        });
    </script>

{{-- tambah sosmed button --}}
    <script>
        $(document).ready(function () {
            $('#tambah-sosmed').on('click', function () {
                let index = $('#container-sosmed .sosmed').length + 1;

                let newSosmed = `
                <div class="row mt-2 sosmed">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-capitalize">Sosial Media ke ${index}</h6>
                        <i class="bi bi-dash-circle text-danger fs-4 hapus-baris" role="button" data-bs-toggle="tooltip" data-bs-title="Hapus Baris" data-bs-custom-class="danger-tooltip"></i>
                    </div>
                    <input type="hidden" name="id_user_sosmed[]" value="">
                    <div class="col-6 mb-3">
                        <label for="formFile" class="form-label">Nama Platform</label>
                        <select name="id_platform[]" class="form-select @error('id_platform') is-invalid @enderror" disabled required>
                            @foreach ($dataSosialMedia as $platform )
                                <option value="{{ $platform->id }}">{{ $platform->nama_platform }}</option>
                            @endforeach
                        </select>
                        @error('id_platform')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6 mb-3">
                        <label for="formFile" class="form-label">Username</label>
                        <input name="username[]" class="form-control @error('username') is-invalid @enderror" type="text" disabled required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6 mb-3">
                        <label for="formFile" class="form-label">Link Sosial Media</label>
                        <input name="link[]" class="form-control  @error('link') is-invalid @enderror" type="text" disabled required>
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>`;

                $('#container-sosmed').append(newSosmed);

                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            //hapus-baris
            $('#container-sosmed').on('click', '.hapus-baris', function () {
                // Temukan elemen i (icon) tempat tooltip aktif
                const tooltipEl = this;

                // Dispose tooltip sebelum elemen dihapus
                const tooltipInstance = bootstrap.Tooltip.getInstance(tooltipEl);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                }

                $(this).closest('.sosmed').remove();
            });
        });
    </script>

@endpush

@section('content')

    @include('components.navbar')

    @include('components.sidebar')

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Profile</h1>
            @include('components.breadcrumb')
        </div>

        <section class="section profile">
            {{-- foto profile --}}
            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-bordered mt-2">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Ubah Kata Sandi</button>
                            </li>
                        </ul>
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center mt-3">
                            <div class="h2 fw-semibold text-uppercase order-1 order-lg-0">
                                {{ $data->profilePribadi->nama_lengkap }}
                            </div>
                            <div class="img-container order-0 order-lg-1">
                                <img src="{{ $data->profilePribadi->foto ? asset('storage/'.$data->profilePribadi->foto) : asset('assets/img/profile-img.jpg') }}" alt="" class="rounded-circle" height="120" width="120">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active profile-edit" id="profile-edit">
                    <form id="form-edit" action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data">
                        @csrf @method('put')
                        {{-- data pekerjaan --}}
                        <div class="row">
                            <div class="card">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p class="h5 text-capitalize fw-semibold mt-2">data Pekerjaan</p>
                                        <hr class="border border-dark opacity-100">
                                    </div>
                                    <div class="col-lg-9 bg-light">
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Departemen</label>
                                                <input name="departemen" class="unedit form-control @error('departemen') is-invalid @enderror" type="text" value="{{ $data->profilePekerjaan->departemen->nama_departemen ?? ''}}" disabled>
                                                @error('departemen')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Tempat Bekerja</label>
                                                <input name="tempat_kerja" class="unedit form-control @error('tempat_kerja') is-invalid @enderror" type="text" value="{{ $data->profilePekerjaan->tempatKerja->nama_tempat ?? ''}}" disabled>
                                                @error('tempat_kerja')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Jabatan</label>
                                                <input name="jabatan" class="unedit form-control @error('jabatan') is-invalid @enderror" type="text" value="{{ $data->profilePekerjaan->jabatan->nama_jabatan ?? ''}}" disabled>
                                                @error('jabatan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Nomor Induk Karyawan</label>
                                                <input name="nomor_induk_karyawan" class="unedit form-control @error('nomor_induk_karyawan') is-invalid @enderror" type="text" value="{{ $data->profilePekerjaan->nomor_induk_karyawan ?? ''}}" disabled>
                                                @error('nomor_induk_karyawan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Tanggal Masuk</label>
                                                <input name="tanggal_masuk" class="unedit form-control @error('tanggal_masuk') is-invalid @enderror" type="text" value="{{ $data->profilePekerjaan->tanggal_masuk ??  ''}}" disabled>
                                                @error('tanggal_masuk')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Lama Pengabdian</label>
                                                <input name="lama_pengabdian" class="unedit form-control @error('lama_pengabdian') is-invalid @enderror" type="text" value="{{ $lamaPengabdian ??  ''}}" disabled>
                                                @error('lama_pengabdian')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Status</label>
                                                <input name="status_karyawan" class="unedit form-control @error('status_karyawan') is-invalid @enderror" type="text" value="{{ $data->profilePekerjaan->status ??  ''}}" disabled>
                                                @error('status_karyawan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- data diri --}}
                        <div class="row">
                            <div class="card">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p class="h5 text-capitalize fw-semibold mt-2">Ubah data diri</p>
                                        <hr class="border border-dark opacity-100">
                                    </div>
                                    <div class="col-lg-9 bg-light">
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="text-end">
                                                    <button type="button" class="btn btn-sm btn-main toggle-edit">
                                                        <i class="bi bi-pencil"></i> Sunting
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Email</label>
                                                <input name="email" class="form-control @error('email') is-invalid @enderror" type="text" value="{{ $data->email }}" disabled required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Nomor Induk Kependudukan</label>
                                                <input name="nomor_induk_kependudukan" class="form-control @error('nomor_induk_kependudukan') is-invalid @enderror" type="text" value="{{ $data->profilePribadi->nomor_induk_kependudukan ?? '' }}" disabled required>
                                                @error('nomor_induk_kependudukan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Nama Lengkap</label>
                                                <input name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" type="text" value="{{ $data->profilePribadi->nama_lengkap ?? '' }}" disabled required>
                                                @error('nama_lengkap')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">NPWP</label>
                                                <input name="npwp" type="text" class="form-control @error('npwp') is-invalid @enderror" value="{{ $data->profilePribadi->npwp ?? '' }}" disabled>
                                                @error('npwp')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Tempat Lahir</label>
                                                <select name="tempat_lahir" class="form-select search-name @error('tempat_lahir') is-invalid @enderror" data-dselect-search="true">
                                                    <option value="" @if ($data->profilePribadi->tempat_lahir == '') selected @endif disabled>Tidak ada data yang dipilih</option>
                                                    @foreach ($allKota as $provinsi)
                                                        <optgroup label="{{ $provinsi['provinsi'] }}">
                                                            @foreach ($provinsi['kota'] as $kota)
                                                                <option value="{{ $kota }}" @if ($data->profilePribadi->tempat_lahir == $kota) selected @endif>{{ $kota }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                                @error('tempat_lahir')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Tanggal Lahir</label>
                                                <input name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" type="date" value="{{ $data->profilePribadi->tanggal_lahir ?? '' }}" disabled>
                                                @error('tanggal_lahir')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Jenis Kelamin</label>
                                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" disabled>
                                                    <option value="pria" @if ($data->profilePribadi->jenis_kelamin == 'pria') selected  @endif>Pria</option>
                                                    <option value="wanita"@if ($data->profilePribadi->jenis_kelamin == 'wanita') selected  @endif>Wanita</option>
                                                </select>
                                                @error('jenis_kelamin')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Status Pernikahan</label>
                                                <select name="status_pernikahan" class="form-select @error('status_pernikahan') is-invalid @enderror" disabled>
                                                    <option value="belum nikah"@if ($data->profilePribadi->status_pernikahan == 'belum nikah') selected  @endif>Belum Menikah</option>
                                                    <option value="sudah nikah" @if ($data->profilePribadi->status_pernikahan == 'sudah nikah') selected  @endif>Sudah Menikah</option>
                                                </select>
                                                @error('status_pernikahan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Golongan Darah</label>
                                                <select name="golongan_darah" class="form-select @error('golongan_darah') is-invalid @enderror" disabled>
                                                    <option value="a" @if ($data->profilePribadi->golongan_darah == 'a') selected  @endif>A</option>
                                                    <option value="b"@if ($data->profilePribadi->golongan_darah == 'b') selected  @endif>B</option>
                                                    <option value="ab"@if ($data->profilePribadi->golongan_darah == 'ab') selected  @endif>AB</option>
                                                    <option value="o"@if ($data->profilePribadi->golongan_darah == 'o') selected  @endif>O</option>
                                                </select>
                                                @error('golongan_darah')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Kecamatan</label>
                                                <select name="kecamatan" class="form-select search-name @error('kecamatan') is-invalid @enderror" data-dselect-search="true">
                                                    <option value="" @if ($data->profilePribadi->kecamatan == '') selected @endif disabled>Tidak ada data yang dipilih</option>
                                                    @foreach ($allKecamatan as $kecamatan )
                                                        <option value="{{ $kecamatan['name'] }}"@if ($data->profilePribadi->kecamatan == $kecamatan['name']) selected @endif>{{ $kecamatan['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('kecamatan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Alamat Lengkap</label>
                                                <textarea name="alamat_lengkap" class="form-control @error('alamat_lengkap') is-invalid @enderror" disabled>{{ $data->profilePribadi->alamat_lengkap ?? ''}}</textarea>
                                                @error('alamat_lengkap')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Nomor Hp</label>
                                                <input name="no_hp" type="text" class="form-control @error('no_hp') is-invalid @enderror" value="{{ $data->profilePribadi->no_hp ?? '' }}" disabled>
                                                @error('no_hp')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Foto</label>
                                                <input name="foto" type="file" class="form-control @error('foto') is-invalid @enderror" disabled>
                                                @error('foto')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- data orang tua --}}
                        <div class="row">
                            <div class="card">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p class="h5 text-capitalize fw-semibold mt-2">Ubah data orang tua</p>
                                        <hr class="border border-dark opacity-100">
                                    </div>
                                    <div class="col-lg-9 bg-light">
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="text-end">
                                                    <button type="button" class="btn btn-sm btn-main toggle-edit">
                                                        <i class="bi bi-pencil"></i> Sunting
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Nama Ayah</label>
                                                <input name="nama_ayah" class="form-control @error('nama_ayah') is-invalid @enderror" type="text" value="{{ $data->orangTua->nama_ayah ?? '' }}" disabled>
                                                @error('nama_ayah')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Pekerjaan Ayah</label>
                                                <input name="pekerjaan_ayah" class="form-control @error('pekerjaan_ayah') is-invalid @enderror" type="text" value="{{ $data->orangTua->pekerjaan_ayah ?? '' }}" disabled>
                                                @error('pekerjaan_ayah')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Nama Ibu</label>
                                                <input name="nama_ibu" class="form-control @error('nama_ibu') is-invalid @enderror" type="text" value="{{ $data->orangTua->nama_ibu ?? '' }}" disabled>
                                                @error('nama_ibu')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Pekerjaan Ibu</label>
                                                <input name="pekerjaan_ibu" class="form-control @error('pekerjaan_ibu') is-invalid @enderror" type="text" value="{{ $data->orangTua->pekerjaan_ibu ?? '' }}" disabled>
                                                @error('pekerjaan_ibu')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 mb-3">
                                                <label for="formFile" class="form-label">Alamat Orang Tua</label>
                                                <textarea name="alamat_orang_tua" class="form-control @error('alamat_orang_tua') is-invalid @enderror" disabled>{{ $data->orangTua->alamat_orang_tua ?? '' }}</textarea>
                                                @error('alamat_orang_tua')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- data keluarga --}}
                        <div class="row">
                            <div class="card">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p class="h5 text-capitalize fw-semibold mt-2">Ubah data keluarga</p>
                                        <hr class="border border-dark opacity-100">
                                    </div>
                                    <div class="col-lg-9 bg-light">
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    <button type="button" id="tambah-anggota" class="btn btn-sm btn-main">
                                                        <i class="bi bi-plus-circle"></i> Tambah Anggota
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-main toggle-edit">
                                                        <i class="bi bi-pencil"></i> Sunting
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="container-keluarga">
                                            @forelse ($data->keluarga as $index => $keluarga )
                                            <div class="row mt-2 anggota-keluarga">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="fw-bold text-capitalize">keluarga ke {{ $index+1 }}</div>
                                                    <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $keluarga->id }}">
                                                        <i class="bi bi-trash" data-bs-toggle="tooltip" data-bs-title="Hapus Keluarga" data-bs-custom-class="danger-tooltip"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="id_keluarga[]" value="{{ $keluarga->id }}">
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Nama</label>
                                                    <input name="nama[]" class="form-control @error('nama.' . $index) is-invalid @enderror" type="text" value="{{ old('nama.' . $index, $keluarga->nama) }}" disabled required>
                                                    @error('nama.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Hubungan</label>
                                                    <select name="hubungan[]" class="form-select @error('hubungan.' . $index) is-invalid @enderror" disabled required>
                                                        <option value="suami" @if ($keluarga->hubungan == 'suami') selected @endif>Suami</option>
                                                        <option value="istri" @if ($keluarga->hubungan == 'istri') selected @endif>Istri</option>
                                                        <option value="anak" @if ($keluarga->hubungan == 'anak') selected @endif>Anak</option>
                                                        <option value="lainnya" @if ($keluarga->hubungan == 'lainnya') selected @endif>Lainnya</option>
                                                    </select>
                                                    @error('hubungan.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Tanggal Lahir</label>
                                                    <input name="tanggal_lahir_keluarga[]" class="form-control @error('tanggal_lahir_keluarga.' . $index) is-invalid @enderror" type="date" value="{{ old('tanggal_lahir_keluarga.' . $index, $keluarga->tanggal_lahir) }}" disabled required>
                                                    @error('tanggal_lahir_keluarga.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Pekerjaan</label>
                                                    <input name="pekerjaan[]" class="form-control  @error('pekerjaan.' . $index) is-invalid @enderror" type="text" value="{{ old('pekerjaan.' . $index, $keluarga->pekerjaan) }}" disabled required>
                                                    @error('pekerjaan.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            @empty
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="text-center h1">
                                                            Tidak ada data untuk ditampilkan!
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- data sosial media --}}
                        <div class="row">
                            <div class="card">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p class="h5 text-capitalize fw-semibold mt-2">Ubah data sosial media</p>
                                        <hr class="border border-dark opacity-100">
                                    </div>
                                    <div class="col-lg-9 bg-light">
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    <button type="button" id="tambah-sosmed" class="btn btn-sm btn-main">
                                                        <i class="bi bi-plus-circle"></i> Tambah Sosial Media
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-main toggle-edit">
                                                        <i class="bi bi-pencil"></i> Sunting
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="container-sosmed">
                                            @forelse ($data->userSosialMedia as $index => $sosmed )
                                            <div class="row mt-2 sosmed">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="fw-bold text-capitalize">Sosial media ke {{ $index+1 }}</div>
                                                    <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $sosmed->id }}">
                                                        <i class="bi bi-trash" data-bs-toggle="tooltip" data-bs-title="Hapus Keluarga" data-bs-custom-class="danger-tooltip"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="id_user_sosmed[]" value="{{ $sosmed->id }}">
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Nama Platform</label>
                                                    <select name="id_platform[]" class="form-select @error('id_platform.' . $index) is-invalid @enderror" disabled required>
                                                        @foreach ($dataSosialMedia as $platform )
                                                            <option value="{{ $platform->id }}" @if ($sosmed->id_platform == $platform->id) selected @endif>{{ $platform->nama_platform }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('id_platform.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Username</label>
                                                    <input name="username[]" class="form-control @error('username.' . $index) is-invalid @enderror" type="text" value="{{ old('username.' . $index, $sosmed->username) }}" disabled required>
                                                    @error('username.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="formFile" class="form-label">Link Sosial Media</label>
                                                    <input name="link[]" class="form-control  @error('link.' . $index) is-invalid @enderror" type="text" value="{{ old('link.' . $index, $sosmed->link) }}" disabled required>
                                                    @error('link.' . $index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            @empty
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="text-center h1">
                                                            Tidak ada data untuk ditampilkan!
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- button submit --}}
                        <div class="row">
                            <button type="submit" class="btn btn-main">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="profile-change-password">
                    <!-- Change Password Form -->
                    <div class="card">
                        <div class="card-body pt-3">
                            <form action="{{ route('profile.password.update') }}" method="post">
                                @csrf @method('put')
                                <div class="row mb-3">
                                    <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Kata Sandi Sekarang</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="password_lama" type="password" class="form-control @error('password_lama') is-invalid @enderror" value="{{ old('password_lama') }}" required>
                                        @error('password_lama')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">Kata Sandi</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="password_baru" type="password" class="form-control @error('password_baru') is-invalid @enderror" value="{{ old('password_baru') }}" required>
                                        @error('password_baru')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Ulangi Kata Sandi Baru</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="konf_password" type="password" class="form-control @error('konf_password') is-invalid @enderror" value="{{ old('konf_password') }}" required>
                                        @error('konf_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-main">Ubah Kata Sandi</button>
                                </div>
                            </form><!-- End Change Password Form -->
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    @foreach ($data->keluarga as $keluarga )
        {{-- hapus modal --}}
        <div class="modal fade" id="hapusModal{{ $keluarga->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Hapus Keluarga</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('keluarga.destroy',['id_keluarga'=> $keluarga->id]) }}" method="post">
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
    @endforeach

    @foreach ($data->userSosialMedia as $userSosmed )
        {{-- hapus modal --}}
        <div class="modal fade" id="hapusModal{{ $userSosmed->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Hapus Sosial Media</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user.sosmed.destroy',['id_user_sosmed'=> $userSosmed->id]) }}" method="post">
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
    @endforeach

    @include('components.footer')
@endsection
