<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentWave;
use App\Services\AcademicYearService;
use App\Services\EnrollmentWaveService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentWaveController extends Controller
{
    public function __construct(
        private EnrollmentWaveService $service,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(Request $request)
    {
        $year = $this->academicYearService->resolveContext($request);

        $waves = $year
            ? EnrollmentWave::where('academic_year_id', $year->id)
                ->withCount('students')
                ->with('majors')
                ->orderBy('wave_number')
                ->get()
                ->map(function ($wave) {
                    $wave->accepted_count = $wave->students()->where('is_accepted', true)->count();
                    return $wave;
                })
            : collect();

        return Inertia::render('Admin/EnrollmentWaves/Index', [
            'waves'       => $waves,
            'academicYear' => $year,
        ]);
    }

    public function store(Request $request)
    {
        $year = $this->academicYearService->resolveContext($request);

        if (! $year) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran yang dipilih.']);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'open_date'   => 'nullable|date',
            'close_date'  => 'nullable|date|after_or_equal:open_date',
            'description' => 'nullable|string',
        ]);

        try {
            $this->service->createWave($year, $validated);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Gelombang berhasil dibuat.');
    }

    public function show(Request $request, EnrollmentWave $enrollmentWave)
    {
        $enrollmentWave->load(['academicYear', 'majors', 'students']);

        $quotaStats = $enrollmentWave->majors->map(function ($major) use ($enrollmentWave) {
            $quota    = (int) $major->pivot->quota;
            $accepted = $this->service->getAcceptedCountInWave($enrollmentWave, $major->id);

            return [
                'major_id'   => $major->id,
                'major_name' => $major->name,
                'quota'      => $quota,
                'accepted'   => $accepted,
                'remaining'  => max(0, $quota - $accepted),
            ];
        });

        return Inertia::render('Admin/EnrollmentWaves/Show', [
            'wave'       => $enrollmentWave,
            'quotaStats' => $quotaStats,
        ]);
    }

    public function update(Request $request, EnrollmentWave $enrollmentWave)
    {
        if (! $enrollmentWave->isDraft()) {
            return back()->withErrors(['error' => 'Hanya gelombang berstatus draft yang dapat diedit.']);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'open_date'   => 'nullable|date',
            'close_date'  => 'nullable|date|after_or_equal:open_date',
            'description' => 'nullable|string',
        ]);

        $enrollmentWave->update($validated);

        return redirect()->back()->with('success', 'Gelombang berhasil diperbarui.');
    }

    public function destroy(EnrollmentWave $enrollmentWave)
    {
        try {
            $this->service->deleteWave($enrollmentWave);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.enrollment-waves.index')
            ->with('success', 'Gelombang berhasil dihapus.');
    }

    public function open(EnrollmentWave $enrollmentWave)
    {
        try {
            $this->service->openWave($enrollmentWave);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Gelombang berhasil dibuka.');
    }

    public function close(EnrollmentWave $enrollmentWave)
    {
        try {
            $this->service->closeWave($enrollmentWave);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Gelombang berhasil ditutup.');
    }

    public function announce(EnrollmentWave $enrollmentWave)
    {
        try {
            $this->service->announceWave($enrollmentWave);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Hasil gelombang berhasil diumumkan.');
    }

    public function updateQuotas(Request $request, EnrollmentWave $enrollmentWave)
    {
        $validated = $request->validate([
            'quotas'          => 'required|array',
            'quotas.*.major_id' => 'required|exists:majors,id',
            'quotas.*.quota'    => 'required|integer|min:0',
        ]);

        $quotas = collect($validated['quotas'])->pluck('quota', 'major_id')->all();

        try {
            $this->service->updateQuotas($enrollmentWave, $quotas);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Kuota berhasil diperbarui.');
    }
}
