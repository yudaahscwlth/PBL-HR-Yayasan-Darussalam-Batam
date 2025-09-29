<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'semester',
        'is_aktif'
    ];

    public function Evaluasi()
    {
        return $this->hasMany(Evaluasi::class, 'id_tahun_ajaran');
    }
}
