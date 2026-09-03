<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;
use App\Models\AvailabilityBlock;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AccommodationController extends Controller
{
    /**
     * Display a listing of accommodation types and physical units.
     */
    public function index()
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'manage_settings']), 403, 'Unauthorized access to accommodation setup.');

        $types = AccommodationType::with('units')->orderBy('name')->get();
        $blocks = AvailabilityBlock::with('unit')->orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Accommodation/Index', [
            'types' => $types,
            'blocks' => $blocks,
        ]);
    }

    /**
     * Store a new accommodation type.
     */
    public function storeType(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to manage accommodation configurations.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'beds' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'has_interior_kitchen' => 'required|boolean',
            'minimum_stay' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['gallery_images'] = [];
        $validated['active'] = true;

        $type = AccommodationType::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'accommodation_type_created',
            'entity_type' => 'AccommodationType',
            'entity_id' => $type->id,
            'new_values' => $type->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Accommodation model '{$type->name}' created successfully.");
    }

    /**
     * Update accommodation type.
     */
    public function updateType(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to manage accommodation configurations.');

        $type = AccommodationType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'beds' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'has_interior_kitchen' => 'required|boolean',
            'minimum_stay' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $oldValues = $type->toArray();
        $type->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'accommodation_type_updated',
            'entity_type' => 'AccommodationType',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'new_values' => $type->fresh()->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Accommodation model '{$type->name}' updated successfully.");
    }

    /**
     * Store a physical unit.
     */
    public function storeUnit(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to manage accommodation configurations.');

        $validated = $request->validate([
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'name' => 'required|string|unique:accommodation_units,name|max:255',
            'status' => 'required|string|in:active,maintenance,blocked',
        ]);

        $unit = AccommodationUnit::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'accommodation_unit_created',
            'entity_type' => 'AccommodationUnit',
            'entity_id' => $unit->id,
            'new_values' => $unit->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Physical unit '{$unit->name}' registered successfully.");
    }

    /**
     * Update physical unit status.
     */
    public function updateUnit(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to manage accommodation configurations.');

        $unit = AccommodationUnit::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:active,maintenance,blocked',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $unit->toArray();
        $unit->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'accommodation_unit_updated',
            'entity_type' => 'AccommodationUnit',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'new_values' => $unit->fresh()->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Physical unit '{$unit->name}' status updated to '{$unit->status}'.");
    }

    /**
     * Store a blackout dates / maintenance block.
     */
    public function storeBlock(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to manage accommodation configurations.');

        $validated = $request->validate([
            'accommodation_unit_id' => 'required|exists:accommodation_units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
        ]);

        $block = AvailabilityBlock::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'availability_block_created',
            'entity_type' => 'AvailabilityBlock',
            'entity_id' => $block->id,
            'new_values' => $block->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Blackout maintenance block created successfully.');
    }

    /**
     * Remove / Delete a blackout date block.
     */
    public function destroyBlock($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to manage accommodation configurations.');

        $block = AvailabilityBlock::findOrFail($id);
        $oldValues = $block->toArray();
        $block->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'availability_block_deleted',
            'entity_type' => 'AvailabilityBlock',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Blackout date block removed successfully.');
    }
}
