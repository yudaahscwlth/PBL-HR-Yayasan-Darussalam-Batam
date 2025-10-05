<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanKuisioner extends Model
{
    use HasFactory;

    protected $table = 'jawaban_kuisioner';

    protected $fillable = [
        'penilaian_id',
        'kuisioner_id',
        'skor', 
        'komentar',
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    // Relasi ke penilaian
    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id');
    }

    // Relasi ke kuisioner
    public function kuisioner(): BelongsTo
    {
        return $this->belongsTo(Kuisioner::class, 'kuisioner_id');
    }

    // Scope untuk skor tertentu
    public function scopeSkor($query, $skor)
    {
        return $query->where('skor', $skor);
    }

    // Scope untuk skor minimal
    public function scopeSkorMinimal($query, $skor)
    {
        return $query->where('skor', '>=', $skor);
    }

    // Scope untuk skor maksimal
    public function scopeSkorMaksimal($query, $skor)
    {
        return $query->where('skor', '<=', $skor);
    }

    // Scope untuk range skor
    public function scopeSkorRange($query, $min, $max)
    {
        return $query->whereBetween('skor', [$min, $max]);
    }

    // Scope untuk jawaban yang ada komentar
    public function scopeAdaKomentar($query)
    {
        return $query->whereNotNull('komentar')
                    ->where('komentar', '!=', '');
    }

    // Scope untuk jawaban tanpa komentar
    public function scopeTanpaKomentar($query)
    {
        return $query->whereNull('komentar')
                    ->orWhere('komentar', '');
    }

    // Scope untuk penilaian tertentu
    public function scopePenilaian($query, $penilaianId)
    {
        return $query->where('penilaian_id', $penilaianId);
    }

    // Scope untuk kuisioner tertentu
    public function scopeKuisioner($query, $kuisionerId)
    {
        return $query->where('kuisioner_id', $kuisionerId);
    }

    // Accessor untuk skor badge color
    public function getSkorBadgeAttribute()
    {
        $colors = [
            1 => 'danger',
            2 => 'warning',
            3 => 'info',
            4 => 'primary',
            5 => 'success',
        ];

        return $colors[$this->skor] ?? 'secondary';
    }

    // Accessor untuk skor text
    public function getSkorTextAttribute()
    {
        $texts = [
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
        ];

        return $texts[$this->skor] ?? 'Tidak Diketahui';
    }

    // Accessor untuk komentar preview
    public function getKomentarPreviewAttribute()
    {
        if (empty($this->komentar)) {
            return '-';
        }

        return strlen($this->komentar) > 50 
            ? substr($this->komentar, 0, 50) . '...' 
            : $this->komentar;
    }

    // Method untuk mendapatkan nilai tertimbang (skor * bobot kuisioner)
    public function getNilaiTertimbang()
    {
        return $this->skor * $this->kuisioner->bobot;
    }

    // Method untuk validasi skor
    public function isValidSkor()
    {
        return $this->skor >= 1 && $this->skor <= 5;
    }

    // Boot method untuk validasi otomatis
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validasi skor harus antara 1-5
            if ($model->skor < 1 || $model->skor > 5) {
                throw new \InvalidArgumentException('Skor harus antara 1-5');
            }
        });
    }

    // Mutator untuk skor (pastikan dalam range 1-5)
    public function setSkorAttribute($value)
    {
        $this->attributes['skor'] = max(1, min(5, (int) $value));
    }

    // Method untuk update jawaban
    public function updateJawaban($skor, $komentar = null)
    {
        $this->skor = $skor;
        $this->komentar = $komentar;
        $this->save();

        // Update total nilai di penilaian
        $this->penilaian->updateTotalNilai();

        return $this;
    }
}