<?php

namespace App\Http\Controllers;

use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentInboxController extends Controller
{
    private function authStudent()
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            abort(redirect()->route('student.login'));
        }

        return $student;
    }

    public function index()
    {
        $student = $this->authStudent();

        $messages = Inbox::where('student_id', $student->id)
            ->with('sender')
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Inbox::where('student_id', $student->id)
            ->where('is_read', false)
            ->count();

        return Inertia::render('Student/Inbox', [
            'messages'   => $messages,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function show(Inbox $message)
    {
        $student = $this->authStudent();

        if ((int) $message->student_id !== (int) $student->id) {
            abort(403);
        }

        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        $message->load('sender');

        return Inertia::render('Student/InboxShow', [
            'message' => $message,
        ]);
    }

    public function markAsRead(Inbox $message)
    {
        $student = $this->authStudent();

        if ((int) $message->student_id !== (int) $student->id) {
            abort(403);
        }

        $message->update(['is_read' => true]);

        return back();
    }

    public function markAllAsRead()
    {
        $student = $this->authStudent();

        Inbox::where('student_id', $student->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }

    public function destroy(Inbox $message)
    {
        $student = $this->authStudent();

        if ((int) $message->student_id !== (int) $student->id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Pesan berhasil dihapus');
    }
}
