<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function __construct(private AcademicYearService $service) {}

    public function index()
    {
        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => AcademicYear::withCount('students')
                ->orderBy('start_year', 'desc')
                ->get(),
            'majors' => Major::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:20',
            'start_year'  => 'required|integer|min:2000',
            'end_year'    => 'required|integer|min:2000|gt:start_year',
            'description' => 'nullable|string',
        ]);

        $this->service->create($validated);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:20',
            'start_year'  => 'required|integer|min:2000',
            'end_year'    => 'required|integer|min:2000|gt:start_year',
            'description' => 'nullable|string',
        ]);

        $academicYear->update($validated);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function activate(AcademicYear $academicYear)
    {
        $this->service->activate($academicYear);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil diaktifkan.');
    }

    public function close(AcademicYear $academicYear)
    {
        $this->service->close($academicYear);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil ditutup.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $count = $academicYear->students()->count();

        if ($count > 0) {
            return back()->withErrors([
                'error' => "Tahun ajaran tidak dapat dihapus karena memiliki {$count} data pendaftaran.",
            ]);
        }

        $academicYear->delete();

        return redirect()->back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function setContext(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        session(['academic_year_context' => $validated['academic_year_id']]);

        return redirect()->back();
    }

    public function majorConfig(AcademicYear $academicYear)
    {
        $academicYear->load('majors');

        $allMajors = Major::all();

        $pivotData = $academicYear->majors->keyBy('id');

        $majors = $allMajors->map(function ($major) use ($pivotData, $academicYear) {
            $pivot = $pivotData->get($major->id);
            $major->pivot_quota     = $pivot?->pivot->quota ?? $major->quota;
            $major->pivot_is_active = $pivot ? (bool) $pivot->pivot->is_active : true;
            return $major;
        });

        return Inertia::render('Admin/AcademicYears/MajorConfig', [
            'academicYear' => $academicYear,
            'majors'       => $majors,
        ]);
    }

    public function updateMajorConfig(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'majors'             => 'required|array',
            'majors.*.id'        => 'required|exists:majors,id',
            'majors.*.quota'     => 'required|integer|min:1',
            'majors.*.is_active' => 'required|boolean',
        ]);

        $syncData = [];
        foreach ($validated['majors'] as $majorData) {
            $syncData[$majorData['id']] = [
                'quota'     => $majorData['quota'],
                'is_active' => $majorData['is_active'],
            ];
        }

        $academicYear->majors()->sync($syncData);

        return redirect()->back()->with('success', 'Konfigurasi jurusan berhasil diperbarui.');
    }
}
