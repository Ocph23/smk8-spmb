<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'pending_verification' => Student::where('verification_status', 'pending')->count(),
            'verified' => Student::where('verification_status', 'verified')->count(),
            'accepted' => Student::where('is_accepted', true)->count(),
        ];

        $recentStudents = Student::with(['majors', 'acceptedMajor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentStudents' => $recentStudents,
        ]);
    }

    public function students(Request $request)
    {
        $query = Student::with(['majors', 'acceptedMajor', 'user']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('major')) {
            $query->whereHas('majors', function ($q) use ($request) {
                $q->where('majors.id', $request->major);
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $majors = Major::all();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'majors' => $majors,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'major' => $request->major,
            ],
        ]);
    }

    public function showStudent(Student $student)
    {
        $student->load(['majors', 'acceptedMajor', 'user']);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
        ]);
    }

    public function verifyStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'note' => 'nullable|string|max:500',
        ]);

        $student->update([
            'verification_status' => $validated['status'],
            'verification_note' => $validated['note'],
        ]);

        return back()->with('success', 'Verifikasi berhasil diperbarui.');
    }

    public function allocateMajor(Request $request, Student $student)
    {
        $validated = $request->validate([
            'major_id' => 'required|exists:majors,id',
            'is_accepted' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Check quota
            $major = Major::find($validated['major_id']);
            $currentAccepted = Student::where('accepted_major_id', $major->id)
                ->where('is_accepted', true)
                ->count();

            if ($validated['is_accepted'] && $currentAccepted >= $major->quota) {
                return back()->withErrors(['error' => "Kuota jurusan {$major->name} sudah penuh ({$major->quota})."]);
            }

            $student->update([
                'accepted_major_id' => $validated['major_id'],
                'is_accepted' => $validated['is_accepted'],
                'verification_status' => 'verified',
            ]);

            DB::commit();

            return back()->with('success', 'Alokasi jurusan berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function destroyStudent(Student $student)
    {
        $student->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
