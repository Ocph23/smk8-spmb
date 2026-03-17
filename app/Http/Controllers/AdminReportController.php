<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminReportController extends Controller
{
    /**
     * Display report page
     */
    public function index(Request $request)
    {
        // Build query with filters
        $query = Student::query();
        
        if ($request->filled('major')) {
            $query->whereHas('majors', function ($q) use ($request) {
                $q->where('majors.id', $request->major);
            });
        }
        
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        
        if ($request->filled('is_accepted')) {
            $query->where('is_accepted', $request->is_accepted === 'true');
        }
        
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $stats = [
            'total_pendaftar' => (clone $query)->count(),
            'laki_laki' => (clone $query)->where('gender', 'male')->count(),
            'perempuan' => (clone $query)->where('gender', 'female')->count(),
            'pending' => (clone $query)->where('verification_status', 'pending')->count(),
            'verified' => (clone $query)->where('verification_status', 'verified')->count(),
            'rejected' => (clone $query)->where('verification_status', 'rejected')->count(),
            'accepted' => (clone $query)->where('is_accepted', true)->count(),
            'not_accepted' => (clone $query)->where('is_accepted', false)->count(),
        ];

        // Major statistics
        $majorStats = Major::with([
            'students' => function ($query) {
                $query->select('students.id');
            },
            'acceptedStudents' => function ($query) {
                $query->select('students.id', 'accepted_major_id');
            }
        ])->get()->map(function ($major) {
            $major->total_pendaftar = $major->students->count();
            $major->diterima = $major->acceptedStudents->count();
            return $major;
        });

        $stats['per_jurusan'] = $majorStats;

        // Filters
        $filters = [
            'major' => $request->major,
            'verification_status' => $request->verification_status,
            'is_accepted' => $request->is_accepted,
            'gender' => $request->gender,
        ];

        $majors = Major::all();

        return Inertia::render('Admin/Reports/Index', [
            'stats' => $stats,
            'majors' => $majors,
            'filters' => $filters,
        ]);
    }

    /**
     * Generate PDF report
     */
    public function generatePdf(Request $request)
    {
        $query = Student::with(['majors', 'acceptedMajor', 'user']);

        // Apply filters
        if ($request->filled('major')) {
            $query->whereHas('majors', function ($q) use ($request) {
                $q->where('majors.id', $request->major);
            });
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('is_accepted')) {
            $query->where('is_accepted', $request->is_accepted === 'true');
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->orderBy('registration_number')->get();

        $stats = [
            'total_pendaftar' => $students->count(),
            'laki_laki' => $students->where('gender', 'male')->count(),
            'perempuan' => $students->where('gender', 'female')->count(),
            'diterima' => $students->where('is_accepted', true)->count(),
        ];

        // Major breakdown
        $majorStats = Major::with([
            'students',
            'acceptedStudents'
        ])->get()->map(function ($major) {
            $major->total_pendaftar = $major->students->count();
            $major->diterima = $major->acceptedStudents->count();
            return $major;
        });

        $data = [
            'students' => $students,
            'stats' => $stats,
            'majorStats' => $majorStats,
            'filters' => $request->only(['major', 'verification_status', 'is_accepted', 'gender']),
            'generated_at' => now()->format('d F Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.registration', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'laporan-pendaftaran-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate Excel-style report (CSV)
     */
    public function exportCsv(Request $request)
    {
        $query = Student::with(['majors', 'acceptedMajor']);

        // Apply filters
        if ($request->filled('major')) {
            $query->whereHas('majors', function ($q) use ($request) {
                $q->where('majors.id', $request->major);
            });
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('is_accepted')) {
            $query->where('is_accepted', $request->is_accepted === 'true');
        }

        $students = $query->orderBy('registration_number')->get();

        $csv = "No,No Pendaftaran,Nama Lengkap,NIK,NISN,Tempat Lahir,Tanggal Lahir,Jenis Kelamin,Agama,Alamat,Email,Telepon,Pilihan 1,Pilihan 2,Pilihan 3,Status Verifikasi,Diterima,Jurusan Diterima\n";

        foreach ($students as $index => $student) {
            $majors = $student->majors->sortBy('pivot.preference');
            $pilihan1 = $majors->firstWhere('pivot.preference', 1)?->name ?? '-';
            $pilihan2 = $majors->firstWhere('pivot.preference', 2)?->name ?? '-';
            $pilihan3 = $majors->firstWhere('pivot.preference', 3)?->name ?? '-';

            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $index + 1,
                $student->registration_number,
                $this->escapeCsv($student->full_name),
                $student->nik,
                $student->nisn ?? '-',
                $student->place_of_birth,
                $student->date_of_birth?->format('d-m-Y') ?? '-',
                $student->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                $student->religion,
                $this->escapeCsv($student->address),
                $student->email,
                $student->phone,
                $pilihan1,
                $pilihan2,
                $pilihan3,
                ucfirst($student->verification_status),
                $student->is_accepted ? 'Ya' : 'Belum',
                $student->acceptedMajor?->name ?? '-'
            );
        }

        $filename = 'laporan-pendaftaran-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Escape CSV field
     */
    private function escapeCsv($value)
    {
        $value = str_replace('"', '""', $value);
        if (preg_match('/[,"\n]/', $value)) {
            return '"' . $value . '"';
        }
        return $value;
    }

    /**
     * Get report data as JSON
     */
    public function getData(Request $request)
    {
        $query = Student::with(['majors', 'acceptedMajor']);

        if ($request->filled('major')) {
            $query->whereHas('majors', function ($q) use ($request) {
                $q->where('majors.id', $request->major);
            });
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('is_accepted')) {
            $query->where('is_accepted', $request->is_accepted === 'true');
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->orderBy('registration_number')->paginate(50);

        return response()->json(['students' => $students]);
    }
}
