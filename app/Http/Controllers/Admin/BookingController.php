<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\BookingStatusHistory;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display a list of bookings with searching and filtering.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_bookings'), 403, 'Unauthorized access to view bookings list.');

        $query = Booking::with(['customer', 'unit.type']);

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by dates
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('check_in', [$request->input('start_date'), $request->input('end_date')]);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display the calendar grid.
     */
    public function calendar(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_bookings'), 403, 'Unauthorized access to view calendar grid.');

        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $bookings = Booking::with(['customer', 'unit.type'])
            ->where('check_in', '<=', $endDate->format('Y-m-d'))
            ->where('check_out', '>=', $startDate->format('Y-m-d'))
            ->get();

        $units = AccommodationUnit::with('type')->get();

        $blocks = AvailabilityBlock::where('start_date', '<=', $endDate->format('Y-m-d'))
            ->where('end_date', '>=', $startDate->format('Y-m-d'))
            ->get();

        $canReschedule = auth()->user()->hasRole('owner') || auth()->user()->hasRole('manager');

        return Inertia::render('Admin/Bookings/Calendar', [
            'bookings' => $bookings,
            'units' => $units,
            'blocks' => $blocks,
            'can_reschedule' => $canReschedule,
            'current_month' => $month,
            'current_year' => $year,
        ]);
    }

    /**
     * Show the booking detail screen.
     */
    public function show($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_bookings'), 403, 'Unauthorized access to view booking details.');

        $booking = Booking::with([
            'customer', 
            'unit.type', 
            'items', 
            'payments.recorder', 
            'statusHistory.user',
            'creator',
            'updater'
        ])->findOrFail($id);

        $statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];
        $paymentMethods = ['cash', 'mobile_money', 'bank_transfer', 'card', 'other'];

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
            'statuses' => $statuses,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Show booking creation page.
     */
    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('create_bookings'), 403, 'Unauthorized access to create a booking.');

        return Inertia::render('Admin/Bookings/Create', [
            'villas' => AccommodationType::where('active', true)->get(),
            'customers' => Customer::all(),
        ]);
    }

    /**
     * Store new manual booking.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('create_bookings'), 403, 'Unauthorized access to store a booking.');
        $rules = [
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'guests_count' => 'required|integer|min:1',
            'status' => 'required|string',
            'source' => 'required|string',
            'notes' => 'nullable|string',
            'rate_override' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|required_if:amount_paid,>0',
        ];

        // Create new customer if not using existing
        if ($request->input('customer_mode') === 'new') {
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_phone'] = 'nullable|string';
            $rules['customer_email'] = 'nullable|email';
        } else {
            $rules['customer_id'] = 'required|exists:customers,id';
        }

        $validated = $request->validate($rules);
        $validated['user_id'] = auth()->id();

        try {
            $booking = $this->bookingService->createBooking($validated);

            // Record immediate payment if cashier entered amount_paid
            if ($booking->amount_paid > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'method' => $validated['payment_method'],
                    'reference' => $request->input('payment_reference'),
                    'amount' => $booking->amount_paid,
                    'status' => 'completed',
                    'paid_at' => Carbon::now(),
                    'recorded_by' => auth()->id(),
                ]);
            }

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Booking created successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['booking' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Transition booking status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        $booking = Booking::findOrFail($id);

        if ($validated['status'] === 'cancelled') {
            abort_if(!auth()->user()->hasPermissionTo('cancel_bookings'), 403, 'Unauthorized to cancel bookings.');
        } else {
            abort_if(!auth()->user()->hasPermissionTo('edit_bookings'), 403, 'Unauthorized to update booking status.');
        }

        try {
            $this->bookingService->transitionStatus($booking, $validated['status'], auth()->id(), $validated['notes']);
            return back()->with('success', "Booking status updated to {$validated['status']}.");
        } catch (Exception $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * Add payment to booking.
     */
    public function addPayment(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('edit_bookings'), 403, 'Unauthorized to record payments on bookings.');

        $validated = $request->validate([
            'method' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $booking = Booking::lockForUpdate()->findOrFail($id);

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'method' => $validated['method'],
                'reference' => $validated['reference'],
                'amount' => $validated['amount'],
                'status' => 'completed',
                'paid_at' => Carbon::now(),
                'recorded_by' => auth()->id(),
            ]);

            // Update booking totals
            $newPaid = $booking->amount_paid + $payment->amount;
            $newBalance = max(0.00, $booking->total - $newPaid);

            $booking->update([
                'amount_paid' => $newPaid,
                'balance' => $newBalance,
            ]);

            return back()->with('success', 'Payment added successfully.');
        });
    }

    /**
     * Reschedule stay dates for a booking.
     */
    public function reschedule(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('edit_bookings'), 403, 'Unauthorized to reschedule bookings. Only owners and managers can perform this operation.');

        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $booking = Booking::findOrFail($id);
        $oldCheckIn = $booking->check_in;
        $oldCheckOut = $booking->check_out;

        $overlap = Booking::where('accommodation_unit_id', $booking->accommodation_unit_id)
            ->where('id', '!=', $booking->id)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($validated) {
                $query->where('check_in', '<', $validated['check_out'])
                      ->where('check_out', '>', $validated['check_in']);
            })
            ->exists();

        $blocked = AvailabilityBlock::where('accommodation_unit_id', $booking->accommodation_unit_id)
            ->where(function ($query) use ($validated) {
                $query->where('start_date', '<', $validated['check_out'])
                      ->where('end_date', '>', $validated['check_in']);
            })
            ->exists();

        if ($overlap || $blocked) {
            return back()->withErrors(['booking' => 'The selected dates overlap with another booking or maintenance block on this room unit.']);
        }

        DB::transaction(function() use ($booking, $validated, $oldCheckIn, $oldCheckOut) {
            $booking->update([
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
            ]);

            \App\Models\BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $booking->status,
                'to_status' => $booking->status,
                'user_id' => auth()->id(),
                'notes' => "Rescheduled stay from [{$oldCheckIn} to {$oldCheckOut}] to [{$validated['check_in']} to {$validated['check_out']}].",
                'created_at' => Carbon::now(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'booking_rescheduled',
                'entity_type' => 'Booking',
                'entity_id' => $booking->id,
                'old_values' => ['check_in' => $oldCheckIn, 'check_out' => $oldCheckOut],
                'new_values' => ['check_in' => $validated['check_in'], 'check_out' => $validated['check_out']],
                'created_at' => Carbon::now(),
            ]);
        });

        return back()->with('success', 'Booking rescheduled successfully.');
    }
}
