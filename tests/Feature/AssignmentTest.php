<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Services\JwtService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql', 'mysql_users'];

    public function test_student_can_submit_assignment_with_file()
    {
        // Gunakan uniqid() agar email selalu berbeda setiap di-test
        $instructor = User::create(['name' => 'Inst', 'email' => uniqid() . '@x.com', 'password' => '123', 'role' => 'instructor']);
        $student = User::create(['name' => 'Stud', 'email' => uniqid() . '@x.com', 'password' => '123', 'role' => 'student']);

        // ... sisa kode tetap sama
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Course 1', 'status' => 'published']);
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Tugas Akhir',
            'type' => 'assignment',
            'due_date' => now()->addDays(7)
        ]);

        $studentToken = JwtService::generateToken($student);

        // 2. Buat file PDF palsu berukuran 100 Kilobyte
        $fakePdf = UploadedFile::fake()->create('tugas.pdf', 100, 'application/pdf');

        // 3. Tembak endpoint upload
        $response = $this->withHeader('Authorization', 'Bearer ' . $studentToken)
            ->postJson("/api/assignments/{$assignment->id}/submit", [
                'file' => $fakePdf
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Assignment submitted successfully']);
    }
}
