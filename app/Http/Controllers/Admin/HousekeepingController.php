<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationUnit;
use App\Models\Booking;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HousekeepingController extends Controller
{
    /**
     * List units with housekeeping statuses.
     */
    public function index(Request $request)
    {
        $units = AccommodationUnit::with('type')->orderBy('name')->get();
        $todayStr = Carbon::today()->format('Y-m-d');

        // Fetch departures and arrivals today to show alerts
        $todayDepartures = Booking::where('check_out', $todayStr)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->get()
            ->pluck('accommodation_unit_id')
            ->toArray();

        $todayArrivals = Booking::where('check_in', $todayStr)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->pluck('accommodation_unit_id')
            ->toArray();

        $unitsData = $units->map(function ($unit) use ($todayDepartures, $todayArrivals) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'type_name' => $unit->type->name,
                'status' => $unit->status, // active, maintenance, blocked
                'housekeeping_status' => $unit->housekeeping_status, // clean, dirty, inspect
                'notes' => $unit->notes,
                'needs_cleaning' => in_array($unit->id, $todayDepartures),
                'incoming_guest' => in_array($unit->id, $todayArrivals),
            ];
        });

        $statuses = ['clean', 'dirty', 'inspect'];

        return Inertia::render('Admin/Housekeeping/Index', [
            'units' => $unitsData,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update unit housekeeping status.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'housekeeping_status' => 'required|string|in:clean,dirty,inspect',
            'status' => 'required|string|in:active,maintenance,blocked',
            'notes' => 'nullable|string',
        ]);

        $unit = AccommodationUnit::findOrFail($id);
        $oldValues = $unit->toArray();

        $unit->update($validated);

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'housekeeping_unit_updated',
            'entity_type' => 'AccommodationUnit',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'new_values' => $unit->fresh()->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Housekeeping status of {$unit->name} updated successfully.");
    }
}
