@extends('karyawan.master')

@section('title', 'Data Absensi')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('karyawan.absensi') }}">Absensi</a></li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Data Absensi Pegawai</h5>
        </div>

        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <!-- Filter Section -->
          <div class="row mb-3">
            <div class="col-12">
              <form method="GET" action="{{ route('karyawan.absensi') }}" class="row g-2">
                <div class="col-md-3">
                  <select name="bulan" class="form-select">
                    <option value="">Pilih Bulan</option>
                    @foreach($bulanList as $key => $bulan)
                      <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>
                        {{ $bulan }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <select name="tahun" class="form-select">
                    <option value="">Pilih Tahun</option>
                    @foreach($tahunList as $tahun)
                      <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                        {{ $tahun }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach($statusList as $status)
                      <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-2">
                  <a href="{{ route('karyawan.absensi') }}" class="btn btn-secondary">Reset</a>
                </div>
              </form>
            </div>
          </div>

          <!-- Info Section -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted">
                    Menampilkan {{ $absensi->firstItem() }} - {{ $absensi->lastItem() }} 
                    dari {{ $absensi->total() }} data
                  </small>
                </div>
                <div>
                  <small class="text-muted">
                    Halaman {{ $absensi->currentPage() }} dari {{ $absensi->lastPage() }}
                  </small>
                </div>
              </div>
            </div>
          </div>

          <!-- Table Section -->
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Pegawai</th>
                  <th>Departemen</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Jam Masuk</th>
                  <th>Jam Pulang</th>
                  <th>Total Jam</th>
                  <th>Status Jam</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($absensi as $index => $item)
                  <tr>
                    <td>{{ $absensi->firstItem() + $index }}</td>
                    <td>{{ $item->pegawai->nama ?? 'N/A' }}</td>
                    <td>{{ $item->pegawai->departemen->nama_departemen ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td>
                      @if($item->status_kehadiran == 'Hadir')
                        <span class="badge bg-success">Hadir</span>
                      @elseif($item->status_kehadiran == 'Terlambat')
                        <span class="badge bg-warning">Terlambat</span>
                      @elseif($item->status_kehadiran == 'Izin')
                        <span class="badge bg-info">Izin</span>
                      @elseif($item->status_kehadiran == 'Sakit')
                        <span class="badge bg-secondary">Sakit</span>
                      @else
                        <span class="badge bg-danger">Tidak Hadir</span>
                      @endif
                    </td>
                    <td>
                      {{ $item->waktu_masuk ? \Carbon\Carbon::parse($item->waktu_masuk)->format('H:i') : '-' }}
                    </td>
                    <td>
                      {{ $item->waktu_pulang ? \Carbon\Carbon::parse($item->waktu_pulang)->format('H:i') : '-' }}
                    </td>
                    <td>{{ $item->durasi_kerja ?? '-' }}</td>
                    <td>
                      @if($item->status_jam_kerja == 'Memenuhi')
                        <span class="badge bg-success">Memenuhi</span>
                      @elseif($item->status_jam_kerja == 'Setengah Hari')
                        <span class="badge bg-warning">Setengah Hari</span>
                      @elseif($item->status_jam_kerja == 'Kurang')
                        <span class="badge bg-danger">Kurang</span>
                      @else
                        <span class="badge bg-secondary">-</span>
                      @endif
                    </td>
                    <td>
                      <button type="button" 
                              class="btn btn-sm btn-info" 
                              data-bs-toggle="modal" 
                              data-bs-target="#detailModal{{ $item->id_kehadiran }}">
                        Detail
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center">
                      @if(request()->hasAny(['bulan', 'tahun', 'status']))
                        Tidak ada data absensi sesuai filter yang dipilih
                      @else
                        Tidak ada data absensi
                      @endif
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Pagination Section -->
 <div class="d-flex justify-content-between align-items-center mt-3">
  <small class="text-muted">
    Menampilkan {{ $absensi->firstItem() }}-{{ $absensi->lastItem() }} dari {{ $absensi->total() }} data
  </small>
  {{ $absensi->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Modals -->
  @foreach($absensi as $item)
    <div class="modal fade" id="detailModal{{ $item->id_kehadiran }}" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Absensi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <strong>Nama Pegawai:</strong><br>
                {{ $item->pegawai->nama ?? 'N/A' }}
              </div>
              <div class="col-12">
                <strong>Departemen:</strong><br>
                {{ $item->pegawai->departemen->nama_departemen ?? 'N/A' }}
              </div>
              <div class="col-6">
                <strong>Tanggal:</strong><br>
                {{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}
              </div>
              <div class="col-6">
                <strong>Status Kehadiran:</strong><br>
                @if($item->status_kehadiran == 'Hadir')
                  <span class="badge bg-success">Hadir</span>
                @elseif($item->status_kehadiran == 'Terlambat')
                  <span class="badge bg-warning">Terlambat</span>
                @elseif($item->status_kehadiran == 'Izin')
                  <span class="badge bg-info">Izin</span>
                @elseif($item->status_kehadiran == 'Sakit')
                  <span class="badge bg-secondary">Sakit</span>
                @else
                  <span class="badge bg-danger">Tidak Hadir</span>
                @endif
              </div>
              <div class="col-6">
                <strong>Jam Masuk:</strong><br>
                {{ $item->waktu_masuk ? \Carbon\Carbon::parse($item->waktu_masuk)->format('H:i') . ' WIB' : '-' }}
              </div>
              <div class="col-6">
                <strong>Jam Pulang:</strong><br>
                {{ $item->waktu_pulang ? \Carbon\Carbon::parse($item->waktu_pulang)->format('H:i') . ' WIB' : '-' }}
              </div>
              <div class="col-6">
                <strong>Total Jam Kerja:</strong><br>
                {{ $item->durasi_kerja ?? '-' }}
              </div>
              <div class="col-6">
                <strong>Status Jam Kerja:</strong><br>
                @if($item->status_jam_kerja == 'Memenuhi')
                  <span class="badge bg-success">Memenuhi</span>
                @elseif($item->status_jam_kerja == 'Setengah Hari')
                  <span class="badge bg-warning">Setengah Hari</span>
                @elseif($item->status_jam_kerja == 'Kurang')
                  <span class="badge bg-danger">Kurang</span>
                @else
                  <span class="badge bg-secondary">-</span>
                @endif
              </div>
              @if($item->lokasi_kantor)
                <div class="col-12">
                  <strong>Lokasi Kantor:</strong><br>
                  {{ $item->lokasiKantor->nama_lokasi ?? 'N/A' }}
                </div>
              @endif
              @if($item->keterangan)
                <div class="col-12">
                  <strong>Keterangan:</strong><br>
                  <div class="p-2 bg-light rounded">
                    {{ $item->keterangan }}
                  </div>
                </div>
              @endif
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection