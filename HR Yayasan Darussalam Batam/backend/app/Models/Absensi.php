<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Absensi extends Model
{
    /** @use HasFactory<\Database\Factories\AbsensiFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'absensis';

    protected $fillable = [
        'id_user',
        'tanggal',
        'check_in',
        'check_out',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'status',
        'keterangan',
        'file_pendukung',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function logAktivitasAbsensi()
    {
        return $this->hasMany(LogAktivitasAbsensi::class,'id_absensi');
    }

    protected static $isRestoring = false; // Flag restore
    protected static function booted()
    {
        static::restoring(function ($absensi) {
            // Flag restore mulai
            self::$isRestoring = true;
        });

        static::restored(function ($absensi) {
            LogAktivitasAbsensi::create([
                'id_absensi' => $absensi->id,
                'id_user' => Auth::id(),
                'aksi' => 'restored',
                'data_baru' => $absensi->toJson(),
            ]);

            // Selesai restore
            self::$isRestoring = false;
        });

        static::updated(function ($absensi) {
            // Cegah log update jika ini bagian dari proses restore
            if (self::$isRestoring) {
                return;
            }

            LogAktivitasAbsensi::create([
                'id_absensi' => $absensi->id,
                'id_user' => Auth::id(),
                'aksi' => 'updated',
                'data_lama' => json_encode($absensi->getOriginal()),
                'data_baru' => $absensi->toJson(),
            ]);
        });

        static::created(function ($absensi) {
            LogAktivitasAbsensi::create([
                'id_absensi' => $absensi->id,
                'id_user' => Auth::id() ?? $absensi->id_user, // Fallback ke user yang memiliki absensi
                'aksi' => 'created',
                'data_baru' => $absensi->toJson(),
            ]);
        });

        static::deleted(function ($absensi) {
            LogAktivitasAbsensi::create([
                'id_absensi' => $absensi->id,
                'id_user' => Auth::id(),
                'aksi' => 'deleted',
                'data_lama' => $absensi->toJson(),
            ]);
        });
    }
}
