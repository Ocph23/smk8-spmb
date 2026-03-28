<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentTemplateController extends Controller
{
    // -------------------------------------------------------------------------
    // Admin
    // -------------------------------------------------------------------------

    public function index()
    {
        $documents = DocumentTemplate::with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'file'        => 'required|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
        ]);

        $file     = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('document-templates', 'public');
        $fileSize = $this->formatFileSize($file->getSize());

        DocumentTemplate::create([
            'name'        => $request->name,
            'description' => $request->description,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
            'is_active'   => true,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function update(Request $request, DocumentTemplate $document)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'file'        => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'is_active'   => 'boolean',
        ]);

        $data = [
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', $document->is_active),
        ];

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($document->file_path);
            $file           = $request->file('file');
            $data['file_path'] = $file->store('document-templates', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $this->formatFileSize($file->getSize());
        }

        $document->update($data);

        return back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(DocumentTemplate $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function toggleActive(DocumentTemplate $document)
    {
        $document->update(['is_active' => !$document->is_active]);

        return back()->with('success', 'Status dokumen diperbarui.');
    }

    // -------------------------------------------------------------------------
    // Public / Student download
    // -------------------------------------------------------------------------

    public function publicIndex()
    {
        $documents = DocumentTemplate::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function download(DocumentTemplate $document)
    {
        if (!$document->is_active) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
