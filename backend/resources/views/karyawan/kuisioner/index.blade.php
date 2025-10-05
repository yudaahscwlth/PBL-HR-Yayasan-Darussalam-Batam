@extends('karyawan.master')

@section('title', 'Kuisioner Penilaian Kinerja')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <h3 class="text-primary">KUISIONER PENILAIAN KINERJA</h3>
            <p>Selamat datang, <strong>{{ $pegawai->nama }}</strong></p>
            <small class="text-muted">Departemen: {{ $nama_departemen }}</small>
            <small class="text-muted d-block">ID Pegawai: {{ $pegawai->id_pegawai }}</small>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Form Pilih Periode -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Pilih Periode Penilaian</h5>
        </div>
        <div class="card-body">
            <form id="periodeForm">
                <div class="mb-3">
                    <select class="form-select" id="periode_penilaian" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periode as $p)
                            <option value="{{ $p->id }}">{{ $p->tahun }} - {{ $p->semester }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" id="loadPegawaiBtn" class="btn btn-primary" disabled>
                    Tampilkan Semua Pegawai
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Semua Pegawai -->
    <div id="pegawaiSection" class="card" style="display: none;">
        <div class="card-header">
            <h5>Daftar Semua Pegawai</h5>
            <small class="text-muted">Menampilkan semua pegawai dari seluruh departemen</small>
        </div>
        <div class="card-body">
            <div id="pegawaiList"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodeSelect = document.getElementById('periode_penilaian');
    const loadPegawaiBtn = document.getElementById('loadPegawaiBtn');
    const pegawaiSection = document.getElementById('pegawaiSection');
    const pegawaiList = document.getElementById('pegawaiList');
    const currentPegawaiId = {{ $pegawai->id_pegawai ?? 'null' }};
    let allPegawaiData = [];

    periodeSelect.addEventListener('change', function() {
        loadPegawaiBtn.disabled = !this.value;
        pegawaiSection.style.display = 'none';
    });

    loadPegawaiBtn.addEventListener('click', function() {
        const periodeId = periodeSelect.value;
        if (!periodeId) {
            alert('Silakan pilih periode terlebih dahulu');
            return;
        }
        pegawaiList.innerHTML = '<div class="text-center"><div class="spinner-border"></div><p>Loading semua pegawai...</p></div>';
        pegawaiSection.style.display = 'block';

        // Kirim periode_id sebagai parameter
        fetch(`/pegawai/kuisioner/get-all-pegawai?periode_id=${periodeId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    allPegawaiData = data.data;
                    displayAllPegawai(data.data, periodeId);
                } else {
                    pegawaiList.innerHTML = `
                        <div class="alert alert-warning">
                            ${data.message || 'Tidak ada pegawai ditemukan'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                pegawaiList.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Gagal memuat data pegawai</strong><br>
                        Error: ${error.message}
                    </div>
                `;
            });
    });

    function displayAllPegawai(pegawaiData, periodeId) {
        if (!pegawaiData || pegawaiData.length === 0) {
            pegawaiList.innerHTML = `
                <div class="alert alert-info">
                    <strong>Tidak ada pegawai lain ditemukan</strong>
                </div>
            `;
            return;
        }

        let html = '<div class="row">';
        let displayedCount = 0;

        const groupedByDepartemen = {};
        pegawaiData.forEach(pegawai => {
            const dept = pegawai.departemen || 'Tidak ada departemen';
            if (!groupedByDepartemen[dept]) {
                groupedByDepartemen[dept] = [];
            }
            groupedByDepartemen[dept].push(pegawai);
        });

        Object.keys(groupedByDepartemen).sort().forEach(departemen => {
            html += `<div class="col-12 mb-3"><h6 class="text-primary border-bottom pb-2">${departemen}</h6></div>`;
            groupedByDepartemen[departemen].forEach(pegawai => {
                if (pegawai.id_pegawai === currentPegawaiId) return;
                displayedCount++;
                
                // Tentukan status badge dan tombol berdasarkan status penilaian
                let statusBadge = '';
                let actionButtons = '';
                
                if (pegawai.status_penilaian === 'selesai') {
                    statusBadge = '<span class="badge bg-success">Selesai</span>';
                    actionButtons = `
                        <button class="btn btn-outline-info btn-sm" onclick="lihatHasil(${periodeId}, ${pegawai.id_pegawai})">
                            Lihat Hasil
                        </button>
                    `;
                } else {
                    statusBadge = '<span class="badge bg-warning">Belum Selesai</span>';
                    actionButtons = `
                        <button class="btn btn-primary btn-sm" onclick="mulaiKuisioner(${periodeId}, ${pegawai.id_pegawai})">
                            Mulai Kuisioner
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="lihatHasil(${periodeId}, ${pegawai.id_pegawai})">
                            Lihat Hasil
                        </button>
                    `;
                }
                
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">${pegawai.nama}</h6>
                                    ${statusBadge}
                                </div>
                                <p class="text-muted small mb-1">${pegawai.jabatan} ${pegawai.departemen}</p>
                                <p class="text-muted small mb-1">No: ${pegawai.no_hp || 'N/A'}</p>
                                <p class="text-muted small mb-1">Departemen: ${pegawai.departemen || 'N/A'}</p>
                                <div class="d-flex gap-2">
                                    ${actionButtons}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        });

        html += '</div>';

        if (displayedCount === 0) {
            html = '<div class="alert alert-warning">Tidak ada pegawai yang dapat dinilai</div>';
        } else {
            html = `<div class="alert alert-info mb-3">Menampilkan ${displayedCount} pegawai dari seluruh departemen</div>` + html;
        }

        pegawaiList.innerHTML = html;
    }
});

function mulaiKuisioner(periodeId, idPegawai) {
    if (confirm('Mulai kuisioner untuk pegawai ini?')) {
        window.location.href = `/pegawai/kuisioner/${periodeId}/${idPegawai}`;
    }
}

function lihatHasil(periodeId, idPegawai) {
    window.location.href = `/pegawai/kuisioner/${periodeId}/${idPegawai}/result`;
}
</script>

<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}
.spinner-border {
    width: 2rem;
    height: 2rem;
}
@media (max-width: 768px) {
    .d-flex.gap-2 .btn {
        flex: 1;
    }
}
.border-bottom {
    border-bottom: 2px solid #dee2e6 !important;
}
.badge {
    font-size: 0.75rem;
}
</style>
@endsection