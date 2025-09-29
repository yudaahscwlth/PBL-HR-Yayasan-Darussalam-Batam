<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilePekerjaan extends Model
{
    /** @use HasFactory<\Database\Factories\ProfilePekerjaanFactory> */
    use HasFactory;

    protected $table = 'profile_pekerjaans';

    protected $fillable = [
        'id_user',
        'id_departemen',
        'id_tempat_kerja',
        'id_jabatan',
        'nomor_induk_karyawan',
        'tanggal_masuk',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    public function tempatKerja()
    {
        return $this->belongsTo(TempatKerja::class, 'id_tempat_kerja');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }
}
