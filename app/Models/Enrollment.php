<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $connection = 'mysql';
    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'course_id',
        'progress',
        'enrolled_at',
    ];

    // Relasi ke course (masih 1 database, bisa pakai relasi normal)
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
