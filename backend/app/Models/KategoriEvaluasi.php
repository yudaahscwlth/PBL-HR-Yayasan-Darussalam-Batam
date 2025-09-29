<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriEvaluasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama'
    ];

    public function Evaluasi()
    {
        return $this->hasMany(Evaluasi::class, 'id_kategori');
    }
}
