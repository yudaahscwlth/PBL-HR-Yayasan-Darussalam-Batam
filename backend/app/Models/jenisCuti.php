<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisCuti extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jenis_cuti';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_jenis_cuti';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_jenis_cuti',
        'deskripsi',
        'max_hari_cuti',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'max_hari_cuti' => 'integer',
    ];

    /**
     * Get all cuti for this jenis cuti.
     */
    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'id_jenis_cuti', 'id_jenis_cuti');
    }

    /**
     * Scope a query to only include active jenis cuti.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the jenis cuti's name attribute.
     */
    public function getNamaAttribute()
    {
        return $this->nama_jenis_cuti;
    }
}