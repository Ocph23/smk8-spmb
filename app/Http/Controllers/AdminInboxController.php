<?php

namespace App\Http\Controllers;

use App\Models\Inbox;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminInboxController extends Controller
{
    /**
     * Display admin inbox management page
     */
    public function index(Request $request)
    {
        $query = Inbox::with(['student', 'sender'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $messages = $query->paginate(20)->withQueryString();
        $students = Student::orderBy('full_name')->get();

        return Inertia::render('Admin/Inbox/Index', [
            'messages' => $messages,
            'students' => $students,
            'filters' => [
                'search' => $request->search,
                'student_id' => $request->student_id,
            ],
        ]);
    }

    /**
     * Show compose message form
     */
    public function create()
    {
        $students = Student::orderBy('full_name')->get();

        return Inertia::render('Admin/Inbox/Compose', [
            'students' => $students,
        ]);
    }

    /**
     * Send message to student(s)
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:individual,all,filtered',
            'student_id' => 'nullable|exists:students,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $query = Student::query();

        // Determine recipients based on recipient_type
        if ($validated['recipient_type'] === 'individual') {
            if (!$validated['student_id']) {
                return back()->withErrors(['student_id' => 'Pilih siswa yang akan dikirimi pesan']);
            }
            $query->where('id', $validated['student_id']);
        } elseif ($validated['recipient_type'] === 'filtered') {
            // Apply filters if any
            if ($request->filled('verification_status')) {
                $query->where('verification_status', $request->verification_status);
            }
            if ($request->filled('major_id')) {
                $query->whereHas('majors', function ($q) use ($request) {
                    $q->where('majors.id', $request->major_id);
                });
            }
            if ($request->filled('is_accepted')) {
                $query->where('is_accepted', $request->is_accepted === 'true');
            }
        }
        // For 'all', no additional conditions

        $students = $query->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['recipient_type' => 'Tidak ada siswa yang memenuhi kriteria']);
        }

        $senderId = Auth::id();
        $count = 0;

        foreach ($students as $student) {
            Inbox::create([
                'student_id' => $student->id,
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'is_read' => false,
                'is_system' => false,
                'sender_id' => $senderId,
            ]);
            $count++;
        }

        return back()->with('success', "Pesan berhasil dikirim ke {$count} siswa.");
    }

    /**
     * Display single message from admin perspective
     */
    public function show(Inbox $message)
    {
        $message->load(['student', 'sender']);

        return Inertia::render('Admin/Inbox/Show', [
            'message' => $message,
        ]);
    }

    /**
     * Delete message
     */
    public function destroy(Inbox $message)
    {
        $message->delete();

        return back()->with('success', 'Pesan berhasil dihapus');
    }

    /**
     * Get students by filter (for dynamic dropdown)
     */
    public function getStudents(Request $request)
    {
        $query = Student::with(['majors', 'acceptedMajor'])
            ->orderBy('full_name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                    ->orWhere('registration_number', 'like', "%{$request->search}%")
                    ->orWhere('nik', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        $students = $query->limit(50)->get();

        return response()->json(['students' => $students]);
    }
}
