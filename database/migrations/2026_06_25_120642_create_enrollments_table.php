<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id'); // reference ke users_db
            $table->unsignedBigInteger('course_id');
            $table->integer('progress')->default(0); // 0-100
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            $table->unique(['student_id', 'course_id']); // 1 siswa tidak bisa enroll 2x
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('enrollments');
    }
};
