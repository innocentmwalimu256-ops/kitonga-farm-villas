<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Carbon\Carbon;

class StaffUserController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasAnyPermission(['manage_users', 'manage_roles']), 403, 'Unauthorized access to staff accounts list.');

        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['manage_users', 'manage_roles']), 403, 'Unauthorized to create staff accounts.');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $user->assignRole($request->input('role'));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_staff_created',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'new_values' => $user->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Staff user '{$user->name}' created successfully.");
    }

    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasAnyPermission(['manage_users', 'manage_roles']), 403, 'Unauthorized to update staff accounts.');

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|exists:roles,name',
        ]);

        $oldValues = $user->toArray();
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        // Sync Spatie role
        $user->syncRoles([$request->input('role')]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_staff_updated',
            'entity_type' => 'User',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'new_values' => $user->fresh()->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Staff user '{$user->name}' updated successfully.");
    }
}
