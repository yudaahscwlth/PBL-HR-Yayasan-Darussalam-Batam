<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function kepalaDept(){
        return $this->hasMany(Departemen::class, 'id_kepala_departemen');
    }

    public function profilePribadi(){
        return $this->hasOne(ProfilePribadi::class, 'id_user');
    }

    public function profilePekerjaan(){
        return $this->hasOne(ProfilePekerjaan::class, 'id_user');
    }

    public function orangTua(){
        return $this->hasOne(OrangTua::class, 'id_user');
    }

    public function keluarga(){
        return $this->hasMany(Keluarga::class, 'id_user');
    }

    public function userSosialMedia(){
        return $this->hasMany(UserSosialMedia::class, 'id_user');
    }

    public function absensi(){
        return $this->hasMany(Absensi::class, 'id_user');
    }

    public function pengajuanCuti(){
        return $this->hasMany(PengajuanCuti::class, 'id_user');
    }

    public function logAktivitasAbsensi(){
        return $this->hasMany(LogAktivitasAbsensi::class, 'id_user');
    }

    public function evaluasi(){
        return $this->hasMany(Evaluasi::class, 'id_user');
    }

    public function slipGaji(){
        return $this->hasMany(SlipGaji::class, 'id_user');
    }

}
