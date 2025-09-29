<div class="row">
    <div class="card bg-second text-light">
        <div class="row">
            <div class="col-4 col-md-2">
                <img src="{{ $dataProfile->profilePribadi->foto ? asset('storage/'.$dataProfile->profilePribadi->foto) : asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle my-2" width="120" height="120">
            </div>
            <div class="col-8 col-md-10 my-2">
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Nama</div>
                    <div class="col-lg-9 col-md-8">{{ $dataProfile->profilePribadi->nama_lengkap }}</div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Jabatan</div>
                    <div class="col-lg-9 col-md-8">{{ $dataProfile->profilePekerjaan->jabatan->nama_jabatan }}</div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Alamat</div>
                    <div class="col-lg-9 col-md-8">{{ $dataProfile->profilePribadi->alamat_lengkap }}</div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Email</div>
                    <div class="col-lg-9 col-md-8">{{ $dataProfile->email }}</div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Nomor Telepon</div>
                    <div class="col-lg-9 col-md-8">{{ $dataProfile->profilePribadi->no_hp }}</div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Tahun Masuk</div>
                    <div class="col-lg-9 col-md-8">{{ \Carbon\Carbon::parse($dataProfile->profilePekerjaan->tanggal_masuk)->translatedFormat('F Y') }}
                    </div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Tahun Pengabdian</div>
                    <div class="col-lg-9 col-md-8">{{ $tahunPengabdian. ' Tahun' }} {{ $bulanPengabdian. ' Bulan' }}</div>
                </div>
                <div class="row m-2">
                    <div class="col-lg-3 col-md-4 label fw-semibold">Tahun Kelahiran</div>
                    <div class="col-lg-9 col-md-8">{{ \Carbon\Carbon::parse($dataProfile->profilePribadi->tanggal_lahir)->translatedFormat('d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
