<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    /** @use HasFactory<\Database\Factories\DepartemenFactory> */
    use HasFactory;

    protected $fillable = [
        'id_kepala_departemen',
        'nama_departemen',
    ];

    public function profilePekerjaan(){
        return $this->hasMany(ProfilePekerjaan::class,'id_departemen');
    }

    public function kepala()
    {
        return $this->belongsTo(User::class, 'id_kepala_departemen');
    }
}
