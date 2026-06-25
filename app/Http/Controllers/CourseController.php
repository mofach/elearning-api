<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Memulai query ke academic_db
        $query = Course::query();

        // Fitur Filter: Jika ada query string ?status=published
        // Express analogi: if (req.query.status) { ... }
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Fitur Filter: Pencarian berdasarkan judul
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->query('search') . '%');
        }

        // Response langsung dipaginasi per 10 data
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        // Ambil data user dari middleware
        $user = $request->attributes->get('auth_user');

        // Role Authorization
        if ($user->role !== 'instructor') {
            return response()->json(['message' => 'Forbidden: Only instructors can create courses'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,published'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create([
            'instructor_id' => $user->id, // Ambil ID dari JWT
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'draft'
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    public function enroll(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Forbidden: Only students can enroll'], 403);
        }

        // Cek apakah course ada dan sudah dipublish
        $course = Course::query()->where('id', $id)->where('status', 'published')->first();
        if (!$course) {
            return response()->json(['message' => 'Course not found or not published'], 404);
        }

        // Cek apakah student sudah enroll sebelumnya (mencegah duplikat)
        $existingEnrollment = Enrollment::query()
            ->where('student_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if ($existingEnrollment) {
            return response()->json(['message' => 'You are already enrolled in this course'], 409);
        }

        // Catat enrollment di application layer (academic_db)
        $enrollment = Enrollment::create([
            'student_id' => $user->id,
            'course_id' => $id,
            'progress' => 0
        ]);

        return response()->json([
            'message' => 'Successfully enrolled in the course',
            'enrollment' => $enrollment
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');
        if ($user->role !== 'instructor') return response()->json(['message' => 'Forbidden'], 403);

        $course = Course::query()->find($id);
        if (!$course) return response()->json(['message' => 'Course not found'], 404);

        // Otorisasi: Pastikan instructor hanya mengedit course miliknya sendiri
        if ($course->instructor_id !== $user->id) return response()->json(['message' => 'Unauthorized access to this course'], 403);

        $course->update($request->only(['title', 'description', 'status']));

        return response()->json(['message' => 'Course updated', 'course' => $course]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');
        $course = Course::query()->find($id);

        if (!$course || $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Forbidden or not found'], 403);
        }
        /** @var \App\Models\Course $course */
        Course::query()->where('id', $id)->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function unenroll(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');
        if ($user->role !== 'student') return response()->json(['message' => 'Forbidden'], 403);

        $enrollment = Enrollment::query()->where('student_id', $user->id)->where('course_id', $id)->first();
        if (!$enrollment) return response()->json(['message' => 'Not enrolled in this course'], 404);

        /** @var \App\Models\Enrollment $enrollment */
        Enrollment::query()
            ->where('student_id', $user->id)
            ->where('course_id', $id)
            ->delete();
        return response()->json(['message' => 'Successfully unenrolled']);
    }


    public function updateProgress(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');

        $validator = Validator::make($request->all(), [
            'progress' => 'required|integer|min:0|max:100'
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $enrollment = Enrollment::query()->where('student_id', $user->id)->where('course_id', $id)->first();
        if (!$enrollment) return response()->json(['message' => 'Enrollment not found'], 404);

        $enrollment->update(['progress' => $request->progress]);
        return response()->json(['message' => 'Progress updated', 'progress' => $enrollment->progress]);
    }
}
