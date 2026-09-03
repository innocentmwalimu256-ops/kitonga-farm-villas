<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MediaController extends Controller
{
    /**
     * View all media files in the library.
     */
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_media'), 403, 'Unauthorized access to media library.');

        $mediaItems = Media::orderBy('created_at', 'desc')->paginate(24);

        $mediaData = $mediaItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'filename' => $item->file_name,
                'path' => $item->getUrl(),
                'mime_type' => $item->mime_type,
                'file_size' => $item->size,
                'alt_text' => $item->getCustomProperty('alt_text', ''),
                'caption' => $item->getCustomProperty('caption', ''),
                'created_at' => $item->created_at->toISOString(),
            ];
        });

        // Paginated wrapper
        $paginated = $mediaItems->toArray();
        $paginated['data'] = $mediaData;

        return Inertia::render('Admin/Media/Index', [
            'media' => $paginated,
        ]);
    }

    /**
     * Upload new media asset.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_media'), 403, 'Unauthorized to upload media.');

        $request->validate([
            'file' => 'required|file|image|max:10240', // Max 10MB images
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $user = auth()->user();

        // Upload through Spatie Media Library
        $media = $user->addMedia($file)
            ->withCustomProperties([
                'alt_text' => $request->input('alt_text', ''),
                'caption' => $request->input('caption', '')
            ])
            ->toMediaCollection('cms_media');

        // Audit Log
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'media_uploaded',
            'entity_type' => 'Media',
            'entity_id' => $media->id,
            'new_values' => $media->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media uploaded successfully.');
    }

    /**
     * Delete media asset.
     */
    public function destroy($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_media'), 403, 'Unauthorized to delete media.');

        $media = Media::findOrFail($id);
        $oldValues = $media->toArray();

        // Spatie deletes physical files from disk automatically on delete
        $media->delete();

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'media_deleted',
            'entity_type' => 'Media',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media asset deleted successfully.');
    }
}
