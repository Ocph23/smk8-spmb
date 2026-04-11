<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Student;
use App\Services\AcademicYearService;
use App\Services\EnrollmentWaveService;
use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct(
        protected AcademicYearService $service,
        protected EnrollmentWaveService $enrollmentWaveService,
    ) {}

    public function dashboard()
    {
        $context = $this->service->resolveContext(request());

        $baseQuery = Student::query();
        if ($context) {
            $baseQuery->where('academic_year_id', $context->id);
        }

        $stats = $baseQuery->selectRaw("
            COUNT(*) as total_students,
            SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending_verification,
            SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END) as verified,
            SUM(CASE WHEN is_accepted = 1 THEN 1 ELSE 0 END) as accepted
        ")->first();

        $recentStudents = Student::with(['majors', 'acceptedMajor'])
            ->when($context, fn ($q) => $q->where('academic_year_id', $context->id))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats'               => $stats,
            'recentStudents'      => $recentStudents,
            'currentAcademicYear' => $context,
        ]);
    }

    public function students(Request $request)
    {
        $context = $this->service->resolveContext($request);

        $query = Student::with(['majors', 'acceptedMajor', 'user', 'enrollmentWave']);

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

        if ($request->filled('wave')) {
            $query->where('enrollment_wave_id', $request->wave);
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $majors = Major::all();

        $waves = $context
            ? \App\Models\EnrollmentWave::where('academic_year_id', $context->id)
            ->orderBy('wave_number')
            ->get()
            : collect();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'majors' => $majors,
            'waves' => $waves,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'major' => $request->major,
                'wave' => $request->wave,
            ],
            'currentAcademicYear' => $context,
        ]);
    }

    public function showStudent(Student $student)
    {
        $student->load(['majors', 'acceptedMajor', 'user', 'documents.registrationDocument', 'enrollmentWave']);

        $waveQuotas = null;
        if ($student->enrollmentWave) {
            $wave = $student->enrollmentWave;
            $wave->load('majors');
            $waveQuotas = $wave->majors->map(function ($major) use ($wave) {
                $quota    = (int) $major->pivot->quota;
                $accepted = $this->enrollmentWaveService->getAcceptedCountInWave($wave, $major->id);
                return [
                    'major_id'   => $major->id,
                    'major_name' => $major->name,
                    'quota'      => $quota,
                    'accepted'   => $accepted,
                    'remaining'  => max(0, $quota - $accepted),
                ];
            });
        }

        return Inertia::render('Admin/Students/Show', [
            'student'    => $student,
            'waveQuotas' => $waveQuotas,
        ]);
    }

    public function verifyStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,verified,rejected',
            'note'   => 'nullable|string|max:500',
        ]);

        $oldNote   = $student->verification_note;
        $newNote   = $validated['note'] ?? null;
        $newStatus = $validated['status'];

        $student->update([
            'verification_status' => $newStatus,
            'verification_note'   => $newNote,
        ]);

        // Kirim notifikasi untuk: verified, rejected, atau pending dengan note baru/berubah
        $noteChanged = trim((string) $oldNote) !== trim((string) $newNote);
        $shouldNotify = in_array($newStatus, ['verified', 'rejected'])
            || ($newStatus === 'pending' && $noteChanged && $newNote);

        if ($shouldNotify) {
            $statusLabel = match ($newStatus) {
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak',
                default    => 'Dalam Peninjauan',
            };

            $inboxMessage = match ($newStatus) {
                'verified' => 'Selamat! Berkas pendaftaran Anda telah diverifikasi dan dinyatakan lengkap.'
                    . ($newNote ? " Catatan: {$newNote}" : ''),
                'rejected' => 'Mohon maaf, pendaftaran Anda ditolak.'
                    . ($newNote ? " Catatan: {$newNote}" : ''),
                default    => 'Ada catatan baru dari panitia mengenai pendaftaran Anda.'
                    . ($newNote ? " Catatan: {$newNote}" : ''),
            };

            Inbox::create([
                'student_id' => $student->id,
                'subject'    => "Status Verifikasi: {$statusLabel}",
                'message'    => $inboxMessage,
                'is_system'  => true,
            ]);

            // Email dikirim otomatis oleh InboxObserver
        }

        return back()->with('success', 'Verifikasi berhasil diperbarui.');
    }

    public function allocateMajor(Request $request, Student $student)
    {
        $validated = $request->validate([
            'major_id'    => 'required|exists:majors,id',
            'is_accepted' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Lock student row to prevent race condition
            $student = Student::lockForUpdate()->findOrFail($student->id);
            $major   = Major::findOrFail($validated['major_id']);

            $student->load(['enrollmentWave', 'academicYear']);
            $wave        = $student->enrollmentWave;
            $academicYear = $student->academicYear;

            if ($wave) {
                $quota           = $this->enrollmentWaveService->getWaveQuota($wave, $major->id);
                $currentAccepted = $this->enrollmentWaveService->getAcceptedCountInWave($wave, $major->id);
                if ($student->is_accepted && $student->accepted_major_id == $major->id) {
                    $currentAccepted = max(0, $currentAccepted - 1);
                }
            } else {
                $quota = $academicYear
                    ? $this->service->getQuotaForMajor($academicYear, $major->id)
                    : ($major->quota ?? 30);

                $currentAccepted = Student::where('accepted_major_id', $major->id)
                    ->where('is_accepted', true)
                    ->where('id', '!=', $student->id)
                    ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
                    ->count();
            }

            if ($validated['is_accepted'] && $currentAccepted >= $quota) {
                DB::rollBack();
                return back()->withErrors(['error' => "Kuota jurusan {$major->name} pada gelombang ini sudah penuh ({$quota})."]);
            }

            $student->update([
                'accepted_major_id'   => $validated['major_id'],
                'is_accepted'         => $validated['is_accepted'],
                'verification_status' => 'verified',
            ]);

            DB::commit();

            return back()->with('success', 'Alokasi jurusan berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Allocate major error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat alokasi jurusan.']);
        }
    }

    public function destroyStudent(Student $student)
    {
        $student->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
