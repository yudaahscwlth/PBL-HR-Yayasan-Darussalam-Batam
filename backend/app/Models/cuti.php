<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cuti extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cuti';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_cuti';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pegawai',
        'tanggal_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'id_jenis_cuti',
        'status_cuti',
        'keterangan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'id_pegawai' => 'integer',
        'id_jenis_cuti' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the pegawai that owns the cuti.
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    /**
     * Get the jenis cuti that owns the cuti.
     */
    public function jenisCuti()
    {
        return $this->belongsTo(JenisCuti::class, 'id_jenis_cuti', 'id_jenis_cuti');
    }

    /**
     * Calculate the number of days for this cuti.
     */
    public function getJumlahHariAttribute()
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    /**
     * Check if cuti is approved.
     */
    public function getIsApprovedAttribute()
    {
        return $this->status_cuti === 'Disetujui';
    }

    /**
     * Check if cuti is rejected.
     */
    public function getIsRejectedAttribute()
    {
        return $this->status_cuti === 'Ditolak';
    }

    /**
     * Check if cuti is pending.
     */
    public function getIsPendingAttribute()
    {
        return $this->status_cuti === 'Menunggu';
    }

    /**
     * Scope a query to only include approved cuti.
     */
    public function scopeApproved($query)
    {
        return $query->where('status_cuti', 'Disetujui');
    }

    /**
     * Scope a query to only include rejected cuti.
     */
    public function scopeRejected($query)
    {
        return $query->where('status_cuti', 'Ditolak');
    }

    /**
     * Scope a query to only include pending cuti.
     */
    public function scopePending($query)
    {
        return $query->where('status_cuti', 'Menunggu');
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('tanggal_pengajuan', $year);
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('tanggal_pengajuan', $month);
    }

    /**
     * Scope a query to filter by pegawai.
     */
    public function scopeByPegawai($query, $idPegawai)
    {
        return $query->where('id_pegawai', $idPegawai);
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status_cuti) {
            'Disetujui' => 'success',
            'Ditolak' => 'danger',
            'Menunggu' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get the status badge for UI.
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status_cuti) {
            'Disetujui' => '<span class="badge bg-success">Disetujui</span>',
            'Ditolak' => '<span class="badge bg-danger">Ditolak</span>',
            'Menunggu' => '<span class="badge bg-warning">Menunggu</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }
}