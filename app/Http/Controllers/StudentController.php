<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function create()
    {
        $majors = Major::all();

        return Inertia::render('Student/Register', [
            'majors' => $majors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:students,nik',
            'nisn' => 'nullable|string|max:10',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:50',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:students,email',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'major_1' => 'required|exists:majors,id',
            'major_2' => 'required|exists:majors,id|different:major_1',
            'major_3' => 'nullable|exists:majors,id|different:major_1,major_2',
            'file_ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_akta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:1024',
        ]);

        DB::beginTransaction();

        try {
            // Generate registration number
            $year = date('Y');
            $lastStudent = Student::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
            $number = $lastStudent ? intval(substr($lastStudent->registration_number, -4)) + 1 : 1;
            $registrationNumber = sprintf('SPMB-%d-%04d', $year, $number);

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

            // Create student record
            $student = Student::create([
                'registration_number' => $registrationNumber,
                'full_name' => $validated['full_name'],
                'nik' => $validated['nik'],
                'nisn' => $validated['nisn'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'parent_name' => $validated['parent_name'],
                'parent_phone' => $validated['parent_phone'],
                ...$filePaths,
            ]);

            // Attach major preferences
            $student->majors()->attach($validated['major_1'], ['preference' => 1]);
            $student->majors()->attach($validated['major_2'], ['preference' => 2]);
            if ($validated['major_3']) {
                $student->majors()->attach($validated['major_3'], ['preference' => 3]);
            }

            DB::commit();

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
}
