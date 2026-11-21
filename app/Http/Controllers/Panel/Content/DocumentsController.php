<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\DocumentCategory;
use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsController extends Controller
{
    public function index()
    {
        $documents = Document::with('translations')->latest()->paginate(30);

        return view('panel.content.documents.index', compact('documents'));
    }

    public function create()
    {
        $locales    = config('cms.locales', ['en']);
        $categories = DocumentCategory::cases();

        return view('panel.content.documents.form', compact('locales', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateDocument($request);

        $file     = $request->file('file');
        $dir      = 'documents/' . now()->format('Y/m');
        $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());

        $file->storeAs($dir, $filename);

        $document = Document::create([
            'file'         => $dir . '/' . $filename,
            'file_size'    => $file->getSize(),
            'mime_type'    => $file->getMimeType(),
            'category'     => $data['category'],
            'published_at' => $data['published_at'] ?? null,
            'created_by'   => $request->user()->id,
            'updated_by'   => $request->user()->id,
        ]);

        $this->saveTranslations($document, $data['translations']);

        return redirect()->route('panel.documents.index')->with('success', __('panel.document_created'));
    }

    public function edit(Document $document)
    {
        $document->load('translations');
        $locales    = config('cms.locales', ['en']);
        $categories = DocumentCategory::cases();

        return view('panel.content.documents.form', compact('document', 'locales', 'categories'));
    }

    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'category'     => 'required|in:' . implode(',', array_column(DocumentCategory::cases(), 'value')),
            'published_at' => 'nullable|date',
            'translations' => 'array',
            'translations.*.title'       => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
        ]);

        $document->update([
            'category'     => $data['category'],
            'published_at' => $data['published_at'] ?? null,
            'updated_by'   => $request->user()->id,
        ]);

        $this->saveTranslations($document, $data['translations']);

        return redirect()->route('panel.documents.index')->with('success', __('panel.document_updated'));
    }

    public function destroy(Document $document)
    {
        Storage::delete($document->file);
        $document->delete();

        return back()->with('success', __('panel.document_deleted'));
    }

    private function validateDocument(Request $request): array
    {
        return $request->validate([
            'file'         => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:51200',
            'category'     => 'required|in:' . implode(',', array_column(DocumentCategory::cases(), 'value')),
            'published_at' => 'nullable|date',
            'translations' => 'array',
            'translations.*.title'       => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
        ]);
    }

    private function saveTranslations(Document $document, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title'])) {
                continue;
            }

            $document->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'         => $data['title'],
                    'description'   => $data['description'] ?? null,
                    'translated_by' => 'human',
                    'reviewed'      => true,
                ]
            );
        }
    }
}
