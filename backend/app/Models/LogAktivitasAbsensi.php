<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitasAbsensi extends Model
{
    /** @use HasFactory<\Database\Factories\SosialMediaFactory> */
    use HasFactory;

    protected $table = 'log_aktivitas_absensis';

    protected $fillable = [
        'id_absensi',
        'id_user',
        'aksi',
        'data_lama',
        'data_baru',
    ];

    public function absensi(){
        return $this->belongsTo(Absensi::class,'id_absensi');
    }

    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
