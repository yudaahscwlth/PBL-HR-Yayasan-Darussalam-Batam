<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    /** @use HasFactory<\Database\Factories\EvaluasiFactory> */
    use HasFactory;

    protected $table = 'evaluasis';

    // Kolom yang dapat diisi massal
    protected $fillable = [
        'id_user',
        'id_penilai',
        'id_kategori',
        'id_tahun_ajaran',
        'nilai',
        'catatan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'id_penilai');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriEvaluasi::class, 'id_kategori');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }
}
