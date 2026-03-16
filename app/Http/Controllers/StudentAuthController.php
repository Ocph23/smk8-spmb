<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentAuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return Inertia::render('Auth/StudentLogin');
    }

    /**
     * Handle student login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = Student::where('email', $credentials['email'])->first();

        if (!$student || !Hash::check($credentials['password'], $student->password)) {
            return back()->withErrors([
                'email' => 'Email atau password salah',
            ])->onlyInput('email');
        }

        // Login using student guard
        Auth::guard('student')->login($student, $request->boolean('remember'));

        $request->session()->regenerate();

        // Debug: Check if auth worked
        // dd(Auth::guard('student')->check(), Auth::guard('student')->user());

        return redirect()->route('student.dashboard');
    }

    /**
     * Show registration form (create account)
     */
    public function showRegister()
    {
        return Inertia::render('Auth/StudentRegister');
    }

    /**
     * Handle student registration (create account with password)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:students,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $student = Student::create([
            'registration_number' => 'DRAFT-' . strtoupper(uniqid()),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'verification_status' => 'pending',
        ]);

        Auth::guard('student')->login($student);

        $request->session()->regenerate();

        // Create welcome inbox message
        Inbox::create([
            'student_id' => $student->id,
            'subject' => 'Selamat Datang di SPMB SMKN 8',
            'message' => "Terima kasih telah membuat akun.\n\n" .
                "Silakan lengkapi data pendaftaran Anda dengan mengisi formulir pendaftaran.\n\n" .
                "Klik menu 'Daftar Sekarang' atau kunjungi halaman pendaftaran untuk memulai.",
            'is_system' => true,
        ]);

        return redirect()->route('student.dashboard');
    }

    /**
     * Student Dashboard
     */
    public function dashboard()
    {
        $student = Auth::guard('student')->user();

        // Get unread inbox count
        $unreadCount = Inbox::where('student_id', $student->id)
            ->where('is_read', false)
            ->count();

        // Get recent inbox messages
        $inboxMessages = Inbox::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Load student majors if exists
        $student->load('majors');

        return Inertia::render('Student/Dashboard', [
            'student' => $student,
            'unreadCount' => $unreadCount,
            'recentInbox' => $inboxMessages,
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
