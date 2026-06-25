<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('student_id')->nullable(); // null = soal dari instructor
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // untuk submit file
            $table->integer('grade')->nullable();
            $table->enum('type', ['assignment', 'submission'])->default('assignment');
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('assignments');
    }
};
