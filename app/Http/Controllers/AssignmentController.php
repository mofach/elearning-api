<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    /**
     * POST /api/assignments
     * Instructor membuat tugas/soal
     */
    public function store(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        if ($user->role !== 'instructor') {
            return response()->json(['message' => 'Forbidden: Only instructors can create assignments'], 403);
        }

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek apakah course ada (Validasi manual karena beda database)
        $course = Course::query()->find($request->course_id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $assignment = Assignment::query()->create([
            'course_id' => $request->course_id,
            'student_id' => null, // Null berarti ini adalah 'Soal' dari instruktur
            'title' => $request->title,
            'description' => $request->description,
            'type' => 'assignment',
            'due_date' => $request->due_date
        ]);

        return response()->json([
            'message' => 'Assignment created successfully',
            'assignment' => $assignment
        ], 201);
    }

    /**
     * GET /api/assignments/my-submissions
     * Student melihat tugas yang sudah mereka kumpulkan
     */
    public function mySubmissions(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Cari semua data dengan tipe 'submission' milik user ini
        $submissions = Assignment::query()
            ->where('student_id', $user->id)
            ->where('type', 'submission')
            ->get();

        return response()->json($submissions);
    }

    /**
     * POST /api/assignments/{id}/submit
     * Student mengumpulkan tugas (Upload File)
     */
    public function submit(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validasi file (Pengganti Multer)
        // mimes: batas ekstensi file, max: maksimal ukuran dalam Kilobyte (2048 KB = 2 MB)
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,zip,doc,docx|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cari data 'soal' aslinya
        $parentAssignment = Assignment::query()
            ->where('id', $id)
            ->where('type', 'assignment')
            ->first();

        if (!$parentAssignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // --- INI LOGIKA UPLOAD FILENYA ---
        // Jika di Express: req.file.path
        // Di Laravel: $request->file('nama_field')->store('nama_folder');
        // File otomatis disimpan di folder: storage/app/private/submissions
        $filePath = $request->file('file')->store('submissions');

        // Simpan data jawaban ke database
        $submission = Assignment::query()->create([
            'course_id' => $parentAssignment->course_id,
            'student_id' => $user->id,
            'title' => 'Submission for: ' . $parentAssignment->title,
            'file_path' => $filePath,
            'type' => 'submission'
        ]);

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'submission' => $submission
        ], 201);
    }

    /**
     * PUT /api/assignments/submissions/{id}/grade
     * Instructor memberikan nilai pada submission student
     */
    public function grade(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');
        if ($user->role !== 'instructor') return response()->json(['message' => 'Forbidden'], 403);

        $validator = Validator::make($request->all(), [
            'grade' => 'required|integer|min:0|max:100'
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Cari data submission berdasarkan ID
        $submission = Assignment::query()->where('id', $id)->where('type', 'submission')->first();
        if (!$submission) return response()->json(['message' => 'Submission not found'], 404);

        // Otorisasi ekstra: Pastikan instruktur ini adalah pemilik course dari tugas tersebut
        $course = Course::query()->find($submission->course_id);
        if (!$course || $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to grade this submission'], 403);
        }

        $submission->update(['grade' => $request->grade]);

        return response()->json([
            'message' => 'Grade successfully assigned',
            'submission' => $submission
        ]);
    }
    /**
     * GET /api/assignments/{id}/submissions
     * Instructor melihat history/daftar siapa saja yang sudah mengumpulkan tugas ini
     */
    public function history(Request $request, string $id)
    {
        $user = $request->attributes->get('auth_user');

        // Cari soal tugasnya
        $assignment = Assignment::query()->where('id', $id)->where('type', 'assignment')->first();
        if (!$assignment) return response()->json(['message' => 'Assignment not found'], 404);

        // Validasi otoritas: Pastikan instruktur ini yang memiliki course tersebut
        $course = Course::query()->find($assignment->course_id);
        if (!$course || $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to view these submissions'], 403);
        }

        // Ambil semua submission untuk soal ini
        $history = Assignment::query()
            ->where('title', 'like', '%Submission for: ' . $assignment->title . '%')
            ->where('type', 'submission')
            ->get();

        return response()->json($history);
    }
}
