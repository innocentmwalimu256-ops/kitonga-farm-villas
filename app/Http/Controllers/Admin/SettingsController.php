<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized access to settings.');

        $settings = Setting::all();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to update settings.');

        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($request->input('settings') as $item) {
            $setting = Setting::where('key', $item['key'])->first();
            $oldValue = $setting->value;

            if ($oldValue !== $item['value']) {
                $setting->update([
                    'value' => $item['value'],
                ]);

                // Log audit override
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'setting_override',
                    'entity_type' => 'Setting',
                    'entity_id' => $setting->id,
                    'old_values' => ['value' => $oldValue],
                    'new_values' => ['value' => $item['value']],
                    'metadata' => ['key' => $setting->key],
                    'created_at' => Carbon::now(),
                ]);
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configuration settings updated successfully.');
    }
}
