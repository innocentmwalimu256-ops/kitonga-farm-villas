<?php

namespace App\Http\Controllers;

use App\Models\AccommodationType;
use App\Models\FarmTour;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    /**
     * Helper to get CMS content for a page.
     */
    protected function getCmsContent(string $pageSlug): array
    {
        $page = CmsPage::where('slug', $pageSlug)->with('sections')->first();
        $content = [];
        if ($page) {
            $isPreview = request()->query('preview') === 'true' && auth()->check();
            foreach ($page->sections as $sec) {
                $val = $sec->value;
                if ($isPreview) {
                    $metadata = is_array($sec->metadata) ? $sec->metadata : json_decode($sec->metadata ?? '{}', true);
                    if (isset($metadata['draft_value'])) {
                        $val = $metadata['draft_value'];
                    }
                }
                $content[$sec->key] = $val;
            }
        }
        return $content;
    }

    public function home()
    {
        try {
            $villas = AccommodationType::where('active', true)->orderBy('sort_order')->get();
            $experiences = FarmTour::where('active', true)->get();
            $products = Product::where('active', true)->take(6)->get();
            $cms = $this->getCmsContent('home');
            $settings = [
                'contact_email' => Setting::get('contact_email'),
                'contact_phone' => Setting::get('contact_phone'),
                'location_coordinates' => Setting::get('location_coordinates'),
                'breakfast_policy' => Setting::get('breakfast_policy'),
            ];
        } catch (\Exception $e) {
            $villas = [];
            $experiences = [];
            $products = [];
            $cms = [];
            $settings = [
                'contact_email' => 'kitongafarmvillas@gmail.com',
                'contact_phone' => '+255 758 774 695',
            ];
        }

        return Inertia::render('Public/Home', [
            'villas' => $villas,
            'experiences' => $experiences,
            'products' => $products,
            'cms' => $cms,
            'settings' => $settings,
        ]);
    }

    public function villas()
    {
        return Inertia::render('Public/Villas', [
            'villas' => AccommodationType::where('active', true)->with('amenities')->get()
        ]);
    }

    public function showVilla($slug)
    {
        $villa = AccommodationType::where('slug', $slug)->where('active', true)->with('amenities')->firstOrFail();
        
        return Inertia::render('Public/VillaDetail', [
            'villa' => $villa,
            'other_villas' => AccommodationType::where('active', true)->where('id', '!=', $villa->id)->with('amenities')->take(2)->get(),
            'settings' => [
                'check_in_time' => Setting::get('check_in_time', '14:00'),
                'check_out_time' => Setting::get('check_out_time', '11:00'),
                'breakfast_policy' => Setting::get('breakfast_policy'),
            ]
        ]);
    }

    public function experiences()
    {
        return Inertia::render('Public/Experiences', [
            'experiences' => FarmTour::where('active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'villas' => AccommodationType::where('active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function products()
    {
        return Inertia::render('Public/Products', [
            'products' => Product::where('active', true)->with('category')->get(),
            'categories' => ProductCategory::all(),
            'villas' => AccommodationType::where('active', true)->orderBy('sort_order')->take(3)->get(),
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
                'location_coordinates' => Setting::get('location_coordinates'),
                'contact_phone' => Setting::get('contact_phone'),
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
        $experience = FarmTour::where('slug', $slug)->first();
        if (!$experience) {
            abort(404);
        }

        $villas = AccommodationType::where('active', true)->with('amenities')->get();

        return Inertia::render('Public/ExperienceDetail', [
            'experience' => $experience,
            'villas' => $villas,
            'cms' => $this->getCmsContent('experiences'),
            'isPreview' => request()->query('preview') === 'true' && auth()->check()
        ]);
    }

    public function farm()
    {
        return Inertia::render('Public/Farm', [
            'cms' => $this->getCmsContent('farm'),
            'products' => Product::where('active', true)->take(8)->get(),
            'experiences' => FarmTour::where('active', true)->orderBy('sort_order')->take(3)->get(),
        ]);
    }

    public function gallery()
    {
        return Inertia::render('Public/Gallery', [
            'cms' => $this->getCmsContent('gallery'),
        ]);
    }

    public function about()
    {
        return Inertia::render('Public/About', [
            'cms' => $this->getCmsContent('about'),
        ]);
    }

    public function contact()
    {
        return Inertia::render('Public/Contact', [
            'settings' => [
                'contact_email' => Setting::get('contact_email'),
                'contact_phone' => Setting::get('contact_phone'),
                'location_coordinates' => Setting::get('location_coordinates'),
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
