<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    /** @use HasFactory<\Database\Factories\KeluargaFactory> */
    use HasFactory;

    protected $fillable = [
        'id_user',
        'nama',
        'hubungan',
        'tanggal_lahir',
        'pekerjaan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
