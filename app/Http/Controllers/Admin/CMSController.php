<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CMSController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized access to CMS manager.');

        $pages = CmsPage::with('sections')->get();

        return Inertia::render('Admin/CMS/Index', [
            'pages' => $pages,
        ]);
    }

    public function updateSection(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to update CMS content.');

        $section = CmsSection::findOrFail($id);

        $validated = $request->validate([
            'value' => 'nullable|string',
            'action' => 'required|string|in:draft,publish',
        ]);

        $oldValue = $section->value;
        $metadata = $section->metadata ?? [];
        $actionWord = 'saved as draft';

        if ($validated['action'] === 'draft') {
            $metadata['draft_value'] = $validated['value'];
            $section->update([
                'metadata' => $metadata,
            ]);
        } else {
            $metadata['draft_value'] = null;
            $section->update([
                'value' => $validated['value'],
                'metadata' => $metadata,
            ]);
            $actionWord = 'published';
        }

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'cms_section_updated',
            'entity_type' => 'CmsSection',
            'entity_id' => $section->id,
            'old_values' => ['value' => $oldValue],
            'new_values' => ['value' => $validated['value'], 'status' => $validated['action']],
            'metadata' => [
                'key' => $section->key,
                'page' => $section->page ? $section->page->title : 'Global',
            ],
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "CMS content section {$actionWord} successfully.");
    }

    /**
     * Publish all drafts on a page.
     */
    public function publishPage(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_cms'), 403, 'Unauthorized to publish CMS page drafts.');

        $page = CmsPage::with('sections')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function() use ($page) {
            foreach ($page->sections as $section) {
                $metadata = $section->metadata ?? [];
                if (isset($metadata['draft_value'])) {
                    $section->update([
                        'value' => $metadata['draft_value'],
                        'metadata' => array_diff_key($metadata, ['draft_value' => '']),
                    ]);
                }
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'cms_page_published',
                'entity_type' => 'CmsPage',
                'entity_id' => $page->id,
                'created_at' => Carbon::now(),
            ]);
        });

        return back()->with('success', "CMS page '{$page->title}' drafts published successfully.");
    }
}
