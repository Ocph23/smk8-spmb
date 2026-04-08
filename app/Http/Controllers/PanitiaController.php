<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PanitiaController extends Controller
{
    public function index()
    {
        $panitiaList = User::where('role', 'panitia')->get();

        return Inertia::render('Admin/Panitia/Index', [
            'panitiaList' => $panitiaList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'panitia',
        ]);

        return redirect()->back()->with('success', 'Akun panitia berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'panitia') {
            abort(404);
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Akun panitia berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'panitia') {
            abort(404);
        }

        $user->delete();

        return redirect()->back()->with('success', 'Akun panitia berhasil dihapus.');
    }
}
