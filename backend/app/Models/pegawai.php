<?php
// app/Models/Pegawai.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id_pegawai';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'id_jabatan',
        'id_departemen',
        'tanggal_masuk',
        'foto',
        'jatahtahunan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_pegawai', 'id_pegawai');
    }
    public function kehadiran()
{
    return $this->hasMany(Absensi::class, 'id_pegawai', 'id_pegawai');
}

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen', 'id_departemen');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    /**
     * Get all cuti for this pegawai.
     */
    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'id_pegawai', 'id_pegawai');
    }

    /**
     * Get approved cuti for this pegawai.
     */
    public function cutiDisetujui()
    {
        return $this->hasMany(Cuti::class, 'id_pegawai', 'id_pegawai')
                    ->where('status_cuti', 'Disetujui');
    }

    /**
     * Get pending cuti for this pegawai.
     */
    public function cutiMenunggu()
    {
        return $this->hasMany(Cuti::class, 'id_pegawai', 'id_pegawai')
                    ->where('status_cuti', 'Menunggu');
    }

    /**
     * Get total cuti days used this year.
     */
    public function getTotalCutiThisYearAttribute()
    {
        return $this->cutiDisetujui()
                    ->whereYear('tanggal_pengajuan', now()->year)
                    ->get()
                    ->sum('jumlah_hari');
    }

    /**
     * Get remaining cuti days for this year.
     */
    public function getSisaCutiAttribute()
    {
        $totalCutiTahunan = 12; // Atau ambil dari setting
        return $totalCutiTahunan - $this->total_cuti_this_year;
    }

    // Method untuk mendapatkan nama golongan
    public function getNamaGolonganAttribute()
    {
        $golonganOptions = self::getGolonganOptions();
        return $golonganOptions[$this->golongan] ?? 'Tidak Diketahui';
    }

    // Alternative: Menggunakan jabatan sebagai dasar golongan
    public function getGolonganByJabatanAttribute()
    {
        if (!$this->jabatan) {
            return 'D'; // Default
        }

        // Logika pengelompokan berdasarkan nama jabatan
        $namaJabatan = strtolower($this->jabatan->nama_jabatan);
        
        if (str_contains($namaJabatan, 'direktur') || str_contains($namaJabatan, 'manager')) {
            return 'A';
        } elseif (str_contains($namaJabatan, 'supervisor') || str_contains($namaJabatan, 'team lead')) {
            return 'B';
        } elseif (str_contains($namaJabatan, 'senior') || str_contains($namaJabatan, 'koordinator')) {
            return 'C';
        } else {
            return 'D';
        }
    }
}