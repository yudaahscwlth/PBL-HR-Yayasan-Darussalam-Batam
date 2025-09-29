<div>
    <div class="d-flex flex-column">
        <div class="d-flex align-items-center mb-3">
            <div class="me-2">
                <label for="staticEmail" class="form-label text-capitalize">Nama Penilai : {{ optional($dataEvaluasi?->first()?->penilai?->profilePribadi)->nama_lengkap ?? '-' }}</label>
            </div>
        </div>
        <div class="d-flex align-items-center mb-3">
            <div class="me-2">
                <label for="staticEmail" class="form-label">Tahun Ajaran :</label>
            </div>
            <div>
                <select class="form-select form-select-sm" wire:model.change="selectedTahunAjaran">
                    <option value="" disabled selected>Pilih Tahun Ajaran</option>
                    @forelse ($listTahunAjaran as $tahun)
                        <option value="{{ $tahun->id }}" wire:key="tahun-ajaran-{{ $tahun->id }}">{{ $tahun->nama }} - {{ ucfirst($tahun->semester) }}</option>
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
                    <span class="badge badge-sm @if ($dataEvaluasi != null) text-bg-success @else text-bg-danger @endif">
                        @if ($dataEvaluasi != null) Sudah Terisi @else Belum Terisi @endif
                    </span>
            </label>
            </div>
        </div>
    </div>
    <table class="table table-striped table-hover border table-bordered align-middle">
        <thead>
            <tr class="text-center">
                <th scope="col">No</th>
                <th scope="col">Indikator Penilaian</th>
                <th scope="col">
                    <div>Sangat Baik</div>
                    <div>(5)</div>
                    <div>
                        <span class="disabled-text text-primary text-decoration-underline" role="button" onclick="checkAllRadio(5)">Ceklis</span>
                    </div>
                </th>
                <th scope="col">
                    <div>Baik</div>
                    <div>(4)</div>
                    <div>
                        <span class="disabled-text text-primary text-decoration-underline" role="button" onclick="checkAllRadio(4)">Ceklis</span>
                    </div>
                </th>
                <th scope="col">
                    <div>Sedang</div>
                    <div>(3)</div>
                    <div>
                        <span class="disabled-text text-primary text-decoration-underline" role="button" onclick="checkAllRadio(3)">Ceklis</span>
                    </div>
                </th>
                <th scope="col">
                    <div>Kurang</div>
                    <div>(2)</div>
                    <div>
                        <span class="disabled-text text-primary text-decoration-underline" role="button" onclick="checkAllRadio(2)">Ceklis</span>
                    </div>
                </th>
                <th scope="col">
                    <div>Sangat Kurang</div>
                    <div>(1)</div>
                    <div>
                        <span class="disabled-text text-primary text-decoration-underline" role="button" onclick="checkAllRadio(1)">Ceklis</span>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @if ($dataEvaluasi != null)
                @forelse ($dataEvaluasi as $evaluasi => $data )
                    <tr wire:key="evaluasi-row-{{ $data->id }}">
                        <td class="text-start">{{ $evaluasi+1 }}</td>
                        <td class="align-middle">{!! $data->kategori->nama !!}</td>
                        @for ($i = 5; $i >= 1; $i--)
                            <td wire:key="radio-{{ $data->id }}-{{ $i }}">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="nilai[{{ $data->id_kategori }}]" value="{{ $i }}"
                                    @if ($data->nilai == $i)
                                    checked
                                    @endif
                                    disabled
                                    >
                                </div>
                            </td>
                        @endfor
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center">Tidak Ada Kategori</td>
                    </tr>
                @endforelse
            @else
                <tr>
                    <td colspan="100%" class="text-center">Tidak Ada Kategori</td>
                </tr>
            @endif
            <tr>
                <td colspan="2" class="text-center fw-bold">Total Skor</td>
                <td colspan="5" class="text-center fw-bold"> {{ $dataEvaluasi?->sum('nilai') ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
    <textarea name="catatan" class="form-control" placeholder="Catatan jika ada" rows="5" disabled>{{  $dataEvaluasi?->first()?->catatan ?? '-' }}</textarea>
</div>
