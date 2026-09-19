<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
                'target_villa' => $item->getCustomProperty('target_villa', ''),
                'target_experience' => $item->getCustomProperty('target_experience', ''),
                'target_role' => $item->getCustomProperty('target_role', 'general'),
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

        // Pass live villa models and experiences for dropdowns
        $villas = \App\Models\AccommodationType::select('id', 'name', 'slug', 'featured_image')->orderBy('name')->get();
        $experiences = \App\Models\FarmTour::select('id', 'title', 'slug', 'featured_image')->orderBy('title')->get();

        return Inertia::render('Admin/Media/Index', [
            'media' => $paginated,
            'villas_list' => $villas,
            'experiences_list' => $experiences,
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

        @set_time_limit(600);
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '1024M');

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:524288', // Max 512MB
                function ($attribute, $value, $fail) {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'mp4', 'webm', 'mov', 'ogg', 'm4v', 'mkv', 'avi', '3gp', 'qt'];
                    $ext = strtolower($value->getClientOriginalExtension());
                    $mime = strtolower($value->getMimeType() ?? '');
                    $isAllowedExt = in_array($ext, $allowedExtensions);
                    $isAllowedMime = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/') || $mime === 'application/octet-stream';
                    
                    if (!$isAllowedExt && !$isAllowedMime) {
                        $fail('The uploaded file must be a valid image (JPG, PNG, WEBP, GIF, SVG) or video (MP4, WEBM, MOV, M4V, MKV).');
                    }
                }
            ],
            'title' => 'nullable|string|max:255',
            'page' => 'nullable|string|in:home,gallery,farm,villas,experiences,about,products,location,general',
            'section' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'target_villa' => 'nullable|string|max:100',
            'target_experience' => 'nullable|string|max:100',
            'target_role' => 'nullable|string|max:100',
            'is_hero' => 'nullable|boolean',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $request->file('file');
            $user = auth()->user();
            $mime = $file->getMimeType() ?? '';
            $ext = strtolower($file->getClientOriginalExtension());
            $isVideo = Str::startsWith($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'ogg', 'm4v', 'mkv', 'avi', '3gp', 'qt']);
            $mediaType = $isVideo ? 'video' : 'image';

            $page = $request->input('page', 'gallery');
            $section = $request->input('section', 'general');
            $category = $request->input('category', 'general');
            $targetVilla = $request->input('target_villa');
            $targetExperience = $request->input('target_experience');
            $targetRole = $request->input('target_role', 'general');
            $isHero = filter_var($request->input('is_hero', false), FILTER_VALIDATE_BOOLEAN);

            // Upload through Spatie Media Library (Auto-convert images to WebP for maximum speed)
            $customProps = [
                'title' => $request->input('title', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'page' => $page,
                'section' => $section,
                'category' => $category,
                'target_villa' => $targetVilla,
                'target_experience' => $targetExperience,
                'target_role' => $targetRole,
                'is_hero' => $isHero,
                'media_type' => $mediaType,
                'alt_text' => $request->input('alt_text', ''),
                'caption' => $request->input('caption', ''),
            ];

            $formatMsg = '';

            if (!$isVideo && function_exists('imagewebp') && in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif'])) {
                $webpPath = $this->convertToWebp($file);
                if ($webpPath && file_exists($webpPath)) {
                    $cleanBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                    $media = $user->addMedia($webpPath)
                        ->usingFileName($cleanBaseName . '.webp')
                        ->withCustomProperties($customProps)
                        ->toMediaCollection('cms_media');
                    @unlink($webpPath);
                    $formatMsg = ' (Optimized to WebP)';
                } else {
                    $media = $user->addMedia($file)
                        ->withCustomProperties($customProps)
                        ->toMediaCollection('cms_media');
                }
            } elseif ($isVideo) {
                // Attempt high-definition FastStart Web Video optimization
                $optVideoPath = $this->optimizeWebVideo($file);
                if ($optVideoPath && file_exists($optVideoPath)) {
                    $cleanBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                    $media = $user->addMedia($optVideoPath)
                        ->usingFileName($cleanBaseName . '.mp4')
                        ->withCustomProperties($customProps)
                        ->toMediaCollection('cms_media');
                    @unlink($optVideoPath);
                    $formatMsg = ' (FastStart Web Video Optimized)';
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

            $mediaUrl = $media->getUrl();

            // Direct Model Publishing:
            // 1. If assigned to a specific Villa
            if ($page === 'villas' && !empty($targetVilla)) {
                $villa = \App\Models\AccommodationType::where('id', $targetVilla)
                    ->orWhere('slug', $targetVilla)
                    ->first();
                if ($villa) {
                    if ($targetRole === 'featured_cover' || empty($targetRole) || $targetRole === 'general') {
                        $villa->update(['featured_image' => $mediaUrl]);
                    } elseif ($targetRole === 'gallery_item') {
                        $currentGallery = is_array($villa->gallery_images) ? $villa->gallery_images : [];
                        $currentGallery[] = $mediaUrl;
                        $villa->update(['gallery_images' => array_values(array_unique($currentGallery))]);
                    }
                }
            }

            // 2. If assigned to a specific Experience / Tour
            if ($page === 'experiences' && !empty($targetExperience)) {
                $exp = \App\Models\FarmTour::where('id', $targetExperience)
                    ->orWhere('slug', $targetExperience)
                    ->first();
                if ($exp) {
                    if ($targetRole === 'featured_cover' || empty($targetRole) || $targetRole === 'general') {
                        $exp->update(['featured_image' => $mediaUrl]);
                    }
                }
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'media_uploaded',
                'entity_type' => 'Media',
                'entity_id' => $media->id,
                'new_values' => [
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'url' => $mediaUrl,
                    'page' => $page,
                    'section' => $section,
                    'category' => $category,
                    'target_villa' => $targetVilla,
                    'target_experience' => $targetExperience,
                    'target_role' => $targetRole,
                ],
                'created_at' => Carbon::now(),
            ]);

            Cache::flush();

            return back()->with('success', "Media asset '{$media->name}' published successfully{$formatMsg}.");
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper to optimize uploaded video for web streaming (H.264, FastStart, CRF 22, max 1080p).
     */
    protected function optimizeWebVideo($uploadedFile): ?string
    {
        try {
            $ffmpeg = $this->findFfmpegBinary();
            if (!$ffmpeg) {
                return null;
            }

            $tempDir = storage_path('app/temp_video');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $cleanName = Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
            $tempPath = $tempDir . '/' . $cleanName . '_' . uniqid() . '.mp4';
            $inputPath = $uploadedFile->getRealPath();

            // Run FFmpeg web optimization (H.264 + FastStart + Max 1080p + 128k AAC audio)
            $cmd = sprintf(
                '%s -y -i %s -c:v libx264 -profile:v high -level 4.1 -preset medium -crf 22 -vf "scale=\'min(1920,iw)\':-2" -c:a aac -b:a 128k -movflags +faststart %s 2>&1',
                escapeshellcmd($ffmpeg),
                escapeshellarg($inputPath),
                escapeshellarg($tempPath)
            );

            @exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                return $tempPath;
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Video optimization notice: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find FFmpeg binary in system path or standard locations.
     */
    protected function findFfmpegBinary(): ?string
    {
        $candidates = [
            config('media-library.ffmpeg_path'),
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg',
            'ffmpeg',
        ];

        foreach ($candidates as $bin) {
            if (!$bin) continue;
            $test = @shell_exec(escapeshellcmd($bin) . ' -version 2>&1');
            if ($test && stripos($test, 'ffmpeg version') !== false) {
                return $bin;
            }
        }

        return null;
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
            'page' => 'nullable|string|in:home,gallery,farm,villas,experiences,about,products,location,general',
            'section' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'target_villa' => 'nullable|string|max:100',
            'target_experience' => 'nullable|string|max:100',
            'target_role' => 'nullable|string|max:100',
            'is_hero' => 'nullable|boolean',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        $customProperties = $media->custom_properties ?? [];
        
        $customProperties['title'] = $validated['title'] ?? ($customProperties['title'] ?? $media->name);
        $customProperties['page'] = $validated['page'] ?? ($customProperties['page'] ?? 'general');
        $customProperties['section'] = $validated['section'] ?? ($customProperties['section'] ?? 'general');
        $customProperties['category'] = $validated['category'] ?? ($customProperties['category'] ?? 'general');
        $customProperties['target_villa'] = $validated['target_villa'] ?? ($customProperties['target_villa'] ?? '');
        $customProperties['target_experience'] = $validated['target_experience'] ?? ($customProperties['target_experience'] ?? '');
        $customProperties['target_role'] = $validated['target_role'] ?? ($customProperties['target_role'] ?? 'general');
        $customProperties['is_hero'] = filter_var($request->input('is_hero', false), FILTER_VALIDATE_BOOLEAN);
        $customProperties['alt_text'] = $validated['alt_text'] ?? '';
        $customProperties['caption'] = $validated['caption'] ?? '';

        $media->custom_properties = $customProperties;
        $media->save();

        $mediaUrl = $media->getUrl();
        $targetVilla = $customProperties['target_villa'] ?? '';
        $targetRole = $customProperties['target_role'] ?? '';
        $targetExperience = $customProperties['target_experience'] ?? '';
        $page = $customProperties['page'] ?? '';

        // If assigned as featured to a Villa
        if ($page === 'villas' && !empty($targetVilla)) {
            $villa = \App\Models\AccommodationType::where('id', $targetVilla)
                ->orWhere('slug', $targetVilla)
                ->first();
            if ($villa && ($targetRole === 'featured_cover' || empty($targetRole) || $targetRole === 'general')) {
                $villa->update(['featured_image' => $mediaUrl]);
            }
        }

        // If assigned as featured to an Experience
        if ($page === 'experiences' && !empty($targetExperience)) {
            $exp = \App\Models\FarmTour::where('id', $targetExperience)
                ->orWhere('slug', $targetExperience)
                ->first();
            if ($exp && ($targetRole === 'featured_cover' || empty($targetRole) || $targetRole === 'general')) {
                $exp->update(['featured_image' => $mediaUrl]);
            }
        }

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'media_updated',
            'entity_type' => 'Media',
            'entity_id' => $media->id,
            'new_values' => $customProperties,
            'created_at' => Carbon::now(),
        ]);

        Cache::flush();

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

        Cache::flush();

        return redirect()->route('admin.media.index')
            ->with('success', 'Media asset deleted successfully.');
    }
}
