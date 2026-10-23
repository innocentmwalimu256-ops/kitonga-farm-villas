<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;
use App\Models\AvailabilityBlock;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\BookingStatusHistory;
use App\Models\ConsultationRequest;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $type = $request->input('type', 'all');

        $query = Booking::with(['customer', 'unit.type', 'items']);

        // Filter by booking type (villa vs tour)
        if ($type === 'villa') {
            $query->whereNotNull('accommodation_unit_id')
                  ->where('reference', 'not like', 'KTG-EXP%');
        } elseif ($type === 'tour') {
            $query->where(function ($q) {
                $q->whereNull('accommodation_unit_id')
                  ->orWhere('source', 'online_experience')
                  ->orWhere('reference', 'like', 'KTG-EXP%');
            });
        }

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('description_snapshot', 'like', "%{$search}%");
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

        $villaCount = Booking::whereNotNull('accommodation_unit_id')
            ->where('reference', 'not like', 'KTG-EXP%')
            ->count();

        $tourCount = Booking::where(function ($q) {
            $q->whereNull('accommodation_unit_id')
              ->orWhere('source', 'online_experience')
              ->orWhere('reference', 'like', 'KTG-EXP%');
        })->count();

        $consultationCount = Schema::hasTable('consultation_requests')
            ? ConsultationRequest::count()
            : 0;

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status', 'start_date', 'end_date', 'type']),
            'counts' => [
                'all' => Booking::count(),
                'villa' => $villaCount,
                'tour' => $tourCount,
                'consultation' => $consultationCount,
            ],
            'activeType' => $type,
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
    public function create(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('create_bookings'), 403, 'Unauthorized access to create a booking.');

        $preselectedUnitId = $request->query('unit_id');
        $preselectedTypeId = $request->query('type_id');

        $preselectedUnit = null;
        if ($preselectedUnitId) {
            $preselectedUnit = AccommodationUnit::with('type')->find($preselectedUnitId);
            if ($preselectedUnit && !$preselectedTypeId) {
                $preselectedTypeId = $preselectedUnit->accommodation_type_id;
            }
        }

        $villas = AccommodationType::with(['units' => function ($q) {
            $q->where('status', 'active')->orderBy('name');
        }])->where('active', true)->get();

        $units = AccommodationUnit::with('type')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Bookings/Create', [
            'villas' => $villas,
            'units' => $units,
            'preselected_unit_id' => $preselectedUnitId ? (int) $preselectedUnitId : null,
            'preselected_unit' => $preselectedUnit,
            'preselected_type_id' => $preselectedTypeId ? (int) $preselectedTypeId : null,
            'experiences' => \App\Models\FarmTour::where('status', 'published')->orWhere('status', 'preview')->orderBy('sort_order')->get(),
            'customers' => Customer::all(),
        ]);
    }

    /**
     * Store new manual booking (Villa, Farm Tour, or Mr. Kitonga Consultation).
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('create_bookings'), 403, 'Unauthorized access to store a booking.');
        
        $bookingType = $request->input('booking_type', 'villa');

        // Meet Mr. Kitonga Consultation Flow
        if ($bookingType === 'consultation') {
            $rules = [
                'consultation_format' => 'required|in:physical,online,hq_dar',
                'consultation_topic' => 'required|string|max:255',
                'consultation_date' => 'required|date',
                'consultation_time' => 'required|string|max:100',
                'status' => 'required|string',
                'source' => 'nullable|string',
                'notes' => 'nullable|string',
                'rate_override' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|required_if:amount_paid,>0',
                'payment_reference' => 'nullable|string|max:255',
                'customer_mode' => 'required|in:new,existing',
            ];

            if ($request->input('customer_mode') === 'new') {
                $rules['customer_name'] = 'required|string|max:255';
                $rules['customer_phone'] = 'nullable|string|max:50';
                $rules['customer_email'] = 'nullable|email|max:255';
            } else {
                $rules['customer_id'] = 'required|exists:customers,id';
            }

            $validated = $request->validate($rules);

            try {
                if ($request->input('customer_mode') === 'existing') {
                    $customer = Customer::findOrFail($validated['customer_id']);
                    $custName = $customer->name;
                    $custPhone = $customer->phone ?? '';
                    $custEmail = $customer->email ?? '';
                } else {
                    $custName = $validated['customer_name'];
                    $custPhone = $validated['customer_phone'] ?? '';
                    $custEmail = $validated['customer_email'] ?? '';

                    // Automatically register or update customer record for CRM consistency
                    Customer::firstOrCreate(
                        ['phone' => $custPhone, 'email' => $custEmail],
                        ['name' => $custName]
                    );
                }

                // Standard consultation fee is 100,000 TZS
                $baseFee = (float) ($validated['rate_override'] ?? 100000);
                $discount = (float) ($validated['discount'] ?? 0);
                $finalFee = max(0, $baseFee - $discount);

                $statusMap = [
                    'confirmed' => 'confirmed',
                    'pending' => 'awaiting_payment',
                    'checked_in' => 'confirmed',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                ];
                $consultStatus = $statusMap[$validated['status']] ?? 'confirmed';
                if (!empty($validated['amount_paid']) && (float) $validated['amount_paid'] >= $finalFee && $finalFee > 0) {
                    $consultStatus = 'payment_verified';
                }

                $reference = ConsultationRequest::generateReference();

                $staffNotes = "Manual consultation reservation entered by staff: " . (auth()->user()->name ?? 'Admin Desk');
                if (!empty($validated['payment_method']) || !empty($validated['amount_paid'])) {
                    $staffNotes .= "\nPayment Received: TZS " . number_format((float) ($validated['amount_paid'] ?? 0), 0) . " via " . ($validated['payment_method'] ?? 'cash');
                    if (!empty($validated['payment_reference'])) {
                        $staffNotes .= " (Ref: " . $validated['payment_reference'] . ")";
                    }
                }

                ConsultationRequest::create([
                    'reference' => $reference,
                    'customer_name' => $custName,
                    'customer_phone' => $custPhone,
                    'customer_email' => $custEmail,
                    'format' => $validated['consultation_format'],
                    'preferred_date' => $validated['consultation_date'],
                    'preferred_time' => $validated['consultation_time'],
                    'fee' => $finalFee,
                    'topic' => $validated['consultation_topic'],
                    'message' => $validated['notes'] ?? null,
                    'status' => $consultStatus,
                    'staff_notes' => $staffNotes,
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'assigned_staff_id' => auth()->id(),
                    'confirmed_date_time' => $consultStatus === 'confirmed' || $consultStatus === 'payment_verified' ? Carbon::parse($validated['consultation_date']) : null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->route('admin.consultations.index')
                    ->with('success', "Meet Mr. Kitonga consultation appointment ({$reference}) booked successfully.");
            } catch (Exception $e) {
                return back()->withErrors(['booking' => $e->getMessage()])->withInput();
            }
        }

        if ($bookingType === 'tour') {
            $rules = [
                'farm_tour_id' => 'required|exists:farm_tours,id',
                'tour_date' => 'required|date',
                'time_slot' => 'required|string',
                'guests_count' => 'required|integer|min:1',
                'status' => 'required|string',
                'source' => 'required|string',
                'notes' => 'nullable|string',
                'id_type' => 'nullable|string|max:50',
                'id_number' => 'nullable|string|max:100',
                'id_document' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
                'rate_override' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|required_if:amount_paid,>0',
            ];

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
                if ($request->hasFile('id_document')) {
                    $file = $request->file('id_document');
                    $destinationPath = public_path('uploads/id_documents');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    $filename = 'id_adm_tour_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $validated['id_document_path'] = 'uploads/id_documents/' . $filename;
                }

                if ($request->input('customer_mode') === 'existing') {
                    $customer = Customer::findOrFail($validated['customer_id']);
                    $validated['customer_name'] = $customer->name;
                    $validated['customer_phone'] = $customer->phone;
                    $validated['customer_email'] = $customer->email;
                }

                $booking = $this->bookingService->createTourBooking($validated);

                // Update custom status, discounts or rate overrides if supplied
                if ($request->filled('rate_override') || $request->filled('discount') || $request->filled('status') || $request->filled('source')) {
                    $tour = \App\Models\FarmTour::findOrFail($validated['farm_tour_id']);
                    $unitPrice = (float) ($validated['rate_override'] ?? $tour->price);
                    $subtotal = $unitPrice * $validated['guests_count'];
                    $discount = (float) ($validated['discount'] ?? 0);
                    $total = max(0, $subtotal - $discount);
                    $amountPaid = (float) ($validated['amount_paid'] ?? 0);
                    $balance = max(0, $total - $amountPaid);

                    $booking->update([
                        'status' => $validated['status'] ?? $booking->status,
                        'source' => $validated['source'] ?? $booking->source,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'total' => $total,
                        'amount_paid' => $amountPaid,
                        'balance' => $balance,
                    ]);

                    $booking->items()->where('item_type', 'tour')->update([
                        'unit_price_snapshot' => $unitPrice,
                        'total' => $subtotal,
                    ]);
                }

                // Record immediate payment if amount_paid > 0
                if ($booking->amount_paid > 0) {
                    Payment::create([
                        'booking_id' => $booking->id,
                        'method' => $validated['payment_method'] ?? 'cash',
                        'reference' => $request->input('payment_reference'),
                        'amount' => $booking->amount_paid,
                        'status' => 'completed',
                        'paid_at' => Carbon::now(),
                        'recorded_by' => auth()->id(),
                    ]);
                }

                return redirect()->route('admin.bookings.show', $booking->id)
                    ->with('success', 'Farm Tour booking created successfully.');
            } catch (Exception $e) {
                return back()->withErrors(['booking' => $e->getMessage()])->withInput();
            }
        }

        // Villa Stay Booking Flow
        $rules = [
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'accommodation_unit_id' => 'nullable|exists:accommodation_units,id',
            'guests_count' => 'required|integer|min:1',
            'status' => 'required|string',
            'source' => 'required|string',
            'notes' => 'nullable|string',
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:100',
            'id_document' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
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
            if ($request->hasFile('id_document')) {
                $file = $request->file('id_document');
                $destinationPath = public_path('uploads/id_documents');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $filename = 'id_adm_villa_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $validated['id_document_path'] = 'uploads/id_documents/' . $filename;
            }

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
                ->with('success', 'Villa booking created successfully.');
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
            'record_payment' => 'nullable|boolean',
            'payment_amount' => 'nullable|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);

        $booking = Booking::findOrFail($id);

        if ($validated['status'] === 'cancelled') {
            abort_if(!auth()->user()->hasAnyPermission(['cancel_bookings', 'edit_bookings', 'view_bookings']), 403, 'Unauthorized to cancel bookings.');
        } else {
            abort_if(!auth()->user()->hasAnyPermission(['edit_bookings', 'create_bookings', 'view_bookings']), 403, 'Unauthorized to update booking status.');
        }

        try {
            DB::transaction(function () use ($booking, $validated) {
                $newStatus = $validated['status'];
                $this->bookingService->transitionStatus($booking, $newStatus, auth()->id(), $validated['notes'] ?? null);

                // Synchronize payment records and paid amounts with booking lifecycle:
                if (in_array($newStatus, ['cancelled', 'no_show'])) {
                    // Mark payments as cancelled so they do not count in completed billing methods/revenue
                    $booking->payments()->where('status', 'completed')->update(['status' => 'cancelled']);
                    $booking->update([
                        'amount_paid' => 0.00,
                        'balance' => (float) $booking->total,
                    ]);
                } elseif ($newStatus === 'pending') {
                    // Mark payments as pending so they don't count towards completed revenue until confirmed
                    $booking->payments()->where('status', 'completed')->update(['status' => 'pending']);
                    $booking->update([
                        'amount_paid' => 0.00,
                        'balance' => (float) $booking->total,
                    ]);
                } elseif (in_array($newStatus, ['confirmed', 'checked_in', 'checked_out'])) {
                    // Restore previously recorded payments to completed
                    $booking->payments()->whereIn('status', ['cancelled', 'pending'])->update(['status' => 'completed']);
                    $activePaymentsSum = (float) $booking->payments()->where('status', 'completed')->sum('amount');

                    // If payments existed in amount_paid column but not in payments table
                    if ($activePaymentsSum == 0 && (float) $booking->amount_paid > 0) {
                        $activePaymentsSum = (float) $booking->amount_paid;
                    }

                    // If payment recording is requested with status update, only add what is remaining
                    if (!empty($validated['record_payment'])) {
                        $remaining = max(0.00, (float) $booking->total - $activePaymentsSum);
                        $amountToAdd = !empty($validated['payment_amount']) ? min($remaining, (float) $validated['payment_amount']) : $remaining;
                        if ($amountToAdd > 0) {
                            \App\Models\Payment::create([
                                'booking_id' => $booking->id,
                                'amount' => $amountToAdd,
                                'method' => $validated['payment_method'] ?? 'mobile_money',
                                'reference' => $validated['payment_reference'] ?? 'Status Confirmation Auto-Payment',
                                'status' => 'completed',
                                'recorded_by' => auth()->id(),
                                'paid_at' => Carbon::now(),
                            ]);
                            $activePaymentsSum += $amountToAdd;
                        }
                    }

                    $booking->update([
                        'amount_paid' => $activePaymentsSum,
                        'balance' => max(0.00, (float) $booking->total - $activePaymentsSum),
                    ]);
                }
            });

            return back()->with('success', "Hali ya booking imebadilishwa kuwa {$validated['status']}.");
        } catch (\Exception $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * Directly update booking financials (total, amount_paid, balance, notes, payment sync).
     */
    public function updateFinancials(Request $request, $id)
    {
        abort_if(!auth()->user()->hasAnyPermission(['edit_bookings', 'view_revenue', 'view_bookings', 'manage_settings']), 403, 'Unauthorized to update booking financials.');

        $validated = $request->validate([
            'total' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::findOrFail($id);

        try {
            DB::transaction(function () use ($booking, $validated) {
                $oldTotal = (float) $booking->total;
                $oldPaid = (float) $booking->amount_paid;
                $oldBalance = (float) $booking->balance;

                $newTotal = (float) $validated['total'];
                $newPaid = (float) $validated['amount_paid'];
                $newBalance = max(0.00, $newTotal - $newPaid);

                $booking->update([
                    'total' => $newTotal,
                    'tax' => 0.00,
                    'amount_paid' => $newPaid,
                    'balance' => $newBalance,
                    'notes' => !empty($validated['notes']) 
                        ? ($booking->notes ? $booking->notes . "\n[Malipo Yalirekebishwa]: " . $validated['notes'] : $validated['notes'])
                        : $booking->notes,
                    'updated_by' => auth()->id(),
                ]);

                // Synchronize Payment records with new amount_paid
                if ($newPaid > 0) {
                    $existingPayment = $booking->payments()->where('status', 'completed')->first();
                    if ($existingPayment) {
                        $existingPayment->update([
                            'amount' => $newPaid,
                            'method' => $validated['payment_method'] ?? $existingPayment->method ?? 'cash',
                            'reference' => $validated['payment_reference'] ?? $existingPayment->reference ?? 'Manual Financial Adjustment',
                            'recorded_by' => auth()->id(),
                            'paid_at' => Carbon::now(),
                        ]);
                        // Clean up redundant duplicate payments
                        $booking->payments()
                            ->where('status', 'completed')
                            ->where('id', '!=', $existingPayment->id)
                            ->update(['status' => 'cancelled']);
                    } else {
                        Payment::create([
                            'booking_id' => $booking->id,
                            'amount' => $newPaid,
                            'method' => $validated['payment_method'] ?? 'cash',
                            'reference' => $validated['payment_reference'] ?? 'Manual Financial Adjustment',
                            'status' => 'completed',
                            'recorded_by' => auth()->id(),
                            'paid_at' => Carbon::now(),
                        ]);
                    }
                } else {
                    $booking->payments()->where('status', 'completed')->update(['status' => 'cancelled']);
                }

                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'booking_financials_updated',
                    'entity_type' => 'Booking',
                    'entity_id' => $booking->id,
                    'old_values' => ['total' => $oldTotal, 'amount_paid' => $oldPaid, 'balance' => $oldBalance],
                    'new_values' => ['total' => $newTotal, 'amount_paid' => $newPaid, 'balance' => $newBalance],
                    'metadata' => [
                        'notes' => $validated['notes'] ?? null,
                        'reference' => $booking->reference,
                    ],
                    'created_at' => Carbon::now(),
                ]);
            });

            return back()->with('success', 'Kiasi cha malipo, jumla kuu, na salio vimesasishwa kikamilifu.');
        } catch (\Exception $e) {
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
