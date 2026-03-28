<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\Major;
use App\Models\Schedule;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function __construct(
        protected AcademicYearService $service
    ) {}

    public function index()
    {
        $activeYear = $this->service->getActive();

        if (!$activeYear) {
            $schedules = collect();
        } else {
            $schedules = Schedule::where('academic_year_id', $activeYear->id)
                ->orderBy('start_date')
                ->get();
        }

        return Inertia::render('Home', [
            'schedules' => $schedules,
            'majors'    => Major::all(),
            'documents' => DocumentTemplate::where('is_active', true)->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function adminIndex()
    {
        $context = $this->service->resolveContext(request());

        $query = Schedule::orderBy('start_date', 'desc');
        if ($context) {
            $query->where('academic_year_id', $context->id);
        }

        $schedules = $query->get();

        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => $schedules,
            'currentAcademicYear' => $context,
        ]);
    }

    public function store(Request $request)
    {
        $context = $this->service->resolveContext($request);

        if (!$context) {
            return back()->withErrors(['error' => 'Tidak ada konteks tahun ajaran aktif. Pilih atau aktifkan tahun ajaran terlebih dahulu.']);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,completed',
        ]);

        $validated['academic_year_id'] = $context->id;

        Schedule::create($validated);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,completed',
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
