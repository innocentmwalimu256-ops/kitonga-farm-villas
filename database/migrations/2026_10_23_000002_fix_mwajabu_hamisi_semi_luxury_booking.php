<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Identify Semi Luxury Accommodation Type & Unit (S1 - Semi Luxury 1)
        $semiType = DB::table('accommodation_types')
            ->where('slug', 'semi-luxury-villa')
            ->orWhere('name', 'like', '%Semi Luxury%')
            ->first();

        $semiUnit = null;
        if ($semiType) {
            $semiUnit = DB::table('accommodation_units')
                ->where('accommodation_type_id', $semiType->id)
                ->where('name', 'like', 'S1%')
                ->first();

            if (!$semiUnit) {
                $semiUnit = DB::table('accommodation_units')
                    ->where('accommodation_type_id', $semiType->id)
                    ->first();
            }
        }

        if (!$semiUnit) {
            $semiUnit = DB::table('accommodation_units')
                ->where('name', 'like', '%S1%')
                ->orWhere('name', 'like', '%Semi%')
                ->first();
        }

        // 2. Locate Mwajabu Hamisi / KFV-EDB-9530 Booking
        $booking = DB::table('bookings')
            ->where('reference', 'KFV-EDB-9530')
            ->orWhere('reference', 'like', '%9530%')
            ->first();

        if (!$booking) {
            $customer = DB::table('customers')
                ->where('name', 'like', '%Mwajabu%')
                ->first();

            if ($customer) {
                $booking = DB::table('bookings')
                    ->where('customer_id', $customer->id)
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        if ($booking) {
            $targetUnitId = $semiUnit ? $semiUnit->id : $booking->accommodation_unit_id;
            $semiRate = 200000.00;

            // 3. Update Booking Financials, Unit and Status to checked_out (Paid in Full & Departed)
            DB::table('bookings')->where('id', $booking->id)->update([
                'accommodation_unit_id' => $targetUnitId,
                'subtotal' => $semiRate,
                'discount' => 0.00,
                'tax' => 0.00,
                'total' => $semiRate,
                'amount_paid' => $semiRate,
                'balance' => 0.00,
                'status' => 'checked_out',
                'notes' => 'Mteja alibook Semi Luxury (TSh 200,000), tayari alishalipia na kashaondoka. Taarifa na salio vimesasishwa kuwa TSh 0.',
                'updated_at' => Carbon::now(),
            ]);

            // 4. Update or Insert Booking Item (1 night stay in Semi Luxury Villa)
            $existingItem = DB::table('booking_items')->where('booking_id', $booking->id)->first();
            if ($existingItem) {
                DB::table('booking_items')->where('id', $existingItem->id)->update([
                    'item_type' => 'accommodation',
                    'item_id' => $semiType ? $semiType->id : $existingItem->item_id,
                    'description_snapshot' => 'Stay in Semi Luxury Villa (1 nights @ 200,000 TZS/night)',
                    'quantity' => 1,
                    'unit_price_snapshot' => $semiRate,
                    'total' => $semiRate,
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                DB::table('booking_items')->insert([
                    'booking_id' => $booking->id,
                    'item_type' => 'accommodation',
                    'item_id' => $semiType ? $semiType->id : null,
                    'description_snapshot' => 'Stay in Semi Luxury Villa (1 nights @ 200,000 TZS/night)',
                    'quantity' => 1,
                    'unit_price_snapshot' => $semiRate,
                    'total' => $semiRate,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // 5. Ensure completed Payment record of 200,000 TZS exists
            $existingPayment = DB::table('payments')
                ->where('booking_id', $booking->id)
                ->first();

            if ($existingPayment) {
                DB::table('payments')->where('id', $existingPayment->id)->update([
                    'amount' => $semiRate,
                    'method' => $existingPayment->method ?: 'mobile_money',
                    'reference' => $existingPayment->reference ?: 'Payment Received - Mwajabu Hamisi (KFV-EDB-9530)',
                    'status' => 'completed',
                    'paid_at' => $existingPayment->paid_at ?: Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                DB::table('payments')->insert([
                    'booking_id' => $booking->id,
                    'method' => 'mobile_money',
                    'reference' => 'Payment Received - Mwajabu Hamisi (KFV-EDB-9530)',
                    'amount' => $semiRate,
                    'status' => 'completed',
                    'paid_at' => $booking->check_in ? Carbon::parse($booking->check_in)->setTime(10, 0) : Carbon::now(),
                    'recorded_by' => $booking->created_by ?? 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // 6. Record Status History Entry
            DB::table('booking_status_history')->insert([
                'booking_id' => $booking->id,
                'from_status' => $booking->status,
                'to_status' => 'checked_out',
                'user_id' => $booking->created_by ?? 1,
                'notes' => 'Mteja alibook Semi Luxury (TSh 200,000), tayari alishalipia na kashaondoka. Status imesasishwa kuwa checked_out na salio ni TSh 0.',
                'created_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
