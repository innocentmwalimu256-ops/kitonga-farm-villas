<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmTour;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ExperienceController extends Controller
{
    /**
     * Display a listing of experiences.
     */
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized access to experience manager.');

        $experiences = FarmTour::orderBy('sort_order')->orderBy('id')->get();

        return Inertia::render('Admin/Experiences/Index', [
            'experiences' => $experiences,
        ]);
    }

    /**
     * Show form for creating a new experience.
     */
    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to create experiences.');

        return Inertia::render('Admin/Experiences/Create');
    }

    /**
     * Store a newly created experience.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to store experiences.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:farm_tours,slug',
            'price' => 'required|numeric|min:0',
            'capacity_per_slot' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'good_to_know' => 'nullable|string',
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'status' => 'required|string|in:draft,preview,published,unpublished,archived',
            'inclusions' => 'nullable|array',
            'highlights' => 'nullable|array',
        ]);

        try {
            // Set defaults for JSON columns
            $validated['inclusions'] = $validated['inclusions'] ?? [];
            $validated['highlights'] = $validated['highlights'] ?? [];
            $validated['gallery'] = [];

            // Handle file upload for featured image if present
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');
                $filename = 'exp_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $validated['featured_image'] = $filename;
            } else {
                $validated['featured_image'] = $request->input('featured_image', 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1200&q=80');
            }

            // Handle gallery files
            $galleryUrls = [];
            if ($request->hasFile('gallery_files')) {
                foreach ($request->file('gallery_files') as $file) {
                    $filename = 'exp_gal_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('images'), $filename);
                    $galleryUrls[] = $filename;
                }
            }
            if ($request->has('gallery')) {
                $galleryUrls = array_merge($galleryUrls, $request->input('gallery', []));
            }
            $validated['gallery'] = $galleryUrls;

            $experience = FarmTour::create($validated);

            // Audit log
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'experience_created',
                'entity_type' => 'FarmTour',
                'entity_id' => $experience->id,
                'new_values' => $experience->toArray(),
                'created_at' => Carbon::now(),
            ]);

            return redirect()->route('admin.experiences.index')
                ->with('success', 'Experience created successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show form for editing an experience.
     */
    public function edit($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to edit experiences.');

        $experience = FarmTour::findOrFail($id);

        return Inertia::render('Admin/Experiences/Edit', [
            'experience' => $experience,
        ]);
    }

    /**
     * Update an experience.
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to update experiences.');

        $experience = FarmTour::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:farm_tours,slug,' . $experience->id,
            'price' => 'required|numeric|min:0',
            'capacity_per_slot' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'good_to_know' => 'nullable|string',
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'status' => 'required|string|in:draft,preview,published,unpublished,archived',
            'inclusions' => 'nullable|array',
            'highlights' => 'nullable|array',
        ]);

        try {
            $validated['inclusions'] = $validated['inclusions'] ?? [];
            $validated['highlights'] = $validated['highlights'] ?? [];

            // Handle file upload for featured image if present
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');
                $filename = 'exp_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $validated['featured_image'] = $filename;
            } else if ($request->has('featured_image')) {
                $validated['featured_image'] = $request->input('featured_image');
            }

            // Handle gallery files
            $galleryUrls = [];
            if ($request->hasFile('gallery_files')) {
                foreach ($request->file('gallery_files') as $file) {
                    $filename = 'exp_gal_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('images'), $filename);
                    $galleryUrls[] = $filename;
                }
            }
            if ($request->has('gallery')) {
                $galleryUrls = array_merge($galleryUrls, $request->input('gallery', []));
            }
            $validated['gallery'] = $galleryUrls;

            $oldValues = $experience->toArray();
            $experience->update($validated);

            // Audit log
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'experience_updated',
                'entity_type' => 'FarmTour',
                'entity_id' => $experience->id,
                'old_values' => $oldValues,
                'new_values' => $experience->toArray(),
                'created_at' => Carbon::now(),
            ]);

            return redirect()->route('admin.experiences.index')
                ->with('success', 'Experience updated successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove an experience.
     */
    public function destroy($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to delete experiences.');

        $experience = FarmTour::findOrFail($id);
        $oldValues = $experience->toArray();

        $experience->delete();

        // Audit log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'experience_deleted',
            'entity_type' => 'FarmTour',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.experiences.index')
            ->with('success', 'Experience deleted successfully.');
    }

    /**
     * Preview an experience in its draft status.
     */
    public function preview($slug)
    {
        abort_if(!auth()->check() || !auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to preview draft experiences.');

        $experience = FarmTour::where('slug', $slug)->firstOrFail();

        // Retrieve villas cross-sell
        $villas = \App\Models\AccommodationType::where('active', true)->with('amenities')->get();

        return Inertia::render('Public/ExperienceDetail', [
            'experience' => $experience,
            'villas' => $villas,
            'cms' => [
                'hero_title' => 'Preview Mode: ' . $experience->name,
            ],
            'isPreview' => true,
        ]);
    }
}
