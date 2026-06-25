<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $connection = 'mysql';
    protected $table = 'courses';

    protected $fillable = [
        'instructor_id',
        'title',
        'description',
        'status',
    ];

    // 1 course punya banyak enrollment
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id');
    }

    // 1 course punya banyak assignment
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'course_id');
    }
}
