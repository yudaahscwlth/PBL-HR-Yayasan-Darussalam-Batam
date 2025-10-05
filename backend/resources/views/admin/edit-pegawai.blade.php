@extends('admin.master')

@section('title', 'Edit Pegawai')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ url('/pegawai') }}">Pegawai</a></li>
  <li class="breadcrumb-item active">Edit Pegawai</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5>Edit Data Pegawai</h5>
            <small>
              Form untuk mengubah data pegawai yang sudah ada dalam sistem.
            </small>
          </div>
          <div>
            <a href="{{ route('admin.karyawan') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
        <div class="card-body">
          <!-- Form Edit Pegawai -->
          <form action="{{ route('pegawai.update', $pegawai->id_pegawai) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                       id="nama" name="nama" value="{{ old('nama', $pegawai->nama) }}" required>
                @error('nama')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" 
                       id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}" required>
                @error('tempat_lahir')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                       id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->format('Y-m-d') : '') }}" required>
                @error('tanggal_lahir')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-select @error('jenis_kelamin') is-invalid @enderror" 
                        id="jenis_kelamin" name="jenis_kelamin" required>
                  <option value="">Pilih Jenis Kelamin</option>
                  <option value="L" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                  <option value="P" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="mb-3">
              <label for="alamat" class="form-label">Alamat</label>
              <textarea class="form-control @error('alamat') is-invalid @enderror" 
                        id="alamat" name="alamat" rows="3" required>{{ old('alamat', $pegawai->alamat) }}</textarea>
              @error('alamat')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="no_hp" class="form-label">No. Handphone</label>
                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" 
                       id="no_hp" name="no_hp" value="{{ old('no_hp', $pegawai->no_hp) }}" required>
                @error('no_hp')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       id="email" name="email" value="{{ old('email', $pegawai->email) }}" required>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="id_departemen" class="form-label">Departemen</label>
                <select class="form-select @error('id_departemen') is-invalid @enderror" 
                        id="id_departemen" name="id_departemen" required>
                  <option value="">Pilih Departemen</option>
                  @foreach($departemen as $dept)
                    <option value="{{ $dept->id_departemen }}" 
                            {{ old('id_departemen', $pegawai->id_departemen) == $dept->id_departemen ? 'selected' : '' }}>
                      {{ $dept->nama_departemen }}
                    </option>
                  @endforeach
                </select>
                @error('id_departemen')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 mb-3" id="jabatan-container">
                <label for="id_jabatan" class="form-label">Jabatan</label>
                <select class="form-select @error('id_jabatan') is-invalid @enderror" 
                        id="id_jabatan" name="id_jabatan">
                  <option value="">Pilih Jabatan</option>
                  @foreach($jabatan as $jbt)
                    <option value="{{ $jbt->id_jabatan }}" 
                            {{ old('id_jabatan', $pegawai->id_jabatan) == $jbt->id_jabatan ? 'selected' : '' }}>
                      {{ $jbt->nama_jabatan }}
                    </option>
                  @endforeach
                </select>
                @error('id_jabatan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
                       id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('Y-m-d')) }}"required>
                @error('tanggal_masuk')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6 mb-3">
                <label for="jatah_tahunan" class="form-label">Jatah Cuti Tahunan</label>
                <input type="number" class="form-control @error('jatahtahunan') is-invalid @enderror" 
                       id="jatah_tahunan" name="jatahtahunan" value="{{ old('jatahtahunan', $pegawai->jatahtahunan ?? 0) }}">
                @error('jatahtahunan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="mb-3">
              <label for="foto" class="form-label">Foto</label>
              <input type="file" class="form-control @error('foto') is-invalid @enderror" 
                     id="foto" name="foto">
              <small class="text-muted">Format: JPG, PNG, JPEG. Maks: 2MB (Kosongkan jika tidak ingin mengubah foto)</small>
              @error('foto')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              
              <!-- Tampilkan foto saat ini jika ada -->
              @if($pegawai->foto)
                <div class="mt-2">
                  <small class="text-info">Foto saat ini:</small>
                  <div class="mt-1">
                    <img src="{{ asset('uploads/pegawai/' . $pegawai2->foto) }}" 
                         alt="Foto {{ $pegawai->nama }}" 
                         class="img-thumbnail" 
                         style="max-width: 150px; max-height: 150px;">
                  </div>
                </div>
              @endif
            </div>
            
            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.karyawan') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Data
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')

  <script>
    // Script untuk menangani tampilan field jabatan berdasarkan departemen yang dipilih
    document.addEventListener('DOMContentLoaded', function () {
      const departemenSelect = document.getElementById('id_departemen');
      const jabatanContainer = document.getElementById('jabatan-container');
      const jabatanSelect = document.getElementById('id_jabatan');

      // Fungsi untuk menampilkan/menyembunyikan field jabatan
      function toggleJabatanField() {
        if (departemenSelect.value !== "") {
          jabatanContainer.style.display = 'block';
        } else {
          jabatanContainer.style.display = 'none';
          jabatanSelect.value = ""; // Reset pilihan jabatan
        }
      }

      // Jalankan saat halaman dimuat (untuk data yang sudah ada)
      toggleJabatanField();

      // Event listener untuk perubahan departemen
      departemenSelect.addEventListener('change', toggleJabatanField);
    });
  </script>
@endpush