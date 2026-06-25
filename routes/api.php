<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\RoleMiddleware;

//Public
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// JWT Requirements
Route::middleware([JwtMiddleware::class])->group(function () {
    //All Roles
    Route::post('/auth/update-password', [AuthController::class, 'updatePassword']); // Auth Management
    Route::get('/courses', [CourseController::class, 'index']); // Course Management

    //RBAC Instructor
    Route::middleware([RoleMiddleware::class . ':instructor'])->group(function () {
        // Course Management
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

        // Assignment System
        Route::post('/assignments', [AssignmentController::class, 'store']); // Create soal
        Route::put('/assignments/submissions/{id}/grade', [AssignmentController::class, 'grade']); // Beri nilai
        Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'history']);
    });

    //RBAC Student
    Route::middleware([RoleMiddleware::class . ':student'])->group(function () {
        // Student Enrollment & Progress
        Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll']);
        Route::delete('/courses/{id}/unenroll', [CourseController::class, 'unenroll']);
        Route::put('/courses/{id}/progress', [CourseController::class, 'updateProgress']);
        // Assignment System
        Route::get('/assignments/my-submissions', [AssignmentController::class, 'mySubmissions']); // Lihat tugas sendiri
        Route::post('/assignments/{id}/submit', [AssignmentController::class, 'submit'])
            ->middleware('throttle:3,1'); // File Upload (Dilengkapi Rate Limiting 3x / menit)
    });
});
