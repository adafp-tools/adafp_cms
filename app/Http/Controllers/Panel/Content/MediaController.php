<?php

namespace App\Http\Controllers\Panel\Content;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(Request $request)
    {
        $items = Media::latest()->paginate(36);

        return view('panel.content.media.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . (config('cms.media_max_size_mb', 10) * 1024),
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
            ],
        ]);

        try {
            $media = $this->media->store($request->file('file'), $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'id'  => $media->id,
                'url' => $media->thumbnailUrl(),
            ]);
        }

        return back()->with('success', __('panel.media_uploaded'));
    }

    public function destroy(Media $medium)
    {
        $this->media->delete($medium);

        return back()->with('success', __('panel.media_deleted'));
    }
}
