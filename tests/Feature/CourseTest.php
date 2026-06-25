<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use DatabaseTransactions;

    // Beri tahu Laravel untuk merollback kedua database setelah test selesai
    protected $connectionsToTransact = ['mysql', 'mysql_users'];

    private function getAuthToken($role)
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => uniqid() . '@test.com', // GANTI time() MENJADI uniqid()
            'password' => bcrypt('password123'),
            'role' => $role
        ]);
        return JwtService::generateToken($user);
    }

    public function test_student_cannot_create_course()
    {
        $token = $this->getAuthToken('student'); // Otorisasi Student

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/courses', [
                'title' => 'Hacking 101',
                'status' => 'published'
            ]);

        // Ekspektasi ditolak oleh RoleMiddleware
        $response->assertStatus(403);
    }

    public function test_instructor_can_create_course()
    {
        $token = $this->getAuthToken('instructor'); // Otorisasi Instructor

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/courses', [
                'title' => 'Advanced Networking',
                'description' => 'Materi CNS',
                'status' => 'published'
            ]);

        $response->assertStatus(201);
    }
}
