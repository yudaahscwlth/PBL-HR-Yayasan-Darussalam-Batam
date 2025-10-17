<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempatKerja extends Model
{
    /** @use HasFactory<\Database\Factories\TempatKerjaFactory> */
    use HasFactory;

    protected $table = 'tempat_kerjas';

    protected $fillable = [
        'nama_tempat',
        'latitude',
        'longitude',
    ];

    public function profilePekerjaan(){
        return $this->hasMany(ProfilePekerjaan::class,'id_tempat_kerja');
    }
}
