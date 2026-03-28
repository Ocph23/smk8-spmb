<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Student;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct(
        protected AcademicYearService $service
    ) {}

    public function dashboard()
    {
        $context = $this->service->resolveContext(request());

        $query = Student::query();
        if ($context) {
            $query->where('academic_year_id', $context->id);
        }

        $stats = [
            'total_students' => (clone $query)->count(),
            'pending_verification' => (clone $query)->where('verification_status', 'pending')->count(),
            'verified' => (clone $query)->where('verification_status', 'verified')->count(),
            'accepted' => (clone $query)->where('is_accepted', true)->count(),
        ];

        $recentStudents = (clone $query)->with(['majors', 'acceptedMajor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentStudents' => $recentStudents,
            'currentAcademicYear' => $context,
        ]);
    }

    public function students(Request $request)
    {
        $context = $this->service->resolveContext($request);

        $query = Student::with(['majors', 'acceptedMajor', 'user']);

        if ($context) {
            $query->where('academic_year_id', $context->id);
        }

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
            'currentAcademicYear' => $context,
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
            'verification_note' => $validated['note'] ?? null,
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
            $major = Major::find($validated['major_id']);

            // Load relasi academicYear pada student
            $student->load('academicYear');
            $academicYear = $student->academicYear;

            // Gunakan service untuk mendapatkan kuota dan jumlah diterima
            $quota = $academicYear
                ? $this->service->getQuotaForMajor($academicYear, $major->id)
                : ($major->quota ?? 30);

            $currentAccepted = $academicYear
                ? Student::where('academic_year_id', $academicYear->id)
                    ->where('accepted_major_id', $major->id)
                    ->where('is_accepted', true)
                    ->where('id', '!=', $student->id)
                    ->count()
                : Student::where('accepted_major_id', $major->id)
                    ->where('is_accepted', true)
                    ->where('id', '!=', $student->id)
                    ->count();

            if ($validated['is_accepted'] && $currentAccepted >= $quota) {
                return back()->withErrors(['error' => "Kuota jurusan {$major->name} sudah penuh ({$quota})."]);
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
