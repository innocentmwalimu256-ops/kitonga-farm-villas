<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Family Villa capacity from 6 to 4 guests
        DB::table('accommodation_types')
            ->where('slug', 'family-villa')
            ->orWhere('name', 'like', '%Family%')
            ->update([
                'capacity' => 4,
                'updated_at' => now(),
            ]);

        // Clear public cached villa data
        Cache::forget('public_villas_list');
        Cache::forget('public_villas_sorted');
        Cache::forget('public_villas_top3');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('accommodation_types')
            ->where('slug', 'family-villa')
            ->orWhere('name', 'like', '%Family%')
            ->update([
                'capacity' => 6,
                'updated_at' => now(),
            ]);

        Cache::forget('public_villas_list');
        Cache::forget('public_villas_sorted');
        Cache::forget('public_villas_top3');
    }
};
