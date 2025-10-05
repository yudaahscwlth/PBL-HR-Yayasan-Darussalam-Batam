<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'periode_id',
        'dinilai_pegawai_id',  // Ganti dari dinilai_id_user
        'penilai_pegawai_id',  // Ganti dari penilai_id_user
        'status',
        'total_nilai',
        'tanggal_penilaian'
    ];

    protected $dates = [
        'tanggal_penilaian'
    ];

    // Relasi ke periode
    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    // Relasi ke pegawai yang dinilai
    public function pegawaiDinilai()
    {
        return $this->belongsTo(Pegawai::class, 'dinilai_pegawai_id');
    }

    // Relasi ke pegawai penilai
    public function penilaiPegawai()
    {   
        return $this->belongsTo(Pegawai::class, 'penilai_pegawai_id');
    }

    // Relasi ke jawaban kuisioner
    public function jawabanKuisioner()
    {
        return $this->hasMany(JawabanKuisioner::class, 'penilaian_id');
    }
}