@extends('admin.master')

@section('title', 'Log Sistem')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.log-sistem.index') }}">Log Sistem</a></li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Log Sistem (Terbaru)</h5>
            </div>
            <div class="card-body">

                {{-- Filter --}}
                <form class="row g-2 mb-3" method="GET" action="{{ route('admin.log-sistem.index') }}">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari aksi atau user..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tanggal_mulai" class="form-control"
                            value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tanggal_selesai" class="form-control"
                            value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">
                            <i class="ti ti-filter"></i> Filter
                        </button>
                    </div>
                </form>

                {{-- Log List --}}
                @forelse($logs as $log)
                    <div class="py-2 border-bottom">
                        <small class="text-muted">
                            [{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}]
                        </small>
                        <strong>{{ $log->nama_user ?? 'System' }}</strong> — 
                        <span class="text-muted">{{ $log->keterangan }}</span>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="ti ti-notes fa-2x mb-2"></i>
                        <p>Tidak ada log ditemukan.</p>
                    </div>
                @endforelse

                <small class="text-muted d-block mt-3">
                    Menampilkan {{ $logs->count() }} log terbaru
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
