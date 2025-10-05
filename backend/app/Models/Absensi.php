<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'kehadiran'; // nama tabel
    protected $primaryKey = 'id_kehadiran'; // primary key yang benar
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_pegawai',
        'tanggal',
        'waktu_masuk',
        'waktu_pulang',
        'status_kehadiran',
        'total_jam_kerja',
        'durasi_kerja',
        'lokasi_kantor_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_masuk' => 'datetime',
        'waktu_pulang' => 'datetime',
        'total_jam_kerja' => 'decimal:2'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function lokasiKantor()
    {
        return $this->belongsTo(LokasiKantor::class, 'lokasi_kantor_id', 'id');
    }
}