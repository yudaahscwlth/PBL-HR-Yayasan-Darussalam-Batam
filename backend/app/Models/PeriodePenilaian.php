<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PeriodePenilaian extends Model
{
    use HasFactory;

    protected $table = 'periode_penilaian';

    protected $fillable = [
        'nama_periode',
        'tahun',
        'semester',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',  
    ];

    protected $dates = [
        'tanggal_mulai',
        'tanggal_selesai',
        'created_at',
        'updated_at'
    ];

    // Cast attributes to proper types
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tahun' => 'integer',
        'semester' => 'integer'
    ];

    /**
     * Relasi ke User yang membuat periode (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User yang mengupdate periode
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi many-to-many ke Kuisioner
     */
    public function kuisioner()
    {
        return $this->belongsToMany(Kuisioner::class, 'periode_kuisioner', 'periode_id', 'kuisioner_id')
                    ->withTimestamps();
    }

    /**
     * Scope untuk periode aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk periode berdasarkan tahun
     */
    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope untuk periode berdasarkan semester
     */
    public function scopeSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Accessor untuk status badge
     */
    public function getStatusBadgeAttribute()
    {
        switch($this->status) {
            case 'aktif':
                return 'success';
            case 'belum_dibuka':
                return 'secondary';
            case 'selesai':
                return 'primary';
            default:
                return 'secondary';
        }
    }

    /**
     * Accessor untuk cek apakah periode expired
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->tanggal_selesai) {
            return false;
        }
        
        return Carbon::now()->gt($this->tanggal_selesai);
    }

    /**
     * Accessor untuk nama periode lengkap
     */
    public function getNamaPeriodeLengkapAttribute()
    {
        return $this->nama_periode . ' - ' . $this->tahun . ' Semester ' . $this->semester;
    }

    /**
     * Method untuk format tanggal
     */
    public function getTanggalMulaiFormattedAttribute()
    {
        return $this->tanggal_mulai ? $this->tanggal_mulai->format('d/m/Y') : '-';
    }

    public function getTanggalSelesaiFormattedAttribute()
    {
        return $this->tanggal_selesai ? $this->tanggal_selesai->format('d/m/Y') : '-';
    }
}