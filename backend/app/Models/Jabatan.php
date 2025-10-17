<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    /** @use HasFactory<\Database\Factories\JabatanFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_jabatan',
    ];

    public function profilePekerjaan(){
        return $this->hasMany(ProfilePekerjaan::class,'id_jabatan');
    }

    public function jamKerja(){
        return $this->hasMany(JamKerja::class,'id_jabatan');
    }
}
