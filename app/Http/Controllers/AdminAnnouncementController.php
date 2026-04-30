<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::query()
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Announcement::create($validated);

        return back()->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate($this->rules());

        $announcement->update($validated);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'required|in:important,info,schedule,result',
            'content' => 'required|string',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:500',
            'is_pinned' => 'boolean',
            'is_active' => 'boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:publish_at',
        ];
    }
}
