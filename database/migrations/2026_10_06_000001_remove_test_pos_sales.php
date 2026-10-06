<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find the test sales SALE-00001 and SALE-00002
        $testSales = DB::table('sales')
            ->whereIn('reference', ['SALE-00001', 'SALE-00002'])
            ->orWhere(function ($q) {
                $q->where('id', '<=', 2);
            })
            ->get();

        foreach ($testSales as $sale) {
            // Delete associated payments
            DB::table('payments')->where('sale_id', $sale->id)->delete();

            // Delete associated inventory movements
            DB::table('inventory_movements')
                ->where('reference_type', 'sales')
                ->where('reference_id', $sale->id)
                ->delete();

            // Delete sale items
            DB::table('sale_items')->where('sale_id', $sale->id)->delete();

            // Delete the sale record itself
            DB::table('sales')->where('id', $sale->id)->delete();
        }

        // Also clean up any orphaned payments not attached to any booking or sale
        DB::table('payments')
            ->whereNull('booking_id')
            ->whereNull('sale_id')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for test data purge
    }
};
