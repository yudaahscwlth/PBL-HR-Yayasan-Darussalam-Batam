<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSosialMedia extends Model
{
    /** @use HasFactory<\Database\Factories\UserSosialMediaFactory> */
    use HasFactory;

    protected $table = 'user_sosial_media';

    protected $fillable = [
        'id_user',
        'id_platform',
        'username',
        'link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function sosialMedia()
    {
        return $this->belongsTo(SosialMedia::class, 'id_platform');
    }
}
