<?php

namespace App\Http\Controllers;

use App\Mail\StudentPasswordResetMail;
use App\Mail\StudentWelcomeMail;
use App\Models\DocumentTemplate;
use App\Models\Inbox;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/StudentLogin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email   = strtolower(trim($credentials['email']));
        $student = Student::where('email', $email)->first();

        if (!$student || !Hash::check($credentials['password'], $student->password)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        Auth::guard('student')->login($student, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('student.dashboard');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/StudentRegister');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email|unique:students,email',
            'phone'    => ['required', 'string', 'max:20', 'regex:/^08[0-9]{8,}$/'],
            'password' => 'required|min:6|confirmed',
        ]);

        $plainPassword = $validated['password'];

        $student = Student::create([
            'registration_number' => 'DRAFT-' . strtoupper(uniqid()),
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'password'            => Hash::make($plainPassword),
            'verification_status' => 'pending',
        ]);

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        Inbox::create([
            'student_id' => $student->id,
            'subject'    => 'Selamat Datang — Segera Lengkapi Pendaftaran',
            'message'    => "Akun Anda berhasil dibuat.\n\n" .
                "Silakan lengkapi data pendaftaran Anda dengan mengisi formulir pendaftaran.\n\n" .
                "Klik menu 'Lengkapi Pendaftaran' di dashboard untuk memulai.",
            'is_system'  => true,
        ]);

        try {
            Mail::to($student->email)->send(new StudentWelcomeMail($student, $plainPassword));
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return redirect()->route('student.dashboard');
    }

    // -------------------------------------------------------------------------
    // Forgot / Reset Password
    // -------------------------------------------------------------------------

    public function showForgotPassword()
    {
        return Inertia::render('Auth/StudentForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $student = Student::where('email', $request->email)->first();

        if (!$student) {
            return back()->with('status', 'Jika email terdaftar, link reset password telah dikirim.');
        }

        DB::table('student_password_resets')->where('email', $request->email)->delete();

        $token = Str::random(64);

        DB::table('student_password_resets')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        try {
            Mail::to($request->email)->send(new StudentPasswordResetMail($request->email, $token));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        return back()->with('status', 'Link reset password telah dikirim ke email Anda.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return Inertia::render('Auth/StudentResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('student_password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah kadaluarsa.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('student_password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Link reset password sudah kadaluarsa. Silakan minta ulang.']);
        }

        $student = Student::where('email', $request->email)->first();

        if (!$student) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $student->update(['password' => Hash::make($request->password)]);
        DB::table('student_password_resets')->where('email', $request->email)->delete();

        return redirect()->route('student.login')
            ->with('status', 'Password berhasil diubah. Silakan login.');
    }

    // -------------------------------------------------------------------------
    // Dashboard & Logout
    // -------------------------------------------------------------------------

    public function dashboard()
    {
        $student = Auth::guard('student')->user();

        $unreadCount = Inbox::where('student_id', $student->id)
            ->where('is_read', false)
            ->count();

        $inboxMessages = Inbox::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $student->load('majors');

        $documents = DocumentTemplate::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Student/Dashboard', [
            'student'     => $student,
            'unreadCount' => $unreadCount,
            'recentInbox' => $inboxMessages,
            'documents'   => $documents,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
