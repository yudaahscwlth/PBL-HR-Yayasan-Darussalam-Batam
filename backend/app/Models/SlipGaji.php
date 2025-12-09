<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlipGaji extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'slip_gaji';

    protected $fillable = [
        'id_user',
        'tanggal',
        'total_gaji',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_gaji' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
