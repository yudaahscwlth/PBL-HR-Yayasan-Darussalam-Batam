@extends('admin.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Edit Periode Penilaian</h3>
                        <a href="{{ route('periode.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('periode.update', $periode->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Nama Periode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_periode') is-invalid @enderror" 
                                           name="nama_periode" value="{{ old('nama_periode', $periode->nama_periode) }}" required>
                                    @error('nama_periode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tahun') is-invalid @enderror" name="tahun" required>
                                        <option value="">Pilih Tahun</option>
                                        @for($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                            <option value="{{ $year }}" {{ old('tahun', $periode->tahun) == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select class="form-select @error('semester') is-invalid @enderror" name="semester" required>
                                        <option value="">Pilih Semester</option>
                                        <option value="1" {{ old('semester', $periode->semester) == '1' ? 'selected' : '' }}>Semester 1</option>
                                        <option value="2" {{ old('semester', $periode->semester) == '2' ? 'selected' : '' }}>Semester 2</option>
                                    </select>
                                    @error('semester')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           name="tanggal_mulai" value="{{ old('tanggal_mulai', $periode->tanggal_mulai ? \Carbon\Carbon::parse($periode->tanggal_mulai)->format('Y-m-d') : '') }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           name="tanggal_selesai" value="{{ old('tanggal_selesai', $periode->tanggal_selesai ? \Carbon\Carbon::parse($periode->tanggal_selesai)->format('Y-m-d') : '') }}" required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="belum_dibuka" {{ old('status', $periode->status) == 'belum_dibuka' ? 'selected' : '' }}>Draft</option>
                                        <option value="aktif" {{ old('status', $periode->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="selesai" {{ old('status', $periode->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('periode.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto hide alerts after 3 seconds
setTimeout(() => $('.alert').fadeOut(), 3000);

// Update tanggal_selesai minimum when tanggal_mulai changes
document.addEventListener('DOMContentLoaded', function() {
    const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]');
    const tanggalSelesai = document.querySelector('input[name="tanggal_selesai"]');
    
    // Set initial minimum for tanggal_selesai
    if (tanggalMulai.value) {
        tanggalSelesai.min = tanggalMulai.value;
    }
    
    // Update minimum when tanggal_mulai changes
    tanggalMulai.addEventListener('change', function() {
        tanggalSelesai.min = this.value;
        // Reset tanggal_selesai if it's before tanggal_mulai
        if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
            tanggalSelesai.value = '';
        }
    });
});
</script>
@endpush