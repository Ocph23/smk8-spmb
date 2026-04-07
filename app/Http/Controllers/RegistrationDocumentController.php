<?php

namespace App\Http\Controllers;

use App\Models\RegistrationDocument;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegistrationDocumentController extends Controller
{
    public function index()
    {
        $documents = RegistrationDocument::orderBy('order')->get();

        return Inertia::render('Admin/RegistrationDocuments/Index', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'label'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'field_name'     => 'required|string|max:100|regex:/^[a-z_]+$/|unique:registration_documents,field_name',
            'accepted_types' => 'required|string|max:100',
            'max_size'       => 'required|integer|min:100|max:10240',
            'is_required'    => 'boolean',
            'order'          => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $maxOrder = RegistrationDocument::max('order') ?? 0;
        $validated['order'] = $validated['order'] ?? $maxOrder + 1;

        RegistrationDocument::create($validated);

        return back()->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, RegistrationDocument $registrationDocument)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'label'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'field_name'     => 'required|string|max:100|regex:/^[a-z_]+$/|unique:registration_documents,field_name,' . $registrationDocument->id,
            'accepted_types' => 'required|string|max:100',
            'max_size'       => 'required|integer|min:100|max:10240',
            'is_required'    => 'boolean',
            'order'          => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $registrationDocument->update($validated);

        return back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(RegistrationDocument $registrationDocument)
    {
        $registrationDocument->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function toggleActive(RegistrationDocument $registrationDocument)
    {
        $registrationDocument->update(['is_active' => !$registrationDocument->is_active]);

        return back()->with('success', 'Status dokumen diperbarui.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:registration_documents,id',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            RegistrationDocument::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return back()->with('success', 'Urutan dokumen berhasil diperbarui.');
    }

    public function getActiveDocuments()
    {
        return RegistrationDocument::active()->orderBy('order')->get();
    }
}