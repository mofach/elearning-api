<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Beritahu Laravel: model ini pakai koneksi mysql_users
    protected $connection = 'mysql_users';
    protected $table = 'users';

    // Kolom yang boleh diisi massal (seperti req.body di Express)
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    // Kolom yang disembunyikan saat response JSON
    protected $hidden = ['password'];

    // Relasi: 1 user punya 1 profile
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}
