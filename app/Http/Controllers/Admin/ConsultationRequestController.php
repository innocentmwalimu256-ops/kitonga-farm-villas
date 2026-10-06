<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class ConsultationRequestController extends Controller
{
    /**
     * Display a listing of consultation requests with metrics and search.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'manage_settings', 'view_revenue']), 403, 'Unauthorized access to consultation requests.');

        $approvedWhatsAppNumber = Setting::get('contact_phone', '+255 758 774 695');

        if (!Schema::hasTable('consultation_requests')) {
            $emptyPaginator = new LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return Inertia::render('Admin/Consultations/Index', [
                'consultations' => $emptyPaginator,
                'metrics' => [
                    'total_requests' => 0,
                    'pending_contact' => 0,
                    'awaiting_payment' => 0,
                    'confirmed' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'total_revenue_potential' => 0,
                    'verified_revenue' => 0,
                ],
                'filters' => $request->only(['search', 'status', 'format', 'start_date', 'end_date']),
                'whatsappNumber' => $approvedWhatsAppNumber,
                'migrationPending' => true,
            ]);
        }

        try {
            $query = ConsultationRequest::with('assignedStaff')->latest();

            // Search
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('topic', 'like', "%{$search}%")
                      ->orWhere('payment_reference', 'like', "%{$search}%");
                });
            }

            // Filter by Status
            if ($status = $request->input('status')) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            }

            // Filter by Format
            if ($format = $request->input('format')) {
                if ($format !== 'all') {
                    $query->where('format', $format);
                }
            }

            // Filter by Date Range
            if ($startDate = $request->input('start_date')) {
                $query->whereDate('preferred_date', '>=', $startDate);
            }
            if ($endDate = $request->input('end_date')) {
                $query->whereDate('preferred_date', '<=', $endDate);
            }

            $consultations = $query->paginate(15)->withQueryString();

            // Metrics Summary
            $all = ConsultationRequest::query();
            $metrics = [
                'total_requests' => (clone $all)->count(),
                'pending_contact' => (clone $all)->whereIn('status', ['request_created', 'whatsapp_initiated'])->count(),
                'awaiting_payment' => (clone $all)->where('status', 'awaiting_payment')->count(),
                'confirmed' => (clone $all)->where('status', 'confirmed')->count(),
                'completed' => (clone $all)->where('status', 'completed')->count(),
                'cancelled' => (clone $all)->where('status', 'cancelled')->count(),
                'total_revenue_potential' => (clone $all)->whereNotIn('status', ['cancelled'])->sum('fee'),
                'verified_revenue' => (clone $all)->whereIn('status', ['payment_verified', 'confirmed', 'completed'])->sum('fee'),
            ];
        } catch (\Throwable $e) {
            $consultations = new LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            $metrics = [
                'total_requests' => 0,
                'pending_contact' => 0,
                'awaiting_payment' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'total_revenue_potential' => 0,
                'verified_revenue' => 0,
            ];
        }

        return Inertia::render('Admin/Consultations/Index', [
            'consultations' => $consultations,
            'metrics' => $metrics,
            'filters' => $request->only(['search', 'status', 'format', 'start_date', 'end_date']),
            'whatsappNumber' => $approvedWhatsAppNumber,
        ]);
    }

    /**
     * Update consultation request status, staff notes, and appointment confirmation.
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'manage_settings']), 403, 'Unauthorized to update consultation request.');

        $consultation = ConsultationRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:request_created,whatsapp_initiated,awaiting_staff_response,awaiting_payment,payment_verified,confirmed,completed,cancelled',
            'staff_notes' => 'nullable|string|max:2000',
            'payment_reference' => 'nullable|string|max:100',
            'confirmed_date_time' => 'nullable|date',
            'assigned_staff_id' => 'nullable|exists:users,id',
        ]);

        $oldValues = $consultation->only(['status', 'staff_notes', 'payment_reference', 'confirmed_date_time']);

        $consultation->update([
            'status' => $validated['status'],
            'staff_notes' => $validated['staff_notes'] ?? $consultation->staff_notes,
            'payment_reference' => $validated['payment_reference'] ?? $consultation->payment_reference,
            'confirmed_date_time' => !empty($validated['confirmed_date_time']) ? Carbon::parse($validated['confirmed_date_time']) : $consultation->confirmed_date_time,
            'assigned_staff_id' => $validated['assigned_staff_id'] ?? auth()->id(),
        ]);

        // Audit Trail
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'consultation_request_updated',
            'entity_type' => 'ConsultationRequest',
            'entity_id' => $consultation->id,
            'old_values' => $oldValues,
            'new_values' => $consultation->only(['status', 'staff_notes', 'payment_reference', 'confirmed_date_time']),
            'metadata' => [
                'reference' => $consultation->reference,
                'customer_name' => $consultation->customer_name,
                'new_status' => $consultation->status,
            ],
            'created_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', "Consultation #{$consultation->reference} updated successfully.");
    }

    /**
     * Remove the specified consultation request from storage.
     */
    public function destroy($id)
    {
        abort_if(!auth()->user()->hasRole('admin') && !auth()->user()->hasPermissionTo('manage_settings'), 403, 'Unauthorized to delete consultation request.');

        $consultation = ConsultationRequest::findOrFail($id);
        $reference = $consultation->reference;
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'consultation_request_deleted',
            'entity_type' => 'ConsultationRequest',
            'entity_id' => $consultation->id,
            'old_values' => $consultation->toArray(),
            'metadata' => ['reference' => $reference],
            'created_at' => Carbon::now(),
        ]);

        $consultation->delete();

        return redirect()->back()->with('success', "Consultation #{$reference} deleted successfully.");
    }
}
