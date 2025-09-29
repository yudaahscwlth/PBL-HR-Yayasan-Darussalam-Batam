<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    /** @use HasFactory<\Database\Factories\PengajuanCutiFactory> */
    use HasFactory;

    protected $table = 'pengajuan_cutis';

    protected $fillable = [
        'id_user',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe_cuti',
        'status_pengajuan',
        'alasan_pendukung',
        'file_pendukung',
        'komentar',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
