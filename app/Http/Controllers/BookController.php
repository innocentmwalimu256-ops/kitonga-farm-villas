<?php

namespace App\Http\Controllers;

use App\Models\AccommodationType;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $guests = (int) ($request->input('guests_count') ?? $request->input('guests') ?? 1);
        $villaId = $request->input('villa_id') ?? $request->input('accommodation_type_id');

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
                'villa_id' => $villaId ? (int) $villaId : null,
            ],
            'settings' => [
                'tax_rate' => Setting::get('tax_rate', '18.00'),
                'deposit_percentage' => Setting::get('deposit_percentage', '50.00'),
                'cancellation_policy' => Setting::get('cancellation_policy'),
                'check_in_time' => Setting::get('check_in_time', '1:00 PM'),
                'check_out_time' => Setting::get('check_out_time', '12:00 PM'),
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
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:100',
            'id_document' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'notes' => 'nullable|string',
        ]);

        try {
            // Handle ID document upload if attached
            if ($request->hasFile('id_document')) {
                $file = $request->file('id_document');
                $destinationPath = public_path('uploads/id_documents');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $filename = 'id_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $validated['id_document_path'] = 'uploads/id_documents/' . $filename;
            }

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
                'contact_email' => Setting::get('contact_email', 'kitongafarmvillas@gmail.com'),
                'contact_phone' => Setting::get('contact_phone', '+255 758 774 695'),
                'check_in_time' => Setting::get('check_in_time', '1:00 PM'),
                'check_out_time' => Setting::get('check_out_time', '12:00 PM'),
            ]
        ]);
    }

    /**
     * Show digital receipt for a booking (Villa or Tour).
     */
    public function receipt($reference)
    {
        $booking = \App\Models\Booking::where('reference', $reference)
            ->with(['customer', 'unit.type', 'items', 'payments.recorder'])
            ->firstOrFail();

        return Inertia::render('Public/BookingReceipt', [
            'booking' => $booking,
            'settings' => [
                'contact_phone' => Setting::get('contact_phone', '+255 758 774 695'),
                'contact_email' => Setting::get('contact_email', 'kitongafarmvillas@gmail.com'),
                'location_coordinates' => Setting::get('location_coordinates', 'Komkonga, Handeni, Tanga'),
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
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:100',
            'id_document' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'payment_method' => 'nullable|string|max:50',
            'mobile_network' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            if ($request->hasFile('id_document')) {
                $file = $request->file('id_document');
                $destinationPath = public_path('uploads/id_documents');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $filename = 'id_tour_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $validated['id_document_path'] = 'uploads/id_documents/' . $filename;
            }

            $validated['payment_method'] = $validated['payment_method'] ?? 'manual';
            $validated['status'] = 'pending';
            $booking = $this->bookingService->createTourBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'Ombi lako la booking limepokelewa. Tafadhali thibitisha na kamilisha malipo kwa WhatsApp.',
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
