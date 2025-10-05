@extends('admin.master')

@section('title', 'Kelola Kuisioner')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Dashboard</a></li>
  <li class="breadcrumb-item active">Kelola Kuisioner</li>
@endsection

@section('content')
<div class="row">
    <!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
    <!-- Form Kuisioner -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white  ">
                    <i class="fas fa-plus-circle me-2"></i>
                    {{ isset($editKuisioner) ? 'Edit Kuisioner' : 'Tambah Kuisioner' }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($editKuisioner) ? route('admin.kuisioner.update', $editKuisioner->id) : route('admin.kuisioner.store') }}" method="POST">
                    @csrf
                    @if(isset($editKuisioner))
                        @method('PUT')
                    @endif
                    
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select @error('kategori') is-invalid @enderror" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="kinerja" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'kinerja') ? 'selected' : '' }}>Kinerja</option>
                            <option value="kedisiplinan" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'kedisiplinan') ? 'selected' : '' }}>Kedisiplinan</option>
                            <option value="komunikasi" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'komunikasi') ? 'selected' : '' }}>Komunikasi</option>
                            <option value="kerjasama" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'kerjasama') ? 'selected' : '' }}>Kerjasama</option>
                            <option value="kepemimpinan" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'kepemimpinan') ? 'selected' : '' }}>Kepemimpinan</option>
                            <option value="inovasi" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'inovasi') ? 'selected' : '' }}>Inovasi</option>
                            <option value="pelayanan" {{ (old('kategori', $editKuisioner->kategori ?? '') == 'pelayanan') ? 'selected' : '' }}>Pelayanan</option>
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="pertanyaan" class="form-label">Pertanyaan</label>
                        <textarea class="form-control @error('pertanyaan') is-invalid @enderror" 
                                  name="pertanyaan" 
                                  rows="4" 
                                  placeholder="Masukkan pertanyaan penilaian..."
                                  required>{{ old('pertanyaan', $editKuisioner->pertanyaan ?? '') }}</textarea>
                        @error('pertanyaan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="aktif" value="1"
                                   {{ old('aktif', $editKuisioner->aktif ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Status Aktif</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ isset($editKuisioner) ? 'Update' : 'Simpan' }}
                        </button>
                        @if(isset($editKuisioner))
                            <a href="{{ route('admin.kuisioner.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Kuisioner -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar Kuisioner
                </h5>
                <!-- Filter -->
                <form method="GET" class="d-flex gap-2">
                    <select class="form-select form-select-sm" name="kategori" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                {{ ucwords($kategori) }}
                            </option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('aktif') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('aktif') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                    @if(request('kategori') || request('aktif'))
                        <a href="{{ route('admin.kuisioner.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                @if($kuisioners->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Pertanyaan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kuisioners as $index => $kuisioner)
                                <tr>
                                    <td>{{ $kuisioners->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucwords($kuisioner->kategori) }}</span>
                                    </td>
                                    <td>{{ Str::limit($kuisioner->pertanyaan, 80) }}</td>
                                    <td>
                                        <span class="badge {{ $kuisioner->aktif ? 'bg-success' : 'bg-danger' }}">
                                            {{ $kuisioner->aktif ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.kuisioner.edit', $kuisioner->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.kuisioner.toggle', $kuisioner->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $kuisioner->aktif ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                <i class="fas fa-toggle-{{ $kuisioner->aktif ? 'on' : 'off' }}"></i>
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('admin.kuisioner.destroy', $kuisioner->id) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus kuisioner ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Menampilkan {{ $kuisioners->firstItem() }}-{{ $kuisioners->lastItem() }} dari {{ $kuisioners->total() }} data
                        </small>
                        {{ $kuisioners->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada kuisioner</h5>
                        <p class="text-muted">Mulai dengan menambahkan kuisioner pertama Anda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistik -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                <h4 class="text-white">{{ $totalKuisioner ?? 0 }}</h4>
                <small>Total Kuisioner</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h4 class="text-white">{{ $kuisionerAktif ?? 0 }}</h4>
                <small>Kuisioner Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-pause-circle fa-2x mb-2"></i>
                <h4 class="text-white">{{ $kuisionerNonAktif ?? 0 }}</h4>
                <small>Kuisioner Non-Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-tags fa-2x mb-2"></i>
                <h4 class="text-white">{{ count($kategoris ?? []) }}</h4>
                <small>Kategori</small>
            </div>
        </div>
    </div>
</div>


@endsection