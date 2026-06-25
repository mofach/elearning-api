<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $connection = 'mysql_users';
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'phone',
    ];

    // Relasi balik: profile ini milik 1 user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
