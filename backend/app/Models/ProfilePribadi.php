<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilePribadi extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    protected $table = 'profile_pribadi';

    protected $fillable = [
        'id_user',
        'nomor_induk_kependudukan',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'status_pernikahan',
        'npwp',
        'kecamatan',
        'alamat_lengkap',
        'no_hp',
        'nomor_rekening',
        'foto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
