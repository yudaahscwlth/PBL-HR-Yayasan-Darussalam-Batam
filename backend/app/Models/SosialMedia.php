<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosialMedia extends Model
{
    /** @use HasFactory<\Database\Factories\SosialMediaFactory> */
    use HasFactory;

    protected $table = 'sosial_media';

    protected $fillable = [
        'nama_platform',
    ];

    public function userSosialMedia(){
        return $this->hasMany(UserSosialMedia::class,'id_platform');
    }
}
