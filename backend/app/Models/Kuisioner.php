<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuisioner extends Model
{
    use HasFactory;

    protected $table = 'kuisioner';

    protected $fillable = [
        'kategori',
        'pertanyaan',
        'bobot',
        'aktif',
        'departemen_id',  // Tambahan
        'golongan',    
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'aktif' => 'boolean',
    ];


    public function periodeKuisioner()
    {
        return $this->hasMany(PeriodeKuisioner::class, 'kuisioner_id');
    }

    // Relasi many-to-many ke periode penilaian melalui tabel pivot periode_kuisioner
    public function periodePenilaian(): BelongsToMany
    {
        return $this->belongsToMany(PeriodePenilaian::class, 'periode_kuisioner', 'kuisioner_id', 'periode_id')
                    ->withTimestamps();
    }

    // Relasi ke jawaban kuisioner
    public function jawabanKuisioner(): HasMany
    {
        return $this->hasMany(JawabanKuisioner::class, 'kuisioner_id');
    }

 public function scopeAktif($query)
{
    return $query->where('aktif', true);
}

public function scopeNonAktif($query)
{
    return $query->where('aktif', false);
}
    // Scope untuk kategori tertentu
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    // Scope untuk mencari pertanyaan
    public function scopeCariPertanyaan($query, $keyword)
    {
        return $query->where('pertanyaan', 'like', '%' . $keyword . '%');
    }


    // Accessor untuk status badge
    public function getStatusBadgeAttribute()
    {
        return $this->aktif ? 'success' : 'secondary';
    }

    // Accessor untuk status text
    public function getStatusTextAttribute()
    {
        return $this->aktif ? 'Aktif' : 'Non-Aktif';
    }

    // Accessor untuk preview pertanyaan (potong jika terlalu panjang)
    public function getPertanyaanPreviewAttribute()
    {
        return strlen($this->pertanyaan) > 100 
            ? substr($this->pertanyaan, 0, 100) . '...' 
            : $this->pertanyaan;
    }

    // Method untuk mendapatkan rata-rata skor
    public function getRataRataSkor($periodeId = null)
    {
        $query = $this->jawabanKuisioner();

        if ($periodeId) {
            $query->whereHas('penilaian', function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId);
            });
        }

        return $query->avg('skor') ?? 0;
    }

    // Method untuk mendapatkan jumlah jawaban
    public function getJumlahJawaban($periodeId = null)
    {
        $query = $this->jawabanKuisioner();

        if ($periodeId) {
            $query->whereHas('penilaian', function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId);
            });
        }

        return $query->count();
    }

    // Method untuk mendapatkan distribusi skor
    public function getDistribusiSkor($periodeId = null)
    {
        $query = $this->jawabanKuisioner();

        if ($periodeId) {
            $query->whereHas('penilaian', function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId);
            });
        }

        return $query->groupBy('skor')
                    ->selectRaw('skor, COUNT(*) as jumlah')
                    ->orderBy('skor')
                    ->pluck('jumlah', 'skor')
                    ->toArray();
    }

    // Method untuk mendapatkan semua kategori unik
    public static function getAllKategori()
    {
        return self::distinct('kategori')
                   ->orderBy('kategori')
                   ->pluck('kategori')
                   ->toArray();
    }

    // Mutator untuk kategori (capitalize)
    public function setKategoriAttribute($value)
    {
        $this->attributes['kategori'] = ucwords(strtolower($value));
    }

    // Method untuk toggle status aktif
    public function toggleAktif()
    {
        $this->aktif = !$this->aktif;
        $this->save();

        return $this;
    }
}