<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingStatusHistory;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;
use App\Models\AvailabilityBlock;
use App\Models\Customer;
use App\Models\FarmTour;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BookingService
{
    /**
     * Check if a specific unit is available for the given dates.
     */
    public function isUnitAvailable(int $unitId, string $checkIn, string $checkOut): bool
    {
        $checkInDate = Carbon::parse($checkIn)->format('Y-m-d');
        $checkOutDate = Carbon::parse($checkOut)->format('Y-m-d');

        if ($checkInDate >= $checkOutDate) {
            return false;
        }

        // 1. Check overlapping bookings (status must not be cancelled, inquiry, or no_show)
        $hasBookingOverlap = Booking::where('accommodation_unit_id', $unitId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('check_in', '<', $checkOutDate)
                      ->where('check_out', '>', $checkInDate);
            })
            ->exists();

        if ($hasBookingOverlap) {
            return false;
        }

        // 2. Check overlapping availability blocks (maintenance, etc.)
        $hasBlockOverlap = AvailabilityBlock::where('accommodation_unit_id', $unitId)
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('start_date', '<', $checkOutDate)
                      ->where('end_date', '>', $checkInDate);
            })
            ->exists();

        if ($hasBlockOverlap) {
            return false;
        }

        return true;
    }

    /**
     * Get all available units of a certain type for the given dates.
     */
    public function getAvailableUnits(int $typeId, string $checkIn, string $checkOut)
    {
        $units = AccommodationUnit::where('accommodation_type_id', $typeId)
            ->where('status', 'active')
            ->get();

        return $units->filter(function ($unit) use ($checkIn, $checkOut) {
            return $this->isUnitAvailable($unit->id, $checkIn, $checkOut);
        });
    }

    /**
     * Create a booking with race condition protection using DB transactions and row locks.
     */
    public function createBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $nights = $checkIn->diffInDays($checkOut);

            if ($nights <= 0) {
                throw new Exception("Check-out date must be after check-in date.");
            }

            // 1. Resolve or create customer
            $customer = null;
            if (isset($data['customer_id'])) {
                $customer = Customer::findOrFail($data['customer_id']);
            } else {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                    'email' => $data['customer_email'] ?? null,
                    'notes' => $data['customer_notes'] ?? null,
                ]);
            }

            // 2. Resolve accommodation type and find available unit
            $accommodationType = AccommodationType::findOrFail($data['accommodation_type_id']);
            
            // Lock accommodation units table to prevent race conditions during concurrent requests
            // In SQLite, this locks the whole DB file; in MySQL, it locks rows or tables
            $availableUnits = AccommodationUnit::where('accommodation_type_id', $accommodationType->id)
                ->where('status', 'active')
                ->lockForUpdate() // Lock table/rows during transaction
                ->get();

            $selectedUnit = null;
            foreach ($availableUnits as $unit) {
                // Perform overlapping check inside transaction
                $overlap = Booking::where('accommodation_unit_id', $unit->id)
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->where('check_in', '<', $checkOut->format('Y-m-d'))
                              ->where('check_out', '>', $checkIn->format('Y-m-d'));
                    })
                    ->exists();

                $blocked = AvailabilityBlock::where('accommodation_unit_id', $unit->id)
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->where('start_date', '<', $checkOut->format('Y-m-d'))
                              ->where('end_date', '>', $checkIn->format('Y-m-d'));
                    })
                    ->exists();

                if (!$overlap && !$blocked) {
                    $selectedUnit = $unit;
                    break;
                }
            }

            if (!$selectedUnit) {
                throw new Exception("No units of type '{$accommodationType->name}' are available for the selected dates.");
            }

            // 3. Price calculations (using snapshots to preserve original rates)
            $ratePerNight = $data['rate_override'] ?? $accommodationType->base_price;
            $subtotal = $ratePerNight * $nights;
            $discount = $data['discount'] ?? 0.00;
            
            // Calculate dynamic tax (VAT)
            $taxRate = (float) \App\Models\Setting::get('tax_rate', 18.00);
            $tax = ($subtotal - $discount) * ($taxRate / 100);
            $total = ($subtotal - $discount) + $tax;

            $amountPaid = $data['amount_paid'] ?? 0.00;
            $balance = $total - $amountPaid;

            // Generate unique booking reference
            $reference = 'KFV-' . strtoupper(Str::random(3)) . '-' . mt_rand(1000, 9999);

            // 4. Create the booking record
            $booking = Booking::create([
                'reference' => $reference,
                'customer_id' => $customer->id,
                'accommodation_unit_id' => $selectedUnit->id,
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'guests_count' => $data['guests_count'] ?? 1,
                'status' => $data['status'] ?? 'pending',
                'source' => $data['source'] ?? 'online',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'balance' => $balance,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['user_id'] ?? null,
                'updated_by' => $data['user_id'] ?? null,
            ]);

            // 5. Create Booking Item for accommodation (Rate Snapshot)
            BookingItem::create([
                'booking_id' => $booking->id,
                'item_type' => 'accommodation',
                'item_id' => $accommodationType->id,
                'description_snapshot' => "Stay in {$accommodationType->name} ({$nights} nights @ " . number_format($ratePerNight) . " TZS/night)",
                'quantity' => $nights,
                'unit_price_snapshot' => $ratePerNight,
                'total' => $subtotal,
            ]);

            // Register primary guest in booking_guests table
            \App\Models\BookingGuest::create([
                'booking_id' => $booking->id,
                'full_name' => $customer->name,
                'phone' => $customer->phone,
                'is_primary' => true,
            ]);

            // Create Booking Items for Extras (e.g. Farm Tours)
            if (isset($data['extras']) && is_array($data['extras'])) {
                foreach ($data['extras'] as $extra) {
                    if ($extra['type'] === 'tour') {
                        $tour = \App\Models\FarmTour::findOrFail($extra['id']);
                        $qty = $extra['quantity'] ?? 1;
                        $price = $tour->price;
                        $extraTotal = $price * $qty;
                        
                        BookingItem::create([
                            'booking_id' => $booking->id,
                            'item_type' => 'tour',
                            'item_id' => $tour->id,
                            'description_snapshot' => "Farm Tour: {$tour->name} for {$qty} guest(s)",
                            'quantity' => $qty,
                            'unit_price_snapshot' => $price,
                            'total' => $extraTotal,
                        ]);
                        
                        $subtotal += $extraTotal;
                    }
                }
                
                // Recalculate tax & total with the extras included
                $tax = ($subtotal - $discount) * ($taxRate / 100);
                $total = ($subtotal - $discount) + $tax;
                $balance = $total - $amountPaid;
                
                $booking->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'balance' => $balance,
                ]);
            }

            // 6. Record Status History
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => 'none',
                'to_status' => $booking->status,
                'user_id' => $data['user_id'] ?? null,
                'notes' => 'Initial booking creation.',
                'created_at' => Carbon::now(),
            ]);

            // 7. Audit Logging
            ActivityLog::create([
                'user_id' => $data['user_id'] ?? null,
                'action' => 'booking_created',
                'entity_type' => 'Booking',
                'entity_id' => $booking->id,
                'new_values' => $booking->toArray(),
                'metadata' => [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
                'created_at' => Carbon::now(),
            ]);

            return $booking;
        });
    }

    /**
     * Transition booking status and log history + audit events.
     */
    public function transitionStatus(Booking $booking, string $newStatus, ?int $userId, ?string $notes = null)
    {
        return DB::transaction(function () use ($booking, $newStatus, $userId, $notes) {
            $oldStatus = $booking->status;
            if ($oldStatus === $newStatus) {
                return $booking;
            }

            // Update booking status
            $booking->update([
                'status' => $newStatus,
                'updated_by' => $userId,
            ]);

            // Log state history
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'user_id' => $userId,
                'notes' => $notes ?? "Status changed from {$oldStatus} to {$newStatus}.",
                'created_at' => Carbon::now(),
            ]);

            // Audit log
            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'booking_status_updated',
                'entity_type' => 'Booking',
                'entity_id' => $booking->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $newStatus],
                'metadata' => ['notes' => $notes],
                'created_at' => Carbon::now(),
            ]);

            return $booking;
        });
    }

    /**
     * Create a direct standalone Day Tour / Experience booking (no accommodation required).
     */
    public function createTourBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $tour = FarmTour::findOrFail($data['farm_tour_id']);
            $date = Carbon::parse($data['tour_date'])->format('Y-m-d');
            $guests = (int) ($data['guests_count'] ?? 1);
            $timeSlot = $data['time_slot'] ?? '09:00 AM - 11:00 AM';
            $paymentMethod = $data['payment_method'] ?? 'arrival';

            // 1. Resolve or create customer by phone or email
            $customer = null;
            if (!empty($data['customer_phone'])) {
                $customer = Customer::where('phone', $data['customer_phone'])->first();
            }
            if (!$customer && !empty($data['customer_email'])) {
                $customer = Customer::where('email', $data['customer_email'])->first();
            }

            if ($customer) {
                $customer->update([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? $customer->phone,
                    'email' => $data['customer_email'] ?? $customer->email,
                ]);
            } else {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                    'email' => $data['customer_email'] ?? null,
                ]);
            }

            // 2. Financials
            $unitPrice = (float) $tour->price;
            $totalPrice = $unitPrice * $guests;
            $amountPaid = ($paymentMethod === 'azhampay') ? $totalPrice : 0.00;
            $balance = $totalPrice - $amountPaid;

            // 3. Clean unique booking reference
            $reference = 'KTG-EXP-' . date('ymd') . '-' . strtoupper(Str::random(4));

            // 4. Create the booking record (accommodation_unit_id is null for day tours)
            $booking = Booking::create([
                'reference' => $reference,
                'customer_id' => $customer->id,
                'accommodation_unit_id' => null,
                'check_in' => $date,
                'check_out' => $date,
                'guests_count' => $guests,
                'status' => 'confirmed',
                'source' => $data['source'] ?? 'online_experience',
                'subtotal' => $totalPrice,
                'discount' => 0.00,
                'tax' => 0.00,
                'total' => $totalPrice,
                'amount_paid' => $amountPaid,
                'balance' => $balance,
                'notes' => "Experience: {$tour->name} | Slot: {$timeSlot} | Payment: {$paymentMethod}" . (!empty($data['notes']) ? " | Notes: {$data['notes']}" : ""),
            ]);

            // 5. Create BookingItem
            BookingItem::create([
                'booking_id' => $booking->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'description_snapshot' => "{$tour->name} ({$timeSlot})",
                'quantity' => $guests,
                'unit_price_snapshot' => $unitPrice,
                'total' => $totalPrice,
            ]);

            // 6. Log status history
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => 'initial',
                'to_status' => 'confirmed',
                'notes' => "Direct Experience Booking created online for {$tour->name} ({$guests} guests).",
                'created_at' => Carbon::now(),
            ]);

            // 7. Audit log
            ActivityLog::create([
                'user_id' => null,
                'action' => 'tour_booking_created',
                'entity_type' => 'Booking',
                'entity_id' => $booking->id,
                'metadata' => [
                    'reference' => $reference,
                    'tour' => $tour->name,
                    'guests' => $guests,
                    'total' => $totalPrice,
                    'payment_method' => $paymentMethod
                ],
                'created_at' => Carbon::now(),
            ]);

            return $booking;
        });
    }
}
