<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        return Inertia::render('Announcement/Index');
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string',
            'nik' => 'required|string',
        ]);

        $student = Student::with(['majors', 'acceptedMajor', 'enrollmentWave'])
            ->where('registration_number', $validated['registration_number'])
            ->where('nik', $validated['nik'])
            ->first();

        if (!$student) {
            return back()->withErrors(['error' => 'Nomor pendaftaran tidak ditemukan.']);
        }

        if ($student->enrollmentWave && ! $student->enrollmentWave->isAnnounced()) {
            return back()->withErrors(['error' => 'Hasil seleksi untuk gelombang ini belum diumumkan.']);
        }

        return Inertia::render('Announcement/Result', [
            'student'        => $student,
            'enrollmentWave' => $student->enrollmentWave,
        ]);
    }
}
