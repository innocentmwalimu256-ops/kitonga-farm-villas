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
        // Find test honey / asali products
        $honeyProducts = DB::table('products')
            ->where('name', 'like', '%honey%')
            ->orWhere('name', 'like', '%asali%')
            ->orWhere('sku', 'like', '%HONEY%')
            ->orWhere('sku', 'like', '%ASALI%')
            ->get();

        foreach ($honeyProducts as $prod) {
            // Check if there are associated sales items; if not, delete movements and product
            $salesCount = DB::table('sale_items')->where('product_id', $prod->id)->count();
            if ($salesCount === 0) {
                DB::table('inventory_movements')->where('product_id', $prod->id)->delete();
                DB::table('products')->where('id', $prod->id)->delete();
            } else {
                // If it has sales, deactivate it
                DB::table('products')->where('id', $prod->id)->update(['active' => false]);
            }
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
