<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * View all media files in the library with filters and page tabs.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_media'), 403, 'Unauthorized access to media library.');

        $query = Media::where('collection_name', 'cms_media')->orderBy('created_at', 'desc');

        // Filter by page
        if ($request->filled('page_filter') && $request->input('page_filter') !== 'all') {
            $page = $request->input('page_filter');
            $query->where(function ($q) use ($page) {
                $q->whereJsonContains('custom_properties->page', $page)
                  ->orWhere('custom_properties->page', $page);
            });
        }

        // Filter by media type (image / video)
        if ($request->filled('type_filter') && $request->input('type_filter') !== 'all') {
            $type = $request->input('type_filter');
            if ($type === 'video') {
                $query->where(function ($q) {
                    $q->where('mime_type', 'like', 'video/%')
                      ->orWhereJsonContains('custom_properties->media_type', 'video')
                      ->orWhere('custom_properties->media_type', 'video');
                });
            } elseif ($type === 'image') {
                $query->where(function ($q) {
                    $q->where('mime_type', 'like', 'image/%')
                      ->orWhereJsonContains('custom_properties->media_type', 'image')
                      ->orWhere('custom_properties->media_type', 'image');
                });
            }
        }

        // Filter by search query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('custom_properties->caption', 'like', "%{$search}%")
                  ->orWhere('custom_properties->title', 'like', "%{$search}%")
                  ->orWhere('custom_properties->alt_text', 'like', "%{$search}%");
            });
        }

        $mediaItems = $query->paginate(24)->withQueryString();

        $mediaData = $mediaItems->map(function ($item) {
            $mime = $item->mime_type ?? '';
            $isVideo = Str::startsWith($mime, 'video/') || in_array(strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION)), ['mp4', 'webm', 'mov', 'ogg', 'm4v', 'mkv']);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'title' => $item->getCustomProperty('title', $item->name),
                'filename' => $item->file_name,
                'path' => $item->getUrl(),
                'mime_type' => $item->mime_type,
                'media_type' => $isVideo ? 'video' : 'image',
                'file_size' => $item->size,
                'page' => $item->getCustomProperty('page', 'general'),
                'section' => $item->getCustomProperty('section', 'general'),
                'category' => $item->getCustomProperty('category', 'general'),
                'is_hero' => (bool) $item->getCustomProperty('is_hero', false),
                'alt_text' => $item->getCustomProperty('alt_text', ''),
                'caption' => $item->getCustomProperty('caption', ''),
                'created_at' => $item->created_at ? $item->created_at->toISOString() : null,
                'created_at_human' => $item->created_at ? $item->created_at->diffForHumans() : '',
            ];
        });

        // Paginated wrapper
        $paginated = $mediaItems->toArray();
        $paginated['data'] = $mediaData;

        // Count totals for badges
        $totalAll = Media::where('collection_name', 'cms_media')->count();
        $totalVideos = Media::where('collection_name', 'cms_media')->where(function($q) {
            $q->where('mime_type', 'like', 'video/%')
              ->orWhereJsonContains('custom_properties->media_type', 'video');
        })->count();

        return Inertia::render('Admin/Media/Index', [
            'media' => $paginated,
            'filters' => [
                'page_filter' => $request->input('page_filter', 'all'),
                'type_filter' => $request->input('type_filter', 'all'),
                'search' => $request->input('search', ''),
            ],
            'counts' => [
                'total_all' => $totalAll,
                'total_videos' => $totalVideos,
            ]
        ]);
    }

    /**
     * Upload new media asset (Images or Videos).
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_media'), 403, 'Unauthorized to upload media.');

        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,webp,gif,svg,mp4,webm,mov,ogg,m4v,mkv|max:153600', // Max 150MB
            'title' => 'nullable|string|max:255',
            'page' => 'nullable|string|in:home,gallery,farm,villas,experiences,about,location,general',
            'section' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'is_hero' => 'nullable|boolean',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $user = auth()->user();
        $mime = $file->getMimeType() ?? '';
        $ext = strtolower($file->getClientOriginalExtension());
        $isVideo = Str::startsWith($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'ogg', 'm4v', 'mkv']);
        $mediaType = $isVideo ? 'video' : 'image';

        $page = $request->input('page', 'gallery');
        $isHero = filter_var($request->input('is_hero', false), FILTER_VALIDATE_BOOLEAN);

        // Upload through Spatie Media Library (Auto-convert images to WebP for maximum speed)
        $customProps = [
            'title' => $request->input('title', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'page' => $page,
            'section' => $request->input('section', 'general'),
            'category' => $request->input('category', 'general'),
            'is_hero' => $isHero,
            'media_type' => $mediaType,
            'alt_text' => $request->input('alt_text', ''),
            'caption' => $request->input('caption', ''),
        ];

        if (!$isVideo && function_exists('imagewebp') && in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif'])) {
            $webpPath = $this->convertToWebp($file);
            if ($webpPath && file_exists($webpPath)) {
                $cleanBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $media = $user->addMedia($webpPath)
                    ->usingFileName($cleanBaseName . '.webp')
                    ->withCustomProperties($customProps)
                    ->toMediaCollection('cms_media');
                @unlink($webpPath);
            } else {
                $media = $user->addMedia($file)
                    ->withCustomProperties($customProps)
                    ->toMediaCollection('cms_media');
            }
        } else {
            $media = $user->addMedia($file)
                ->withCustomProperties($customProps)
                ->toMediaCollection('cms_media');
        }

        // Audit Log
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'media_uploaded',
            'entity_type' => 'Media',
            'entity_id' => $media->id,
            'new_values' => [
                'filename' => $media->file_name,
                'media_type' => $mediaType,
                'page' => $page,
                'is_hero' => $isHero,
                'format' => $media->mime_type,
            ],
            'created_at' => Carbon::now(),
        ]);

        $formatMsg = (!$isVideo && $media->mime_type === 'image/webp') ? ' (Auto-converted to optimized WebP for lightning-fast speed)' : '';

        return redirect()->route('admin.media.index')
            ->with('success', ucfirst($mediaType) . ' uploaded successfully for ' . ucfirst($page) . ' page' . $formatMsg . '.');
    }

    /**
     * Helper to convert an uploaded image to optimized WebP format.
     */
    protected function convertToWebp($uploadedFile): ?string
    {
        try {
            $imageContent = file_get_contents($uploadedFile->getRealPath());
            if (!$imageContent) return null;

            $image = @imagecreatefromstring($imageContent);
            if (!$image) return null;

            // Preserve alpha channel / transparency for PNG/GIF
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $tempDir = storage_path('app/temp_webp');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $cleanName = Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
            $tempPath = $tempDir . '/' . $cleanName . '_' . uniqid() . '.webp';

            // Convert with 85% quality: crystal-clear clarity with 70-80% smaller size
            if (imagewebp($image, $tempPath, 85)) {
                imagedestroy($image);
                return $tempPath;
            }

            imagedestroy($image);
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update media asset properties (title, caption, page, section, category, is_hero).
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_media'), 403, 'Unauthorized to update media.');

        $media = Media::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'page' => 'nullable|string|in:home,gallery,farm,villas,experiences,about,location,general',
            'section' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'is_hero' => 'nullable|boolean',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        $customProperties = $media->custom_properties ?? [];
        
        $customProperties['title'] = $validated['title'] ?? ($customProperties['title'] ?? $media->name);
        $customProperties['page'] = $validated['page'] ?? ($customProperties['page'] ?? 'general');
        $customProperties['section'] = $validated['section'] ?? ($customProperties['section'] ?? 'general');
        $customProperties['category'] = $validated['category'] ?? ($customProperties['category'] ?? 'general');
        $customProperties['is_hero'] = filter_var($request->input('is_hero', false), FILTER_VALIDATE_BOOLEAN);
        $customProperties['alt_text'] = $validated['alt_text'] ?? '';
        $customProperties['caption'] = $validated['caption'] ?? '';

        $media->custom_properties = $customProperties;
        $media->save();

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'media_updated',
            'entity_type' => 'Media',
            'entity_id' => $media->id,
            'new_values' => $customProperties,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Media details updated successfully.');
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
