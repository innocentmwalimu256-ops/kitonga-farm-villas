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
        DB::table('accommodation_types')
            ->where('slug', 'family-villa')
            ->orWhere('name', 'Family Villa')
            ->update([
                'description' => 'Spacious 2-bedroom house with an interior kitchen and private parking. Ideal for families and small groups wanting home-cooked farm food.'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('accommodation_types')
            ->where('slug', 'family-villa')
            ->orWhere('name', 'Family Villa')
            ->update([
                'description' => 'Spacious 2-bedroom house with an interior kitchen, large private dining area, and dedicated parking. Ideal for families and small groups wanting home-cooked farm food.'
            ]);
    }
};
