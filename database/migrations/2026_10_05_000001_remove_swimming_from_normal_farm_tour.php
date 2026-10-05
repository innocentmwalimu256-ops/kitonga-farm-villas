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
        DB::table('farm_tours')
            ->where('slug', 'normal-farm-tour')
            ->orWhere('price', 20000)
            ->update([
                'description' => 'A relaxed, guided entry into the rhythmic beauty of Kitonga. Wander through central palm pathways, observe seasonal fruit plantations, and understand our farming philosophy before relaxing in our rural farm bar and open-air lounge.',
                'inclusions' => json_encode([
                    'Guided farm path tour',
                    'Fresh coconut refreshments',
                    'Tour of the central mango orchard',
                    'Seasonal farm fruit tasting'
                ]),
                'highlights' => json_encode([
                    'Vibrant papaya and organic chilli fields',
                    'Central mango & coconut palm pathways',
                    'Pure fresh-picked coconut juice straight from our palms'
                ]),
                'good_to_know' => 'Wear comfortable closed walking shoes, lightweight clothing, and a sun hat for outdoor paths.',
                'seo_description' => 'Tour the central farm paths, crop areas (mango, papaya, chilli) and enjoy fresh farm fruit tasting.',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};
