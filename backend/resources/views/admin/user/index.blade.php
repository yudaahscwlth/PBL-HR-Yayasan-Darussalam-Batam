@extends('admin.master')

@section('title', 'Manajemen User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">User</a></li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data User</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.user.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Pegawai</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->pegawai->nama ?? 'N/A' }}</td>
                                <td>{{ $user->pegawai->departemen->nama_departemen ?? 'N/A' }}</td>
                                <td>{{ $user->pegawai->jabatan->nama_jabatan ?? 'N/A' }}</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger text-white">{{ ucfirst($user->role) }}</span>
                                    @else
                                        <span class="badge bg-primary text-white">{{ ucfirst($user->role) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success text-white">Aktif</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.user.edit', $user->id_user) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteUser({{ $user->id_user }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }} dari {{ $users->total() }} data
                </small>
                {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card untuk Pegawai yang Belum Menjadi User -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pegawai Belum Menjadi User</h3>
                {{-- <div class="card-tools">
                    <button type="button" class="btn btn-success" onclick="createMultipleUsers()">
                        <i class="fas fa-users"></i> Buat User untuk Semua
                    </button>
                </div> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawaiTanpaUser as $index => $pegawai2)
                            <tr>
                                <td>
                                    <input type="checkbox" class="pegawai-checkbox" value="{{ $pegawai2->id_pegawai }}">
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $pegawai2->nama }}</td>
                                <td>{{ $pegawai2->departemen->nama_departemen ?? 'N/A' }}</td>
                                <td>{{ $pegawai2->jabatan->nama_jabatan ?? 'N/A' }}</td>
                                <td>{{ $pegawai2->email }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="createUserForPegawai({{ $pegawai2->id_pegawai }})">
                                        <i class="fas fa-user-plus"></i> Buat User
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
function deleteUser(userId) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data user akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/user/' + userId,
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    Swal.fire(
                        'Berhasil!',
                        'User berhasil dihapus.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat menghapus user.',
                        'error'
                    );
                }
            });
        }
    });
}

function createUserForPegawai(idPegawai) {
    console.log('Creating user for pegawai ID:', idPegawai);
    
    Swal.fire({
        title: 'Buat User untuk Pegawai',
        text: "User akan dibuat otomatis dengan username dari email pegawai",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, buat user!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Sending request to create user...');
            
            $.ajax({
                url: "{{ route('admin.user.create-for-pegawai') }}", // Menggunakan route helper
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_pegawai": idPegawai
                },
                beforeSend: function(xhr) {
                    console.log('Request data:', {
                        "_token": "{{ csrf_token() }}",
                        "id_pegawai": idPegawai
                    });
                },
                success: function(response) {
                    console.log('Success response:', response);
                    
                    Swal.fire(
                        'Berhasil!',
                        response.message || 'User berhasil dibuat untuk pegawai.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON,
                        statusCode: xhr.status
                    });
                    
                    let errorMessage = 'Terjadi kesalahan saat membuat user.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Route tidak ditemukan. Pastikan route sudah didaftarkan.';
                    } else if (xhr.status === 419) {
                        errorMessage = 'CSRF token tidak valid. Silakan refresh halaman.';
                    }
                    
                    Swal.fire(
                        'Gagal!',
                        errorMessage,
                        'error'
                    );
                }
            });
        }
    });
}
</script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('alert'))
        Swal.fire({
            icon: '{{ session('alert.type') ?? 'info' }}',
            title: '{{ session('alert.title') ?? 'Info' }}',
            html: `{!! session('alert.message') !!}`,
            @if(session('alert.errors'))
                footer: '<ul class="text-start list-unstyled mb-0">{!! collect(session('alert.errors'))->map(fn($e)=>"<li>• ".$e."</li>")->implode('') !!}</ul>',
            @endif
            showConfirmButton: true
        });
    @endif

    @if(session('notifikasi'))
        Swal.fire({
            icon: '{{ session('type') ?? 'info' }}',
            title: 'Pemberitahuan',
            text: '{{ session('notifikasi') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
</script>

<style>
.badge {
    font-size: 0.875em;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-primary {
    background-color: #007bff !important;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-info {
    background-color: #17a2b8 !important;
}

.text-white {
    color: #fff !important;
}
</style>
@endpush