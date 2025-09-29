<?php

namespace App\Livewire;

use App\Models\Evaluasi;
use App\Models\KategoriEvaluasi;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RekapEvaluasiPribadi extends Component
{
    public $listTahunAjaran = []; // Simpan nama tahun ajaran + id
    public $selectedTahunAjaran = null;
    public $dataEvaluasi;

    public function mount(){
        $this->getTahunAjaran();

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
        $user = Auth::user();

        // Ambil data evaluasi user
        $evaluasi = Evaluasi::where('id_user', $user->id)
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
        $user = Auth::user();

        // Ambil data evaluasi user
        $evaluasi = Evaluasi::where('id_user', $user->id)
            ->where('id_tahun_ajaran',$this->selectedTahunAjaran)
            ->with(['tahunAjaran', 'kategori']) // eager load relasi lain jika perlu
            ->get();

        $this->dataEvaluasi = $evaluasi;
    }


    public function render()
    {
        return view('livewire.rekap-evaluasi-pribadi');
    }
}
