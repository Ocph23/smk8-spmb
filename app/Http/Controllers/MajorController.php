<?php

namespace App\Http\Controllers;

use App\Models\Major;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::withCount(['acceptedStudents as accepted_count' => function ($query) {
            $query->where('is_accepted', true);
        }])->withCount(['students as students_count'])->orderBy('code')->get();

        return Inertia::render('Admin/Majors/Index', [
            'majors' => $majors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:majors,code',
            'description' => 'nullable|string',
            'quota' => 'required|integer|min:1|max:100',
            'icon_svg' => 'nullable|string',
        ]);

        Major::create($validated);

        return back()->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Major $major)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:majors,code,' . $major->id,
            'description' => 'nullable|string',
            'quota' => 'required|integer|min:1|max:100',
            'icon_svg' => 'nullable|string',
        ]);

        $major->update($validated);

        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major)
    {
        // Check if major has students
        if ($major->students()->count() > 0) {
            return back()->withErrors(['error' => 'Jurusan tidak dapat dihapus karena sudah memiliki pendaftar.']);
        }

        $major->delete();

        return back()->with('success', 'Jurusan berhasil dihapus.');
    }
}
