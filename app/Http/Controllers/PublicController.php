<?php

namespace App\Http\Controllers;

use App\Models\AccommodationType;
use App\Models\FarmTour;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class PublicController extends Controller
{
    /**
     * Helper to get CMS content for a page with cache.
     */
    protected function getCmsContent(string $pageSlug): array
    {
        $isPreview = request()->query('preview') === 'true' && auth()->check();
        if ($isPreview) {
            $page = CmsPage::where('slug', $pageSlug)->with('sections')->first();
            $content = [];
            if ($page) {
                foreach ($page->sections as $sec) {
                    $val = $sec->value;
                    $metadata = is_array($sec->metadata) ? $sec->metadata : json_decode($sec->metadata ?? '{}', true);
                    if (isset($metadata['draft_value'])) {
                        $val = $metadata['draft_value'];
                    }
                    $content[$sec->key] = $val;
                }
            }
            return $content;
        }

        return Cache::remember("cms_page_content_{$pageSlug}", 600, function () use ($pageSlug) {
            $page = CmsPage::where('slug', $pageSlug)->with('sections')->first();
            $content = [];
            if ($page) {
                foreach ($page->sections as $sec) {
                    $content[$sec->key] = $sec->value;
                }
            }
            return $content;
        });
    }

    /**
     * Helper to get uploaded CMS media assets for a given page with cache.
     */
    protected function getPageMedia(?string $pageSlug = null): array
    {
        $cacheKey = "cms_page_media_" . ($pageSlug ?? 'all');

        return Cache::remember($cacheKey, 600, function () use ($pageSlug) {
            try {
                $query = Media::where('collection_name', 'cms_media')->orderBy('created_at', 'desc');

                if ($pageSlug && $pageSlug !== 'all') {
                    $query->where(function ($q) use ($pageSlug) {
                        $q->whereJsonContains('custom_properties->page', $pageSlug)
                          ->orWhere('custom_properties->page', $pageSlug)
                          ->orWhereJsonContains('custom_properties->page', 'all')
                          ->orWhere('custom_properties->page', 'general');
                    });
                }

                return $query->get()->map(function ($item) {
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
                        'page' => $item->getCustomProperty('page', 'general'),
                        'section' => $item->getCustomProperty('section', 'general'),
                        'category' => $item->getCustomProperty('category', 'general'),
                        'is_hero' => (bool) $item->getCustomProperty('is_hero', false),
                        'alt_text' => $item->getCustomProperty('alt_text', ''),
                        'caption' => $item->getCustomProperty('caption', ''),
                    ];
                })->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function home()
    {
        $data = Cache::remember('public_home_payload', 600, function () {
            try {
                $villas = AccommodationType::where('active', true)->orderBy('sort_order')->get();
                $experiences = FarmTour::where('active', true)->get();
                $products = Product::where('active', true)->take(6)->get();
                $cms = $this->getCmsContent('home');
                $homeMedia = $this->getPageMedia('home');

                $heroVideoUrl = null;
                $heroVideoMime = null;

                try {
                    $heroVideoMedia = Media::where('collection_name', 'cms_media')
                        ->where(function ($q) {
                            $q->where('custom_properties->section', 'hero_video')
                              ->orWhereJsonContains('custom_properties->section', 'hero_video')
                              ->orWhere(function ($sq) {
                                  $sq->where(function ($ssq) {
                                      $ssq->where('custom_properties->page', 'home')
                                          ->orWhereJsonContains('custom_properties->page', 'home');
                                  })->where(function ($ssq2) {
                                      $ssq2->where('custom_properties->is_hero', true)
                                           ->orWhere('custom_properties->is_hero', '1')
                                           ->orWhereJsonContains('custom_properties->is_hero', true);
                                  });
                              })
                              ->orWhere('custom_properties->page', 'home')
                              ->orWhereJsonContains('custom_properties->page', 'home');
                        })
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->first(function ($item) {
                            $mime = strtolower($item->mime_type ?? '');
                            $ext = strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION));
                            $customProps = $item->custom_properties ?? [];
                            $mediaType = $customProps['media_type'] ?? '';
                            return $mediaType === 'video' || Str::startsWith($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'mkv', 'avi', '3gp', 'qt', 'ogg']);
                        });

                    if ($heroVideoMedia) {
                        $heroVideoUrl = $heroVideoMedia->getUrl();
                        $heroVideoMime = $heroVideoMedia->mime_type ?? 'video/mp4';
                    } elseif (!empty($cms['hero_video'])) {
                        $heroVideoUrl = $cms['hero_video'];
                        $heroVideoMime = 'video/mp4';
                    } elseif ($customSettingVideo = Setting::get('hero_video_url')) {
                        $heroVideoUrl = $customSettingVideo;
                        $heroVideoMime = 'video/mp4';
                    }
                } catch (\Exception $mediaEx) {
                    // Fall back
                }

                $settings = [
                    'contact_email' => Setting::get('contact_email', 'kitongafarmvillas@gmail.com'),
                    'contact_phone' => Setting::get('contact_phone', '+255 758 774 695'),
                    'location_coordinates' => Setting::get('location_coordinates', '-5.0889, 39.0988'),
                    'breakfast_policy' => Setting::get('breakfast_policy'),
                ];

                return compact('villas', 'experiences', 'products', 'cms', 'homeMedia', 'heroVideoUrl', 'heroVideoMime', 'settings');
            } catch (\Exception $e) {
                return [
                    'villas' => [],
                    'experiences' => [],
                    'products' => [],
                    'cms' => [],
                    'homeMedia' => [],
                    'heroVideoUrl' => null,
                    'heroVideoMime' => null,
                    'settings' => [
                        'contact_email' => 'kitongafarmvillas@gmail.com',
                        'contact_phone' => '+255 758 774 695',
                    ],
                ];
            }
        });

        return Inertia::render('Public/Home', [
            'villas' => $data['villas'],
            'experiences' => $data['experiences'],
            'products' => $data['products'],
            'cms' => $data['cms'],
            'media' => $data['homeMedia'],
            'hero_video_url' => $data['heroVideoUrl'],
            'hero_video_mime' => $data['heroVideoMime'],
            'settings' => $data['settings'],
        ]);
    }

    public function villas()
    {
        $villas = Cache::remember('public_villas_list', 600, function () {
            return AccommodationType::where('active', true)->with('amenities')->get();
        });

        return Inertia::render('Public/Villas', [
            'villas' => $villas,
            'media' => $this->getPageMedia('villas'),
        ]);
    }

    public function showVilla($slug)
    {
        $villa = AccommodationType::where('slug', $slug)->where('active', true)->with('amenities')->firstOrFail();
        
        $otherVillas = Cache::remember("other_villas_{$villa->id}", 600, function () use ($villa) {
            return AccommodationType::where('active', true)->where('id', '!=', $villa->id)->with('amenities')->take(2)->get();
        });

        return Inertia::render('Public/VillaDetail', [
            'villa' => $villa,
            'other_villas' => $otherVillas,
            'settings' => [
                'check_in_time' => Setting::get('check_in_time', '1:00 PM'),
                'check_out_time' => Setting::get('check_out_time', '12:00 PM'),
                'breakfast_policy' => Setting::get('breakfast_policy'),
            ]
        ]);
    }

    public function experiences()
    {
        $experiences = Cache::remember('public_experiences_list', 600, function () {
            return FarmTour::where('active', true)->orderBy('sort_order')->orderBy('id')->get();
        });

        $villas = Cache::remember('public_villas_sorted', 600, function () {
            return AccommodationType::where('active', true)->orderBy('sort_order')->get();
        });

        return Inertia::render('Public/Experiences', [
            'experiences' => $experiences,
            'villas' => $villas,
            'media' => $this->getPageMedia('experiences'),
        ]);
    }

    public function products()
    {
        $products = Cache::remember('public_products_list', 600, function () {
            return Product::where('active', true)->with('category')->get();
        });

        $categories = Cache::remember('public_product_categories', 600, function () {
            return ProductCategory::all();
        });

        $villas = Cache::remember('public_villas_top3', 600, function () {
            return AccommodationType::where('active', true)->orderBy('sort_order')->take(3)->get();
        });

        return Inertia::render('Public/Products', [
            'products' => $products,
            'categories' => $categories,
            'villas' => $villas,
            'settings' => [
                'contact_phone' => Setting::get('contact_phone', '+255758774695'),
                'location_coordinates' => Setting::get('location_coordinates', '-5.0889, 39.0988'),
            ],
        ]);
    }

    public function location()
    {
        return Inertia::render('Public/Location', [
            'settings' => [
                'location_coordinates' => Setting::get('location_coordinates', '-5.0889, 39.0988'),
                'contact_phone' => Setting::get('contact_phone', '+255 758 774 695'),
            ]
        ]);
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        return back()->with('success', 'Thank you! Your message has been sent successfully.');
    }

    public function showExperience($slug)
    {
        $experience = FarmTour::where('slug', $slug)->firstOrFail();

        $villas = Cache::remember('public_villas_with_amenities', 600, function () {
            return AccommodationType::where('active', true)->with('amenities')->get();
        });

        return Inertia::render('Public/ExperienceDetail', [
            'experience' => $experience,
            'villas' => $villas,
            'cms' => $this->getCmsContent('experiences'),
            'isPreview' => request()->query('preview') === 'true' && auth()->check()
        ]);
    }

    public function farm()
    {
        $products = Cache::remember('public_farm_products', 600, function () {
            return Product::where('active', true)->take(8)->get();
        });

        $experiences = Cache::remember('public_farm_experiences', 600, function () {
            return FarmTour::where('active', true)->orderBy('sort_order')->take(3)->get();
        });

        return Inertia::render('Public/Farm', [
            'cms' => $this->getCmsContent('farm'),
            'products' => $products,
            'experiences' => $experiences,
            'media' => $this->getPageMedia('farm'),
        ]);
    }

    public function gallery()
    {
        return Inertia::render('Public/Gallery', [
            'cms' => $this->getCmsContent('gallery'),
            'uploaded_media' => $this->getPageMedia('gallery'),
        ]);
    }

    public function about()
    {
        return Inertia::render('Public/About', [
            'cms' => $this->getCmsContent('about'),
            'media' => $this->getPageMedia('about'),
        ]);
    }

    public function contact()
    {
        return Inertia::render('Public/Contact', [
            'settings' => [
                'contact_email' => Setting::get('contact_email', 'kitongafarmvillas@gmail.com'),
                'contact_phone' => Setting::get('contact_phone', '+255 758 774 695'),
                'location_coordinates' => Setting::get('location_coordinates', '-5.0889, 39.0988'),
            ]
        ]);
    }

    public function policies($policyName = 'terms')
    {
        $validPolicies = ['terms', 'privacy', 'refunds'];
        if (!in_array($policyName, $validPolicies)) {
            abort(404);
        }

        return Inertia::render('Public/Policies', [
            'policy' => $policyName,
            'cms' => $this->getCmsContent('policies'),
        ]);
    }
}

