<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $connection = 'mysql';
    protected $table = 'assignments';

    protected $fillable = [
        'course_id',
        'student_id',
        'title',
        'description',
        'file_path',
        'grade',
        'type',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
