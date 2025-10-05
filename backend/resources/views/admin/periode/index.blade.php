@extends('admin.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Daftar Periode Penilaian</h3>
                        <a href="{{ route('periode.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Periode
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Periode</th>
                                    <th>Tahun</th>
                                    <th>Semester</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodes as $index => $periode)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $periode->nama_periode }}</td>
                                    <td>{{ $periode->tahun }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            Semester {{ $periode->semester }}
                                        </span>
                                    </td>
                                    <td>{{ $periode->tanggal_mulai ? \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $periode->tanggal_selesai ? \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $periode->status_badge ?? 'secondary' }}">
                                            {{ ucwords(str_replace('_', ' ', $periode->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('periode.kuisioner.index', $periode->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-tasks"></i> Assign
                                            </a>

                                            <a href="{{ route('periode.edit', $periode->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <form action="{{ route('periode.destroy', $periode->id) }}" 
                                                  method="POST" style="display: inline;"
                                                  onsubmit="return confirm('Hapus periode ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="py-3">
                                            <p class="text-muted">Belum ada periode penilaian.</p>
                                            <a href="{{ route('periode.create') }}" class="btn btn-primary">
                                                Buat Periode Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto hide alerts
setTimeout(() => $('.alert').fadeOut(), 3000);
</script>
@endpush