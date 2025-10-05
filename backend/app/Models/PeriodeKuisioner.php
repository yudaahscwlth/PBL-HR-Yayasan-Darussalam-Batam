<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeKuisioner extends Model
{
    use HasFactory;

    protected $table = 'periode_kuisioner';

    protected $fillable = [
        'periode_id',
        'kuisioner_id',
    ];

    /**
     * Relasi ke model PeriodePenilaian
     */
    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    /**
     * Relasi ke model Kuisioner
     */
    public function kuisioner()
    {
        return $this->belongsTo(Kuisioner::class, 'kuisioner_id');
    }
}
