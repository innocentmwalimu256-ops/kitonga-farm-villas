<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update tax_rate setting to 0.00
        DB::table('settings')->where('key', 'tax_rate')->update(['value' => '0.00']);

        // 2. Adjust existing bookings to remove VAT addition
        // Set tax = 0.00, total = max(0, subtotal - discount), balance = max(0, total - amount_paid)
        $bookings = DB::table('bookings')->get();
        foreach ($bookings as $b) {
            $subtotal = (float) $b->subtotal;
            $discount = (float) $b->discount;
            $newTotal = max(0.00, $subtotal - $discount);
            $amountPaid = (float) $b->amount_paid;
            $newBalance = max(0.00, $newTotal - $amountPaid);

            DB::table('bookings')->where('id', $b->id)->update([
                'tax' => 0.00,
                'total' => $newTotal,
                'balance' => $newBalance,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'tax_rate')->update(['value' => '18.00']);
    }
};
