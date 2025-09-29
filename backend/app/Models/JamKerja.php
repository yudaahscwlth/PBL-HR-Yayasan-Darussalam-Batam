<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    /** @use HasFactory<\Database\Factories\JamKerjaFactory> */
    use HasFactory;
    protected $table = 'jam_kerjas';

    protected $fillable = [
        'id_jabatan',
        'hari',
        'jam_masuk',
        'jam_pulang',
        'is_libur',
        'keterangan',
    ];


    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }
}
