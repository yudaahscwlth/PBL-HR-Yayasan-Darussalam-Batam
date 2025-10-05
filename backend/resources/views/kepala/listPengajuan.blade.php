<!-- resources/views/admin/listPengajuan.blade.php -->
@extends('kepala.master')

@section('title', 'Validasi Pengajuan Cuti - FIFO Queue')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ url('/list-pengajuan') }}">Pengajuan Cuti</a></li>
@endsection

@section('content')
   <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5>Validasi Pengajuan Cuti - FIFO Queue</h5>
            <small>
              Sistem validasi dengan prinsip FIFO (First In First Out). Pengajuan yang masuk lebih dulu diproses lebih dulu.
            </small>
          </div>
          <div class="d-flex gap-2">
            <!-- Queue Info -->
            <div class="badge bg-info fs-6 p-2">
              <i class="fas fa-queue"></i> Queue: {{ $pending ?? 0 }} menunggu
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Queue Status Info -->
          <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle"></i>
            <strong>Sistem Queue FIFO:</strong> Pengajuan diproses berdasarkan urutan waktu pengajuan (yang pertama masuk, pertama diproses).
            @if($pending > 0)
              Saat ini ada <strong>{{ $pending }}</strong> pengajuan dalam antrian.
            @endif
          </div>

          <!-- Summary Cards -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card bg-warning text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 class="mb-0">{{ $pending ?? 0 }}</h4>
                      <small>Dalam Queue</small>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-success text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 class="mb-0">{{ $approved ?? 0 }}</h4>
                      <small>Disetujui</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-danger text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 class="mb-0">{{ $rejected ?? 0 }}</h4>
                      <small>Ditolak</small>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-info text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 class="mb-0">{{ $total ?? 0 }}</h4>
                      <small>Total Pengajuan</small>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="dt-responsive table-responsive">
            <table id="cutiTable" class="table table-striped table-bordered nowrap">
              <thead>
                <tr>
                  <th>Posisi Queue</th>
                  <th>Nama Pegawai</th>
                  <th>Departemen</th>
                  <th>Jenis Cuti</th>
                  <th>Tanggal Mulai</th>
                  <th>Tanggal Selesai</th>
                  <th>Durasi</th>
                  <th>Status</th>
                  <th>Tanggal Pengajuan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              @forelse($pengajuan_cuti as $index => $cuti)
              @php
                // Calculate badge class for status
                $status = $cuti->status_cuti;
                switch ($status) {
                    case 'Disetujui':
                        $badgeClass = 'bg-success';
                        break;
                    case 'Ditolak':
                        $badgeClass = 'bg-danger';
                        break;
                    default:
                        $badgeClass = 'bg-warning';
                }
                
                // Calculate queue position for pending items
                $queuePosition = null;
                if ($status == 'Menunggu') {
                    $queuePosition = $loop->iteration;
                }
              @endphp
              <tr class="{{ $status == 'Menunggu' ? 'table-warning' : '' }}">
                  <td>
                    @if($queuePosition)
                      <span class="badge bg-primary">
                        <i class="fas fa-sort-numeric-up"></i> #{{ $queuePosition }}
                      </span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="{{ $cuti->pegawai && $cuti->pegawai->foto ? asset('uploads/pegawai/'.$cuti->pegawai->foto) : asset('assets/images/user/avatar-1.jpg') }}" 
                           class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                      <div>
                        <strong>{{ $cuti->pegawai ? $cuti->pegawai->nama : 'N/A' }}</strong><br>
                        <small class="text-muted">{{ $cuti->pegawai && $cuti->pegawai->jabatan ? $cuti->pegawai->jabatan->nama_jabatan : 'N/A' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>{{ $cuti->pegawai && $cuti->pegawai->departemen ? $cuti->pegawai->departemen->nama_departemen : 'N/A' }}</td>
                  <td>
                    <span class="badge bg-light text-dark">
                      {{ $cuti->jenisCuti ? $cuti->jenisCuti->nama_jenis_cuti : 'N/A' }}
                    </span>
                  </td>
                  <td>{{ $cuti->tanggal_mulai ? \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y') : 'N/A' }}</td>
                  <td>{{ $cuti->tanggal_selesai ? \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y') : 'N/A' }}</td>
                  <td>
                    <span class="badge bg-primary">
                      {{ $cuti->jumlah_hari ?? 0 }} hari
                    </span>
                  </td>
                  <td>
                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                    @if($queuePosition == 1)
                      <small class="d-block text-success mt-1">
                        <i class="fas fa-arrow-right"></i> Prioritas Tertinggi
                      </small>
                    @endif
                  </td>
                  <td>{{ $cuti->tanggal_pengajuan ? \Carbon\Carbon::parse($cuti->tanggal_pengajuan)->format('d/m/Y H:i') : 'N/A' }}</td>
                  <td>
                    <div class="d-flex gap-1">
                      <!-- Detail Button -->
                      <button type="button" 
                              class="btn btn-sm btn-outline-info" 
                              data-bs-toggle="modal" 
                              data-bs-target="#detailModal{{ $cuti->id_cuti }}"
                              title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                      </button>

                      @if($cuti->status_cuti == 'Menunggu')
                      <!-- Approve Button -->
                      <button type="button" 
                              class="btn btn-sm btn-success" 
                              data-bs-toggle="modal" 
                              data-bs-target="#approveModal{{ $cuti->id_cuti }}"
                              title="Setujui (Queue #{{ $queuePosition }})">
                        <i class="fas fa-check"></i>
                      </button>

                      <!-- Reject Button -->
                      <button type="button" 
                              class="btn btn-sm btn-danger" 
                              data-bs-toggle="modal" 
                              data-bs-target="#rejectModal{{ $cuti->id_cuti }}"
                              title="Tolak (Queue #{{ $queuePosition }})">
                        <i class="fas fa-times"></i>
                      </button>
                      @endif

                      @if($cuti->status_cuti != 'Menunggu')
                      <!-- History/Log Button -->
                      <button type="button" 
                              class="btn btn-sm btn-outline-secondary" 
                              title="Riwayat">
                        <i class="fas fa-history"></i>
                      </button>
                      @endif
                    </div>
                  </td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center">
                  <div class="py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data pengajuan cuti</h5>
                    <p class="text-muted">Belum ada pengajuan cuti yang perlu divalidasi.</p>
                  </div>
                </td>
              </tr>
              @endforelse
              </tbody>
              <tfoot>
                <tr>
                  <th>Posisi Queue</th>
                  <th>Nama Pegawai</th>
                  <th>Departemen</th>
                  <th>Jenis Cuti</th>
                  <th>Tanggal Mulai</th>
                  <th>Tanggal Selesai</th>
                  <th>Durasi</th>
                  <th>Status</th>
                  <th>Tanggal Pengajuan</th>
                  <th>Aksi</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modals -->
  @foreach($pengajuan_cuti as $cuti)
  <!-- Detail Modal dengan info queue -->
  <div class="modal fade" id="detailModal{{ $cuti->id_cuti }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Pengajuan Cuti</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if($cuti->status_cuti == 'Menunggu')
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Posisi dalam Queue:</strong> #{{ $loop->iteration }} dari {{ $pending }} pengajuan yang menunggu
          </div>
          @endif
          
          <div class="row">
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <td><strong>Nama Pegawai:</strong></td>
                  <td>{{ $cuti->pegawai ? $cuti->pegawai->nama : 'N/A' }}</td>
                </tr>
                <tr>
                  <td><strong>Departemen:</strong></td>
                  <td>{{ $cuti->pegawai && $cuti->pegawai->departemen ? $cuti->pegawai->departemen->nama_departemen : 'N/A' }}</td>
                </tr>
                <tr>
                  <td><strong>Jenis Cuti:</strong></td>
                  <td>{{ $cuti->jenisCuti ? $cuti->jenisCuti->nama_jenis_cuti : 'N/A' }}</td>
                </tr>
                <tr>
                  <td><strong>Tanggal Mulai:</strong></td>
                  <td>{{ $cuti->tanggal_mulai ? \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d F Y') : 'N/A' }}</td>
                </tr>
                <tr>
                  <td><strong>Tanggal Selesai:</strong></td>
                  <td>{{ $cuti->tanggal_selesai ? \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d F Y') : 'N/A' }}</td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <td><strong>Durasi:</strong></td>
                  <td>{{ $cuti->jumlah_hari ?? 0 }} hari</td>
                </tr>
                <tr>
                  <td><strong>Status:</strong></td>
                  <td>
                    <span class="badge {{ $cuti->status_cuti == 'Disetujui' ? 'bg-success' : ($cuti->status_cuti == 'Ditolak' ? 'bg-danger' : 'bg-warning') }}">
                      {{ $cuti->status_cuti }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td><strong>Sisa Cuti:</strong></td>
                  <td>{{ $cuti->pegawai && isset($cuti->pegawai->sisa_cuti) ? $cuti->pegawai->sisa_cuti : 'N/A' }} hari</td>
                </tr>
                <tr>
                  <td><strong>Tanggal Pengajuan:</strong></td>
                  <td>{{ $cuti->tanggal_pengajuan ? \Carbon\Carbon::parse($cuti->tanggal_pengajuan)->format('d F Y H:i') : 'N/A' }}</td>
                </tr>
              </table>
            </div>
          </div>
          
          @if($cuti->keterangan)
          <div class="mt-3">
            <strong>Keterangan:</strong>
            <p class="mt-2 p-3 bg-light rounded">
              {{ $cuti->keterangan }}
            </p>
          </div>
          @endif
        </div>
        <div class="modal-footer">
          @if($cuti->status_cuti == 'Menunggu')
          <button type="button" class="btn btn-success" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#approveModal{{ $cuti->id_cuti }}">
            <i class="fas fa-check"></i> Setujui
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $cuti->id_cuti }}">
            <i class="fas fa-times"></i> Tolak
          </button>
          @endif
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Approve Modal -->
  <div class="modal fade" id="approveModal{{ $cuti->id_cuti }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Konfirmasi Persetujuan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('kepala.cuti.updateStatus', $cuti->id_cuti) }}" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" name="status" value="Disetujui">
          <div class="modal-body">
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              Apakah Anda yakin ingin menyetujui pengajuan cuti untuk <strong>{{ $cuti->pegawai ? $cuti->pegawai->nama : 'N/A' }}</strong>?
              @if($cuti->status_cuti == 'Menunggu')
              <br><small>Posisi dalam queue: #{{ $loop->iteration }}</small>
              @endif
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-check"></i> Ya, Setujui
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal{{ $cuti->id_cuti }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tolak Pengajuan Cuti</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('kepala.cuti.updateStatus', $cuti->id_cuti) }}" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" name="status" value="Ditolak">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Alasan Penolakan (Opsional):</label>
              <textarea class="form-control" name="keterangan" rows="3" 
                        placeholder="Masukkan alasan penolakan jika diperlukan..."></textarea>
            </div>
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle"></i>
              Pastikan keputusan penolakan sudah sesuai dengan kebijakan perusahaan.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">
              <i class="fas fa-times"></i> Tolak Pengajuan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endforeach
@endsection

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="{{ asset('assets/js/plugins/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    $(document).ready(function() {
      // Initialize DataTable with FIFO ordering
      var table = $('#cutiTable').DataTable({
        order: [[8, 'asc']], // Sort by tanggal pengajuan (ascending) = FIFO
        columnDefs: [
          { orderable: false, targets: [9] }, // Disable sorting for action column
          { 
            targets: [0], // Queue position column
            render: function(data, type, row, meta) {
              if (type === 'display' && data && data.includes('#')) {
                return data;
              }
              return data;
            }
          }
        ],
        responsive: true,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        }
      });
      
      // Auto-refresh every 30 seconds to update queue positions
      setInterval(function() {
        // Only refresh if there are pending items
        if ({{ $pending ?? 0 }} > 0) {
          location.reload();
        }
      }, 30000);
    });

    // Show success/error messages
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        timer: 3000,
        showConfirmButton: false
      });
    @endif

    @if($errors->any())
      Swal.fire({
        icon: 'error',
        title: 'Validation Error!',
        html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
        timer: 5000,
        showConfirmButton: true
      });
    @endif
  </script>

  <style>
    .table td {
      vertical-align: middle;
    }
    
    .badge {
      font-size: 0.75em;
    }
    
    .card {
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .opacity-75 {
      opacity: 0.75;
    }
    
    .gap-1 {
      gap: 0.25rem !important;
    }
    
    .gap-2 {
      gap: 0.5rem !important;
    }
    
    .rounded-circle {
      border-radius: 50% !important;
    }

    /* Highlight pending items in queue */
    .table-warning {
      background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    /* Queue position styling */
    .badge.bg-primary {
      background-color: #0d6efd !important;
    }

    /* DataTables responsive improvements */
    @media (max-width: 768px) {
      .table-responsive {
        font-size: 0.875rem;
      }
      
      .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
      }
    }

    /* Modal improvements */
    .modal-dialog {
      margin: 1.75rem auto;
    }

    @media (max-width: 576px) {
      .modal-dialog {
        margin: 0.5rem;
      }
    }
  </style>
@endpush