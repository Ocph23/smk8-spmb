<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentWave;
use App\Models\AcademicYear;
use App\Models\Inbox;
use App\Models\Major;
use App\Models\Student;
use App\Services\AcademicYearService;
use App\Services\EnrollmentWaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            $query->where(function ($q) use ($context) {
                $q->where('academic_year_id', $context->id)
                    ->orWhere(function ($draftQuery) {
                        $draftQuery->whereNull('academic_year_id')
                            ->where('registration_number', 'like', 'DRAFT-%');
                    });
            });
        }

        // Filters
        if ($request->filled('search')) {
            $search = mb_strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(full_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(registration_number) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nik) LIKE ?', ["%{$search}%"]);
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
            $query->where(function ($q) use ($request) {
                $q->where('enrollment_wave_id', $request->wave)
                    ->orWhere(function ($draftQuery) {
                        $draftQuery->whereNull('enrollment_wave_id')
                            ->where('registration_number', 'like', 'DRAFT-%');
                    });
            });
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

    public function createStudent(Request $request)
    {
        $context = $this->service->resolveContext($request);
        $openWave = $context
            ? EnrollmentWave::where('academic_year_id', $context->id)
                ->where('status', 'open')
                ->first()
            : null;

        return Inertia::render('Admin/Students/Create', [
            'currentAcademicYear' => $context,
            'openWave' => $openWave,
            'majors' => Major::orderBy('code')->get(),
        ]);
    }

    public function storeStudent(Request $request)
    {
        $context = $this->service->resolveContext($request);
        $nikRule = ['required', 'string', 'size:16'];
        if ($context?->id) {
            $nikRule[] = Rule::unique('students', 'nik')->where(fn ($query) => $query->where('academic_year_id', $context->id));
        } else {
            $nikRule[] = Rule::unique('students', 'nik');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => $nikRule,
            'nisn' => 'nullable|string|max:10',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before_or_equal:' . now()->subYears(14)->format('Y-m-d') . '|after_or_equal:' . now()->subYears(21)->format('Y-m-d'),
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:50',
            'street' => 'required|string|max:255',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'dusun' => 'nullable|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'school_name' => 'required|string|max:255',
            'school_city' => 'required|string|max:100',
            'school_province' => 'required|string|max:100',
            'parent_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parent_phone' => ['nullable', 'string', 'max:20', 'regex:/^08[0-9]{8,}$/'],
            'email' => 'required|email|unique:students,email',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^08[0-9]{8,}$/'],
            'major_1' => 'required|exists:majors,id',
            'major_2' => 'required|exists:majors,id|different:major_1',
            'major_3' => 'nullable|exists:majors,id|different:major_1,major_2',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nik.size' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah digunakan pada tahun ajaran ini.',
            'date_of_birth.before_or_equal' => 'Usia pendaftar minimal 14 tahun.',
            'date_of_birth.after_or_equal' => 'Usia pendaftar maksimal 21 tahun.',
            'phone.regex' => 'Nomor telepon harus diawali 08 dan minimal 10 digit.',
            'parent_phone.regex' => 'Nomor telepon orang tua harus diawali 08 dan minimal 10 digit.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $openWave = $context
            ? EnrollmentWave::where('academic_year_id', $context->id)
                ->where('status', 'open')
                ->first()
            : null;

        $registrationNumber = $this->generateAdminRegistrationNumber($context, $openWave);
        $plainPassword = $validated['password'];

        $student = DB::transaction(function () use ($validated, $context, $openWave, $plainPassword, $registrationNumber) {
            $student = Student::create([
                'academic_year_id' => $context?->id,
                'enrollment_wave_id' => $openWave?->id,
                'registration_number' => $registrationNumber,
                'full_name' => trim($validated['full_name']),
                'nik' => trim($validated['nik']),
                'nisn' => !empty($validated['nisn']) ? trim($validated['nisn']) : null,
                'place_of_birth' => trim($validated['place_of_birth']),
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'religion' => $validated['religion'] ?? null,
                'street' => trim($validated['street']),
                'rt' => !empty($validated['rt']) ? trim($validated['rt']) : null,
                'rw' => !empty($validated['rw']) ? trim($validated['rw']) : null,
                'dusun' => !empty($validated['dusun']) ? trim($validated['dusun']) : null,
                'district' => trim($validated['district']),
                'postal_code' => !empty($validated['postal_code']) ? trim($validated['postal_code']) : null,
                'school_name' => trim($validated['school_name']),
                'school_city' => trim($validated['school_city']),
                'school_province' => trim($validated['school_province']),
                'parent_name' => trim($validated['parent_name']),
                'mother_name' => trim($validated['mother_name']),
                'parent_phone' => !empty($validated['parent_phone']) ? trim($validated['parent_phone']) : null,
                'email' => strtolower(trim($validated['email'])),
                'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
                'password' => Hash::make($plainPassword),
                'verification_status' => 'pending',
            ]);

            $student->majors()->detach();
            $student->majors()->attach($validated['major_1'], ['preference' => 1]);
            $student->majors()->attach($validated['major_2'], ['preference' => 2]);
            if (!empty($validated['major_3'])) {
                $student->majors()->attach($validated['major_3'], ['preference' => 3]);
            }

            Inbox::create([
                'student_id' => $student->id,
                'subject' => 'Akun Pendaftaran Dibuat',
                'message' => "Akun pendaftaran Anda telah dibuat oleh admin.\n\n"
                    . "Email Login: {$student->email}\n"
                    . "Password: {$plainPassword}\n"
                    . "Silakan login dan lengkapi data pendaftaran Anda.",
                'is_system' => true,
            ]);

            return $student;
        });

        return redirect()->route('admin.students.show', $student->id)
            ->with('success', 'Pendaftar baru berhasil dibuat.');
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

    private function generateAdminRegistrationNumber(?AcademicYear $context, ?EnrollmentWave $openWave): string
    {
        if ($openWave) {
            return $this->enrollmentWaveService->generateRegistrationNumber($openWave);
        }

        $year = $context ?? AcademicYear::where('status', 'active')->first();

        if (! $year) {
            return 'SPMB-' . now()->format('Y') . '-ADM-' . strtoupper(Str::random(6));
        }

        $seq = Student::where('academic_year_id', $year->id)->count() + 1;

        return sprintf('SPMB-%s-ADM-%04d', $year->end_year, $seq);
    }
}
