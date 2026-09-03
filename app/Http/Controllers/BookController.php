<?php

namespace App\Http\Controllers;

use App\Models\AccommodationType;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Exception;

class BookController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Show booking search page.
     */
    public function showForm(Request $request)
    {
        $checkIn = $request->input('check_in', Carbon::now()->addDay()->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::now()->addDays(3)->format('Y-m-d'));
        $guests = (int) $request->input('guests', 2);

        $villas = AccommodationType::where('active', true)->get();
        $availability = [];

        if ($checkIn && $checkOut) {
            foreach ($villas as $villa) {
                $availableUnits = $this->bookingService->getAvailableUnits($villa->id, $checkIn, $checkOut);
                $availability[$villa->id] = [
                    'available' => $availableUnits->count() > 0,
                    'units_left' => $availableUnits->count(),
                ];
            }
        }

        return Inertia::render('Public/Book', [
            'villas' => $villas,
            'availability' => $availability,
            'search' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => $guests,
            ],
            'settings' => [
                'tax_rate' => Setting::get('tax_rate', '18.00'),
                'deposit_percentage' => Setting::get('deposit_percentage', '50.00'),
                'cancellation_policy' => Setting::get('cancellation_policy'),
            ]
        ]);
    }

    /**
     * Store guest booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            // Set default status to pending for online bookings
            $validated['status'] = 'pending';
            $validated['source'] = 'online';

            $booking = $this->bookingService->createBooking($validated);

            return redirect()->route('booking.success', ['reference' => $booking->reference]);
        } catch (Exception $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * Show booking success page.
     */
    public function success($reference)
    {
        $booking = \App\Models\Booking::where('reference', $reference)
            ->with(['customer', 'unit.type'])
            ->firstOrFail();

        return Inertia::render('Public/BookingSuccess', [
            'booking' => $booking,
            'settings' => [
                'cancellation_policy' => Setting::get('cancellation_policy'),
                'deposit_percentage' => Setting::get('deposit_percentage', '50.00'),
                'contact_email' => Setting::get('contact_email'),
                'contact_phone' => Setting::get('contact_phone'),
            ]
        ]);
    }

    /**
     * Store standalone day tour / experience booking (no villa stay required).
     */
    public function storeExperienceBooking(Request $request)
    {
        $validated = $request->validate([
            'farm_tour_id' => 'required|exists:farm_tours,id',
            'tour_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string|max:50',
            'guests_count' => 'required|integer|min:1|max:50',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'payment_method' => 'nullable|string|max:50',
            'mobile_network' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $validated['payment_method'] = $validated['payment_method'] ?? 'manual';
            $booking = $this->bookingService->createTourBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'Your farm experience booking has been successfully confirmed!',
                'booking' => [
                    'reference' => $booking->reference,
                    'tour_name' => $booking->items->first()?->description_snapshot,
                    'date' => Carbon::parse($booking->check_in)->format('l, d M Y'),
                    'time_slot' => $validated['time_slot'],
                    'guests' => $booking->guests_count,
                    'total' => (float) $booking->total,
                    'payment_method' => $validated['payment_method'],
                    'customer_name' => $booking->customer?->name,
                    'customer_phone' => $booking->customer?->phone,
                    'status' => $booking->status,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
