<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $table = 'otp';

    // Nonaktifkan timestamps karena tabel tidak memiliki created_at dan updated_at
    public $timestamps = false;

    protected $fillable = [
        'email',
        'kode_otp',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid
     */
    public function isValid(string $kodeOtp): bool
    {
        return !$this->isExpired() && $this->kode_otp === $kodeOtp;
    }
}
