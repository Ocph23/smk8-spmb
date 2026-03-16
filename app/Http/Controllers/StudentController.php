<?php

namespace App\Http\Controllers;

use App\Mail\StudentCredentialsMail;
use App\Models\Major;
use App\Models\Student;
use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Show registration form
     */
    public function create()
    {
        $majors = Major::all();
        $student = Auth::guard('student')->user();

        return Inertia::render('Student/Register', [
            'majors' => $majors,
            'student' => $student,
        ]);
    }

    /**
     * Store or update student registration
     */
    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();
        $isUpdate = $student && $student->registration_number && !str_starts_with($student->registration_number, 'DRAFT-');

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:students,nik,' . ($student?->id ?? 'NULL'),
            'nisn' => 'nullable|string|max:10',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:50',
            // Address fields
            'street' => 'required|string|max:255',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'dusun' => 'nullable|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:students,email,' . ($student?->id ?? 'NULL'),
            // Parent fields
            'parent_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            // Major preferences
            'major_1' => 'required|exists:majors,id',
            'major_2' => 'required|exists:majors,id|different:major_1',
            'major_3' => 'nullable|exists:majors,id|different:major_1,major_2',
            // Files
            'file_ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_akta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:1024',
        ]);

        DB::beginTransaction();

        try {
            // Upload files
            $filePaths = [];
            if ($request->hasFile('file_ijazah')) {
                $filePaths['file_ijazah'] = $request->file('file_ijazah')->store('documents/ijazah', 'public');
            }
            if ($request->hasFile('file_kk')) {
                $filePaths['file_kk'] = $request->file('file_kk')->store('documents/kk', 'public');
            }
            if ($request->hasFile('file_akta')) {
                $filePaths['file_akta'] = $request->file('file_akta')->store('documents/akta', 'public');
            }
            if ($request->hasFile('file_pas_photo')) {
                $filePaths['file_pas_photo'] = $request->file('file_pas_photo')->store('documents/photos', 'public');
            }

            if ($student && !$isUpdate) {
                // New registration - generate registration number and password
                $year = date('Y');

                //get las student where registration_number contain SPMB


                $lastStudent = Student::whereYear('created_at', $year)
                    ->where('registration_number', 'LIKE', 'SPMB-%')
                    ->orderBy('id', 'desc')
                    ->first();
                $number = $lastStudent ? intval(substr($lastStudent->registration_number, -4)) + 1 : 1;
                $registrationNumber = sprintf('SPMB-%d-%04d', $year, $number);

                // Generate random password for student
                $randomPassword = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);

                // Update student record
                $student->update([
                    'registration_number' => $registrationNumber,
                    'full_name' => $validated['full_name'],
                    'nik' => $validated['nik'],
                    'nisn' => $validated['nisn'],
                    'place_of_birth' => $validated['place_of_birth'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'religion' => $validated['religion'],
                    'street' => $validated['street'],
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'dusun' => $validated['dusun'] ?? null,
                    'district' => $validated['district'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'password' => Hash::make($randomPassword),
                    'parent_name' => $validated['parent_name'],
                    'mother_name' => $validated['mother_name'],
                    'parent_phone' => $validated['parent_phone'],
                    ...$filePaths,
                ]);

                // Create inbox message with login credentials
                Inbox::create([
                    'student_id' => $student->id,
                    'subject' => 'Pendaftaran Berhasil - Simpan Kredensial Login',
                    'message' => "Selamat! Pendaftaran Anda berhasil.\n\n" .
                        "Nomor Pendaftaran: {$registrationNumber}\n" .
                        "Email Login: {$validated['email']}\n" .
                        "Password: {$randomPassword}\n\n" .
                        "Silakan login di halaman login untuk melihat status pendaftaran Anda.\n" .
                        "Jangan berikan password kepada siapapun!",
                    'is_system' => true,
                ]);

                // Send email with credentials
                try {
                    Mail::to($student->email)->send(
                        new StudentCredentialsMail($student, $randomPassword)
                    );
                } catch (\Exception $e) {
                    // Log error but don't fail the registration
                    \Log::error('Failed to send credentials email: ' . $e->getMessage());
                }
            } elseif ($student && $isUpdate) {
                // Update existing registration - don't change password or registration number
                // Delete old files if new ones uploaded
                if ($request->hasFile('file_ijazah') && $student->file_ijazah) {
                    Storage::disk('public')->delete($student->file_ijazah);
                }
                if ($request->hasFile('file_kk') && $student->file_kk) {
                    Storage::disk('public')->delete($student->file_kk);
                }
                if ($request->hasFile('file_akta') && $student->file_akta) {
                    Storage::disk('public')->delete($student->file_akta);
                }
                if ($request->hasFile('file_pas_photo') && $student->file_pas_photo) {
                    Storage::disk('public')->delete($student->file_pas_photo);
                }

                // Update student record
                $student->update([
                    'full_name' => $validated['full_name'],
                    'nik' => $validated['nik'],
                    'nisn' => $validated['nisn'],
                    'place_of_birth' => $validated['place_of_birth'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'religion' => $validated['religion'],
                    'street' => $validated['street'],
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'dusun' => $validated['dusun'] ?? null,
                    'district' => $validated['district'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'parent_name' => $validated['parent_name'],
                    'mother_name' => $validated['mother_name'],
                    'parent_phone' => $validated['parent_phone'],
                    ...$filePaths,
                ]);

                // Send inbox notification
                Inbox::create([
                    'student_id' => $student->id,
                    'subject' => 'Data Pendaftaran Diperbarui',
                    'message' => "Data pendaftaran Anda telah diperbarui pada " . now()->format('d M Y H:i'),
                    'is_system' => true,
                ]);
            } else {
                // No authenticated student - create new account (for backward compatibility)
                $year = date('Y');
                $lastStudent = Student::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $number = $lastStudent ? intval(substr($lastStudent->registration_number, -4)) + 1 : 1;
                $registrationNumber = sprintf('SPMB-%d-%04d', $year, $number);

                $randomPassword = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);

                $student = Student::create([
                    'registration_number' => $registrationNumber,
                    'full_name' => $validated['full_name'],
                    'nik' => $validated['nik'],
                    'nisn' => $validated['nisn'],
                    'place_of_birth' => $validated['place_of_birth'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'religion' => $validated['religion'],
                    'street' => $validated['street'],
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'dusun' => $validated['dusun'] ?? null,
                    'district' => $validated['district'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'password' => Hash::make($randomPassword),
                    'parent_name' => $validated['parent_name'],
                    'mother_name' => $validated['mother_name'],
                    'parent_phone' => $validated['parent_phone'],
                    ...$filePaths,
                ]);

                Inbox::create([
                    'student_id' => $student->id,
                    'subject' => 'Pendaftaran Berhasil - Simpan Kredensial Login',
                    'message' => "Selamat! Pendaftaran Anda berhasil.\n\n" .
                        "Nomor Pendaftaran: {$registrationNumber}\n" .
                        "Email Login: {$validated['email']}\n" .
                        "Password: {$randomPassword}\n\n" .
                        "Silakan login di halaman login untuk melihat status pendaftaran Anda.\n" .
                        "Jangan berikan password kepada siapapun!",
                    'is_system' => true,
                ]);

                // Send email with credentials
                try {
                    Mail::to($student->email)->send(
                        new StudentCredentialsMail($student, $randomPassword)
                    );
                } catch (\Exception $e) {
                    // Log error but don't fail the registration
                    \Log::error('Failed to send credentials email: ' . $e->getMessage());
                }
            }

            // Attach major preferences (sync to avoid duplicates on update)
            $student->majors()->detach();
            $student->majors()->attach($validated['major_1'], ['preference' => 1]);
            $student->majors()->attach($validated['major_2'], ['preference' => 2]);
            if ($validated['major_3']) {
                $student->majors()->attach($validated['major_3'], ['preference' => 3]);
            }

            DB::commit();

            // Redirect based on context
            if ($student && $isUpdate) {
                return redirect()->route('student.preview', $student->registration_number)
                    ->with('success', 'Data pendaftaran berhasil diperbarui!');
            }

            return redirect()->route('student.certificate', $student->registration_number)
                ->with('success', 'Pendaftaran berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if any
            foreach ($filePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function certificate($registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();

        return Inertia::render('Student/Certificate', [
            'student' => $student,
        ]);
    }

    public function printCertificate($registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();

        $pdf = \PDF::loadView('certificates.registration', compact('student'));

        return $pdf->download('bukti-pendaftaran-' . $registrationNumber . '.pdf');
    }

    /**
     * Show registration preview for students to review their data
     */
    public function preview($registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();

        return Inertia::render('Student/Preview', [
            'student' => $student,
        ]);
    }

    /**
     * Show edit form for students to update their registration
     */
    public function edit($registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();
        $majors = Major::all();

        return Inertia::render('Student/Edit', [
            'student' => $student,
            'majors' => $majors,
        ]);
    }

    /**
     * Update student registration
     */
    public function update(Request $request, $registrationNumber)
    {
        $student = Student::with('majors')->where('registration_number', $registrationNumber)->firstOrFail();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:students,nik,' . $student->id,
            'nisn' => 'nullable|string|max:10',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:50',
            // Address fields
            'street' => 'required|string|max:255',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'dusun' => 'nullable|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:students,email,' . $student->id,
            // Parent fields
            'parent_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            // Major preferences
            'major_1' => 'required|exists:majors,id',
            'major_2' => 'required|exists:majors,id|different:major_1',
            'major_3' => 'nullable|exists:majors,id|different:major_1,major_2',
            // Files (optional - only upload new ones if provided)
            'file_ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_akta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:1024',
        ]);

        DB::beginTransaction();

        try {
            // Upload new files if provided
            $filePaths = [];
            if ($request->hasFile('file_ijazah')) {
                // Delete old file
                if ($student->file_ijazah) {
                    Storage::disk('public')->delete($student->file_ijazah);
                }
                $filePaths['file_ijazah'] = $request->file('file_ijazah')->store('documents/ijazah', 'public');
            }
            if ($request->hasFile('file_kk')) {
                if ($student->file_kk) {
                    Storage::disk('public')->delete($student->file_kk);
                }
                $filePaths['file_kk'] = $request->file('file_kk')->store('documents/kk', 'public');
            }
            if ($request->hasFile('file_akta')) {
                if ($student->file_akta) {
                    Storage::disk('public')->delete($student->file_akta);
                }
                $filePaths['file_akta'] = $request->file('file_akta')->store('documents/akta', 'public');
            }
            if ($request->hasFile('file_pas_photo')) {
                if ($student->file_pas_photo) {
                    Storage::disk('public')->delete($student->file_pas_photo);
                }
                $filePaths['file_pas_photo'] = $request->file('file_pas_photo')->store('documents/photos', 'public');
            }

            // Update student record
            $student->update([
                'full_name' => $validated['full_name'],
                'nik' => $validated['nik'],
                'nisn' => $validated['nisn'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'religion' => $validated['religion'],
                'street' => $validated['street'],
                'rt' => $validated['rt'] ?? null,
                'rw' => $validated['rw'] ?? null,
                'dusun' => $validated['dusun'] ?? null,
                'district' => $validated['district'],
                'postal_code' => $validated['postal_code'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'parent_name' => $validated['parent_name'],
                'mother_name' => $validated['mother_name'],
                'parent_phone' => $validated['parent_phone'],
                ...$filePaths,
            ]);

            // Sync major preferences
            $student->majors()->detach();
            $student->majors()->attach($validated['major_1'], ['preference' => 1]);
            $student->majors()->attach($validated['major_2'], ['preference' => 2]);
            if ($validated['major_3']) {
                $student->majors()->attach($validated['major_3'], ['preference' => 3]);
            }

            DB::commit();

            return redirect()->route('student.preview', $student->registration_number)
                ->with('success', 'Data pendaftaran berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if any
            foreach ($filePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
