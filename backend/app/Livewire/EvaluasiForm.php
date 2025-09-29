<?php

namespace App\Livewire;

use App\Models\Evaluasi;
use App\Models\KategoriEvaluasi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EvaluasiForm extends Component
{
    public $id_pegawai ='';
    public $id_tahun_ajaran ='';
    public $dataNilaiEvaluasi;
    public $catatan = '';
    public $dataPegawai;
    public $dataTahunAjaran;
    public $dataKategori;

    public function mount(){
        $this->getDataPegawai();
        $this->getDataTahunAjaran();
        $this->getDataKategori();
    }

    public function updated($property)
    {
        if (in_array($property, ['id_pegawai', 'id_tahun_ajaran'])) {
            $this->loadEvaluasi();
        }
    }

    public function loadEvaluasi()
    {
        if ($this->id_pegawai && $this->id_tahun_ajaran) {
            $existing = Evaluasi::where('id_user', $this->id_pegawai)
                ->where('id_tahun_ajaran', $this->id_tahun_ajaran)
                ->get();

            $this->dataNilaiEvaluasi = $existing->pluck('nilai', 'id_kategori')->toArray();

            $this->catatan = $existing->first()->catatan ?? '';
        }
    }

    public function getDataPegawai()
    {
        $user = Auth::user();

        if ($user->hasRole('kepala sekolah')) {
            // Hanya tenaga pendidik di tempat kerja yang sama
            $pegawai = User::whereHas('roles', function ($query) {
                    $query->where('name', 'tenaga pendidik');
                })
                ->whereHas('profilePekerjaan', function ($query) use ($user) {
                    $query->where('id_tempat_kerja', $user->profilePekerjaan->id_tempat_kerja);
                })
                ->get();

        } elseif ($user->hasRole('kepala departemen')) {
            // tampilkan user departemen yg sama kecuali role superadmin dan kepala yayasan
            $pegawai = User::whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', ['superadmin', 'kepala yayasan']);
                })
                ->whereHas('profilePekerjaan', function ($query) use ($user) {
                    $query->where('id_departemen', $user->profilePekerjaan->id_departemen);
                })
                ->get();

        } elseif (!$user->hasRole('superadmin')) {
            // Semua kecuali superadmin dan kepala yayasan
            $pegawai = User::whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', ['superadmin', 'kepala yayasan']);
                })
                ->get();
        } else {
            // Superadmin bisa melihat semua
            $pegawai = User::all();
        }

        $this->dataPegawai = $pegawai;
    }

    public function getDataTahunAjaran()
    {
        $tahunAjaran = TahunAjaran::where('is_aktif', true)->get();
        $this->dataTahunAjaran = $tahunAjaran;
    }

    public function getDataKategori()
    {
        $kategori = KategoriEvaluasi::orderBy('nama', 'asc')->get();
        $this->dataKategori = $kategori;
    }

    public function render()
    {
        return view('livewire.evaluasi-form');
    }
}
