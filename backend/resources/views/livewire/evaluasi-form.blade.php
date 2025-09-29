<div>
    <div class="d-flex flex-column">
        <div class="d-flex align-items-center mb-3">
            <div class="me-2">
                <label for="staticEmail" class="form-label">Nama Pendidik :</label>
            </div>
            <div>
                <select class="form-select form-select-sm" wire:model.change="id_pegawai">
                    <option disabled value="">Pilih Nama Pendidik</option>
                    @forelse ($dataPegawai as $pegawai )
                        <option value="{{ $pegawai->id }}" wire:key="pegawai-{{ $pegawai->id }}">{{ $pegawai->profilePribadi->nama_lengkap }}</option>
                    @empty
                        <option value=""></option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center mb-3">
            <div class="me-2">
                <label for="staticEmail" class="form-label">Tahun Ajaran :</label>
            </div>
            <div>
                <select class="form-select form-select-sm" wire:model.change="id_tahun_ajaran">
                    <option disabled value="">Pilih Tahun Ajaran</option>
                    @forelse ($dataTahunAjaran as $tahunAjaran )
                        <option value="{{ $tahunAjaran->id }}" wire:key="tahun-ajaran-{{ $tahunAjaran->id }}">{{ $tahunAjaran->nama }} {{ $tahunAjaran->semester }}</option>
                    @empty
                        <option value=""></option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center mb-3">
            <div class="me-2">
                <label for="staticEmail" class="form-label">
                    Status :
                    <span class="badge badge-sm @if ($id_pegawai && $id_tahun_ajaran) @if ($dataNilaiEvaluasi != null) text-bg-success @else text-bg-danger @endif @else text-dark @endif ">
                        @if ($id_pegawai && $id_tahun_ajaran)
                            @if ($dataNilaiEvaluasi != null)
                                Sudah Terisi
                            @else
                                Belum Terisi
                            @endif
                        @else
                            -
                        @endif
                    </span>
            </label>
            </div>
        </div>
    </div>
    @if ($id_pegawai && $id_tahun_ajaran)
        <form action="{{ route('evaluasi.store') }}" method="POST">
            @csrf @method('post')
            <input type="hidden" name="id_user" value="{{ $id_pegawai }}">
            <input type="hidden" name="id_tahun_ajaran" value="{{ $id_tahun_ajaran }}">
            <table class="table table-striped table-hover border table-bordered align-middle">
                <thead>
                    <tr class="text-center">
                        <th scope="col">No</th>
                        <th scope="col">Indikator Penilaian</th>
                        <th scope="col">
                            <div>Sangat Baik</div>
                            <div>(5)</div>
                            <div>
                                <span class="@if ($dataNilaiEvaluasi != null) disabled-text @endif text-primary text-decoration-underline" role="button" onclick="checkAllRadio(5)">Ceklis</span>
                            </div>
                        </th>
                        <th scope="col">
                            <div>Baik</div>
                            <div>(4)</div>
                            <div>
                                <span class="@if ($dataNilaiEvaluasi != null) disabled-text @endif text-primary text-decoration-underline" role="button" onclick="checkAllRadio(4)">Ceklis</span>
                            </div>
                        </th>
                        <th scope="col">
                            <div>Sedang</div>
                            <div>(3)</div>
                            <div>
                                <span class="@if ($dataNilaiEvaluasi != null) disabled-text @endif text-primary text-decoration-underline" role="button" onclick="checkAllRadio(3)">Ceklis</span>
                            </div>
                        </th>
                        <th scope="col">
                            <div>Kurang</div>
                            <div>(2)</div>
                            <div>
                                <span class="@if ($dataNilaiEvaluasi != null) disabled-text @endif text-primary text-decoration-underline" role="button" onclick="checkAllRadio(2)">Ceklis</span>
                            </div>
                        </th>
                        <th scope="col">
                            <div>Sangat Kurang</div>
                            <div>(1)</div>
                            <div>
                                <span class="@if ($dataNilaiEvaluasi != null) disabled-text @endif text-primary text-decoration-underline" role="button" onclick="checkAllRadio(1)">Ceklis</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataKategori as $kategori => $data )
                        <tr wire:key="row-kategori-{{ $data->id }}-pegawai-{{ $id_pegawai }}">
                            <td class="text-start">{{ $kategori+1 }}</td>
                            <td class="align-middle">{!! $data->nama !!}</td>
                            @for ($i = 5; $i >= 1; $i--)
                                <td wire:key="radio-kategori-{{ $data->id }}-nilai-{{ $i }}-pegawai-{{ $id_pegawai }}">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="radio" name="nilai[{{ $data->id }}]" value="{{ $i }}"
                                        {{-- cek apakah nilai null --}}
                                        @if ($dataNilaiEvaluasi != null)
                                            {{-- tidak null cocokkan nilainya dengan value input --}}
                                            @if ($dataNilaiEvaluasi[$data->id] == $i)
                                                checked disabled
                                            @else
                                                disabled
                                            @endif
                                        @else
                                        {{-- jika null ceklis semua value 5 --}}
                                            @if ($i == 5)
                                                checked
                                            @endif
                                        @endif
                                        required
                                        >
                                    </div>
                                </td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">Tidak Ada Kategori</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td colspan="2" class="text-center fw-bold">Total Skor</td>
                        <td colspan="5" class="text-center fw-bold"> {{ collect($dataNilaiEvaluasi)->sum() ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
            <textarea name="catatan" class="form-control" placeholder="Catatan jika ada" rows="5" @if ($dataNilaiEvaluasi != null) disabled @endif>{{ $catatan }}</textarea>
            @can('manajemen_evaluasi.create')
                <div class="row mt-3">
                    <button class="btn btn-main @if ($dataNilaiEvaluasi != null) d-none @endif" type="submit">Simpan</button>
                </div>
            @endcan
        </form>
    @else
        <table class="table table-striped table-hover border table-bordered align-middle">
            <thead>
                <tr class="text-center">
                    <th scope="col">No</th>
                    <th scope="col">Indikator Penilaian</th>
                    <th scope="col">
                        <div>Sangat Baik</div>
                        <div>(5)</div>
                        <div>
                            <span class="text-primary text-decoration-underline" role="button" onclick="checkAllRadio(5)">Ceklis</span>
                        </div>
                    </th>
                    <th scope="col">
                        <div>Baik</div>
                        <div>(4)</div>
                        <div>
                            <span class="text-primary text-decoration-underline" role="button" onclick="checkAllRadio(4)">Ceklis</span>
                        </div>
                    </th>
                    <th scope="col">
                        <div>Sedang</div>
                        <div>(3)</div>
                        <div>
                            <span class="text-primary text-decoration-underline" role="button" onclick="checkAllRadio(3)">Ceklis</span>
                        </div>
                    </th>
                    <th scope="col">
                        <div>Kurang</div>
                        <div>(2)</div>
                        <div>
                            <span class="text-primary text-decoration-underline" role="button" onclick="checkAllRadio(2)">Ceklis</span>
                        </div>
                    </th>
                    <th scope="col">
                        <div>Sangat Kurang</div>
                        <div>(1)</div>
                        <div>
                            <span class="text-primary text-decoration-underline" role="button" onclick="checkAllRadio(1)">Ceklis</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="100%" class="text-center">Tidak Ada Kategori</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center fw-bold">Total Skor</td>
                    <td colspan="5" class="text-center fw-bold">-</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>
