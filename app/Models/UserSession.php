<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $connection = 'mysql_users';
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    // Cast otomatis expires_at jadi object datetime
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
