<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    // Beri tahu Laravel untuk merollback kedua database
    protected $connectionsToTransact = ['mysql', 'mysql_users'];

    public function test_user_can_register_as_student()
    {
        // Buat email unik
        $email = uniqid() . '@test.com';

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test Student',
            'email' => $email,
            'password' => 'password123',
            'role' => 'student'
        ]);

        // Ekspektasi HTTP Status 201 Created
        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user']);
    }

    public function test_user_can_login_and_receive_token()
    {
        // Buat email unik
        $email = uniqid() . '@test.com';

        // 1. Buat user dummy langsung ke database pengguna
        $user = User::create([
            'name' => 'Login Tester',
            'email' => $email,
            'password' => bcrypt('password123'),
            'role' => 'instructor'
        ]);

        // 2. Tembak endpoint login dengan email unik tersebut
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password123'
        ]);

        // 3. Ekspektasi token JWT keluar
        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'token', 'user']);
    }
}
