<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('view_audit_logs'), 403, 'Unauthorized access to system audit logs.');

        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
        ]);
    }
}
