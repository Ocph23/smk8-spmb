<?php

namespace App\Http\Controllers;

use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentInboxController extends Controller
{
    /**
     * Display inbox
     */
    public function index()
    {
        $student = Auth::guard('student')->user();

        $messages = Inbox::where('student_id', $student->id)
            ->with('sender')
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Inbox::where('student_id', $student->id)
            ->where('is_read', false)
            ->count();

        return Inertia::render('Student/Inbox', [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Display single message
     */
    public function show(Inbox $message)
    {
        $student = Auth::guard('student')->user();

        // Only allow viewing own messages
        if ($message->student_id !== $student->id) {
            abort(403);
        }

        // Mark as read
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        $message->load('sender');

        return Inertia::render('Student/InboxShow', [
            'message' => $message,
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Inbox $message)
    {
        $student = Auth::guard('student')->user();

        if ($message->student_id !== $student->id) {
            abort(403);
        }

        $message->update(['is_read' => true]);

        return back();
    }

    /**
     * Mark all messages as read
     */
    public function markAllAsRead()
    {
        $student = Auth::guard('student')->user();

        Inbox::where('student_id', $student->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }

    /**
     * Delete message
     */
    public function destroy(Inbox $message)
    {
        $student = Auth::guard('student')->user();

        if ($message->student_id !== $student->id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Pesan berhasil dihapus');
    }
}
