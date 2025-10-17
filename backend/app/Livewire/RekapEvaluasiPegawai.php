<?php

namespace App\Livewire;

use App\Models\Evaluasi;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RekapEvaluasiPegawai extends Component
{
    public $listTahunAjaran = []; // Simpan nama tahun ajaran + id
    public $selectedTahunAjaran = null;
    public $dataEvaluasi;
    public $id_pegawai;

    public function mount($id_pegawai){
        $this->getTahunAjaran();

        $this->id_pegawai = $id_pegawai;

        // Auto-set tahun ajaran terbaru jika ada
        if (!empty($this->listTahunAjaran)) {
            $this->selectedTahunAjaran = $this->listTahunAjaran[0]->id;
            $this->getEvaluasi(); // langsung ambil evaluasi tahun terbaru
        }
    }

    public function updated(){
        $this->getEvaluasi();
    }

    public function getTahunAjaran()
    {
        // Ambil data evaluasi user
        $evaluasi = Evaluasi::where('id_user', $this->id_pegawai)
            ->with('tahunAjaran') // eager load relasi
            ->get();

        // Ambil tahun ajaran unik
        $this->listTahunAjaran = $evaluasi
        ->pluck('tahunAjaran') // ini object TahunAjaran
        ->unique('id')
        ->sortByDesc('nama') // sort jika perlu
        ->values()
        ->all(); // untuk menghindari collection binding
    }

    public function getEvaluasi()
    {
        // Ambil data evaluasi user
        $evaluasi = Evaluasi::where('id_user', $this->id_pegawai)
            ->where('id_tahun_ajaran',$this->selectedTahunAjaran)
            ->with(['tahunAjaran', 'kategori']) // eager load relasi lain jika perlu
            ->get();

        $this->dataEvaluasi = $evaluasi;
    }

    public function render()
    {
        return view('livewire.rekap-evaluasi-pegawai');
    }
}
