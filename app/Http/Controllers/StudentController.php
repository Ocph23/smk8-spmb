<?php

namespace App\Http\Controllers;

use App\Mail\StudentCredentialsMail;
use App\Models\Inbox;
use App\Models\Major;
use App\Models\RegistrationDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function __construct(protected AcademicYearService $academicYearService) {}

    /**
     * Show registration form
     */
    public function create()
    {
        $majors = Major::all();
        $student = Auth::guard('student')->user();
        $activeYear = $this->academicYearService->getActive();

        $registrationDocuments = RegistrationDocument::active()
            ->orderBy('order')
            ->get()
            ->map(function ($doc) use ($student) {
                $studentDoc = null;
                if ($student && $student->id) {
                    $studentDoc = StudentDocument::where('student_id', $student->id)
                        ->where('registration_document_id', $doc->id)
                        ->first();
                }
                
                return [
                    'id'            => $doc->id,
                    'name'          => $doc->name,
                    'label'         => $doc->label,
                    'description'   => $doc->description,
                    'field_name'    => $doc->field_name,
                    'accepted_types' => $doc->accepted_types,
                    'max_size'      => $doc->max_size,
                    'is_required'   => $doc->is_required,
                    'order'         => $doc->order,
                    'existing_file' => $studentDoc ? [
                        'file_path' => $studentDoc->file_path,
                        'file_name' => $studentDoc->file_name,
                    ] : null,
                ];
            });

        return Inertia::render('Student/Register', [
            'majors'             => $majors,
            'student'            => $student,
            'registrationClosed' => $activeYear === null,
            'activeYear'         => $activeYear,
            'registrationDocuments' => $registrationDocuments,
        ]);
    }

    /**
     * Store or update student registration
     */
    public function store(Request $request)
    {
        $activeYear = $this->academicYearService->getActive();

        if (!$activeYear) {
            return response()->json([
                'message' => 'Pendaftaran belum dibuka. Tidak ada tahun ajaran aktif saat ini.',
            ], 422);
        }

        $student = Auth::guard('student')->user();
        $isUpdate = $student && $student->registration_number && !str_starts_with($student->registration_number, 'DRAFT-');

        $nikRule = 'required|string|size:16|unique:students,nik,' . ($student?->id ?? 'NULL') . ',id,academic_year_id,' . $activeYear->id;

        $documents = RegistrationDocument::active()->orderBy('order')->get();
        $fileValidationRules = [];
        foreach ($documents as $doc) {
            $mimes = str_replace(',', ',', $doc->accepted_types);
            $rule = $doc->is_required ? 'required' : 'nullable';
            $rule .= '|file|mimes:' . $mimes . '|max:' . $doc->max_size;
            $fileValidationRules[$doc->field_name] = $rule;
        }

        $validated = $request->validate([
            'full_name'      => 'required|string|max:255',
            'nik'            => $nikRule,
            'nisn'           => 'nullable|string|max:10',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth'  => 'required|date',
            'gender'         => 'required|in:male,female',
            'religion'       => 'nullable|string|max:50',
            'street'         => 'required|string|max:255',
            'rt'             => 'nullable|string|max:10',
            'rw'             => 'nullable|string|max:10',
            'dusun'          => 'nullable|string|max:100',
            'district'       => 'required|string|max:100',
            'postal_code'    => 'nullable|string|max:10',
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|unique:students,email,' . ($student?->id ?? 'NULL'),
            'parent_name'    => 'required|string|max:255',
            'mother_name'    => 'required|string|max:255',
            'parent_phone'   => 'required|string|max:20',
            'school_name'    => 'required|string|max:255',
            'school_city'    => 'required|string|max:100',
            'school_province' => 'required|string|max:100',
            'major_1'        => 'required|exists:majors,id',
            'major_2'        => 'required|exists:majors,id|different:major_1',
            'major_3'        => 'nullable|exists:majors,id|different:major_1,major_2',
        ] + $fileValidationRules);

        DB::beginTransaction();

        try {
            $filePaths = $this->uploadFiles($request, $student);

            if ($student && !$isUpdate) {
                $registrationNumber = $this->generateRegistrationNumber($activeYear);

                $student->update(array_merge([
                    'academic_year_id'    => $activeYear->id,
                    'registration_number' => $registrationNumber,
                ], $this->studentFields($validated), $filePaths));

                $this->sendCredentialsInbox($student, $registrationNumber, $validated['email'], null);
                $this->sendCredentialsMail($student, null);

            } elseif ($student && $isUpdate) {
                $this->deleteOldFiles($request, $student);
                $student->update(array_merge($this->studentFields($validated), $filePaths));

                Inbox::create([
                    'student_id' => $student->id,
                    'subject'    => 'Data Pendaftaran Diperbarui',
                    'message'    => 'Data pendaftaran Anda telah diperbarui pada ' . now()->format('d M Y H:i'),
                    'is_system'  => true,
                ]);
            } else {
                $registrationNumber = $this->generateRegistrationNumber($activeYear);
                $randomPassword = $this->generatePassword();

                $student = Student::create(array_merge([
                    'academic_year_id'    => $activeYear->id,
                    'registration_number' => $registrationNumber,
                    'password'            => Hash::make($randomPassword),
                ], $this->studentFields($validated), $filePaths));

                $this->sendCredentialsInbox($student, $registrationNumber, $validated['email'], $randomPassword);
                $this->sendCredentialsMail($student, $randomPassword);
            }

            $student->majors()->detach();
            $student->majors()->attach($validated['major_1'], ['preference' => 1]);
            $student->majors()->attach($validated['major_2'], ['preference' => 2]);
            if (!empty($validated['major_3'])) {
                $student->majors()->attach($validated['major_3'], ['preference' => 3]);
            }

            DB::commit();

            if ($isUpdate) {
                return redirect()->route('student.preview', $student->registration_number)
                    ->with('success', 'Data pendaftaran berhasil diperbarui!');
            }

            return redirect()->route('student.certificate', $student->registration_number)
                ->with('success', 'Pendaftaran berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($filePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function certificate($registrationNumber)
    {
        $student = Student::with(['majors', 'documents.registrationDocument'])
            ->where('registration_number', $registrationNumber)->firstOrFail();

        return Inertia::render('Student/Certificate', ['student' => $student]);
    }

    public function printCertificate($registrationNumber)
    {
        $student = Student::with(['majors', 'documents.registrationDocument'])
            ->where('registration_number', $registrationNumber)->firstOrFail();

        $pdf = \PDF::loadView('certificates.registration', compact('student'));

        return $pdf->download('bukti-pendaftaran-' . $registrationNumber . '.pdf');
    }

    public function preview($registrationNumber)
    {
        $student = Student::with(['majors', 'documents.registrationDocument'])
            ->where('registration_number', $registrationNumber)->firstOrFail();

        return Inertia::render('Student/Preview', ['student' => $student]);
    }
    public function edit($registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();

        $authStudent = Auth::guard('student')->user();
        if (!$authStudent || $authStudent->id !== $student->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $registrationDocuments = RegistrationDocument::active()
            ->orderBy('order')
            ->get()
            ->map(function ($doc) use ($student) {
                $studentDoc = StudentDocument::where('student_id', $student->id)
                    ->where('registration_document_id', $doc->id)
                    ->first();
                
                return [
                    'id'            => $doc->id,
                    'name'          => $doc->name,
                    'label'         => $doc->label,
                    'description'   => $doc->description,
                    'field_name'    => $doc->field_name,
                    'accepted_types' => $doc->accepted_types,
                    'max_size'      => $doc->max_size,
                    'is_required'   => $doc->is_required,
                    'order'         => $doc->order,
                    'existing_file' => $studentDoc ? [
                        'file_path' => $studentDoc->file_path,
                        'file_name' => $studentDoc->file_name,
                    ] : null,
                ];
            });

        return Inertia::render('Student/Edit', [
            'student' => $student,
            'majors'  => Major::all(),
            'registrationDocuments' => $registrationDocuments,
        ]);
    }

    public function update(Request $request, $registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();

        $authStudent = Auth::guard('student')->user();
        if (!$authStudent || $authStudent->id !== $student->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $documents = RegistrationDocument::active()->orderBy('order')->get();
        $fileValidationRules = [];
        foreach ($documents as $doc) {
            $mimes = str_replace(',', ',', $doc->accepted_types);
            $rule = 'nullable|file|mimes:' . $mimes . '|max:' . $doc->max_size;
            $fileValidationRules[$doc->field_name] = $rule;
        }

        $validated = $request->validate([
            'full_name'      => 'required|string|max:255',
            'nik'            => 'required|string|size:16|unique:students,nik,' . $student->id,
            'nisn'           => 'nullable|string|max:10',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth'  => 'required|date',
            'gender'         => 'required|in:male,female',
            'religion'       => 'nullable|string|max:50',
            'street'         => 'required|string|max:255',
            'rt'             => 'nullable|string|max:10',
            'rw'             => 'nullable|string|max:10',
            'dusun'          => 'nullable|string|max:100',
            'district'       => 'required|string|max:100',
            'postal_code'    => 'nullable|string|max:10',
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|unique:students,email,' . $student->id,
            'parent_name'    => 'required|string|max:255',
            'mother_name'    => 'required|string|max:255',
            'parent_phone'   => 'required|string|max:20',
            'school_name'    => 'required|string|max:255',
            'school_city'    => 'required|string|max:100',
            'school_province' => 'required|string|max:100',
            'major_1'        => 'required|exists:majors,id',
            'major_2'        => 'required|exists:majors,id|different:major_1',
            'major_3'        => 'nullable|exists:majors,id|different:major_1,major_2',
        ] + $fileValidationRules);

        DB::beginTransaction();

        try {
            $this->deleteOldFiles($request, $student);
            $filePaths = $this->uploadFiles($request, $student);

            $student->update(array_merge($this->studentFields($validated), $filePaths));

            $student->majors()->detach();
            $student->majors()->attach($validated['major_1'], ['preference' => 1]);
            $student->majors()->attach($validated['major_2'], ['preference' => 2]);
            if (!empty($validated['major_3'])) {
                $student->majors()->attach($validated['major_3'], ['preference' => 3]);
            }

            DB::commit();

            return redirect()->route('student.preview', $student->registration_number)
                ->with('success', 'Data pendaftaran berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function generateRegistrationNumber(\App\Models\AcademicYear $activeYear): string
    {
        $year = $activeYear->end_year;
        $last = Student::where('academic_year_id', $activeYear->id)
            ->where('registration_number', 'LIKE', "SPMB-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();
        $number = $last ? intval(substr($last->registration_number, -4)) + 1 : 1;
        return sprintf('SPMB-%d-%04d', $year, $number);
    }

    private function generatePassword(): string
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
    }

    private function studentFields(array $validated): array
    {
        return [
            'full_name'      => $validated['full_name'],
            'nik'            => $validated['nik'],
            'nisn'           => $validated['nisn'] ?? null,
            'place_of_birth' => $validated['place_of_birth'],
            'date_of_birth'  => $validated['date_of_birth'],
            'gender'         => $validated['gender'],
            'religion'       => $validated['religion'] ?? null,
            'street'         => $validated['street'],
            'rt'             => $validated['rt'] ?? null,
            'rw'             => $validated['rw'] ?? null,
            'dusun'          => $validated['dusun'] ?? null,
            'district'       => $validated['district'],
            'postal_code'    => $validated['postal_code'] ?? null,
            'phone'          => $validated['phone'],
            'email'          => $validated['email'],
            'parent_name'    => $validated['parent_name'],
            'mother_name'    => $validated['mother_name'],
            'parent_phone'   => $validated['parent_phone'],
            'school_name'    => $validated['school_name'],
            'school_city'    => $validated['school_city'],
            'school_province' => $validated['school_province'],
        ];
    }

    private function uploadFiles(Request $request, ?Student $student): array
    {
        $paths = [];
        $documents = RegistrationDocument::active()->orderBy('order')->get();
        
        foreach ($documents as $doc) {
            $fieldName = $doc->field_name;
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $filePath = $file->store('documents/' . $doc->name, 'public');
                $paths[$fieldName] = $filePath;

                if ($student && $student->id) {
                    StudentDocument::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'registration_document_id' => $doc->id,
                        ],
                        [
                            'file_path' => $filePath,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                        ]
                    );
                }
            }
        }
        return $paths;
    }

    private function deleteOldFiles(Request $request, Student $student): void
    {
        $documents = RegistrationDocument::active()->orderBy('order')->get();
        
        foreach ($documents as $doc) {
            $fieldName = $doc->field_name;
            if ($request->hasFile($fieldName)) {
                $studentDoc = StudentDocument::where('student_id', $student->id)
                    ->where('registration_document_id', $doc->id)
                    ->first();
                
                if ($studentDoc && $studentDoc->file_path) {
                    Storage::disk('public')->delete($studentDoc->file_path);
                    $studentDoc->delete();
                }
            }
        }
    }

    private function sendCredentialsInbox(Student $student, string $regNum, string $email, ?string $password): void
    {
        $passwordLine = $password ? "Password: {$password}\n" : '';

        Inbox::create([
            'student_id' => $student->id,
            'subject'    => 'Pendaftaran Berhasil',
            'message'    => "Selamat! Pendaftaran Anda berhasil.\n\n" .
                "Nomor Pendaftaran: {$regNum}\n" .
                "Email Login: {$email}\n" .
                $passwordLine .
                "\nSilakan login di halaman login untuk melihat status pendaftaran Anda.",
            'is_system'  => true,
        ]);
    }

    private function sendCredentialsMail(Student $student, ?string $password): void
    {
        if (!$password) return;

        try {
            Mail::to($student->email)->send(new StudentCredentialsMail($student, $password));
        } catch (\Exception $e) {
            \Log::error('Failed to send credentials email: ' . $e->getMessage());
        }
    }
}
