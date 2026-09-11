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
        // 1. Update CMS Section farm_story
        DB::table('cms_sections')
            ->where('key', 'farm_story')
            ->update([
                'value' => 'Kitonga Farm Villas is built on a 150-acre organic reserve dedicated to agroecology. We cultivate modern horticulture, harvest raw honey, and raise dairy cows, all while maintaining absolute preservation of the native countryside flora and fauna.',
                'updated_at' => now(),
            ]);

        // 2. Update General Farm Tour
        DB::table('farm_tours')
            ->where('slug', 'general-farm-tour')
            ->update([
                'description' => 'Our complete agritourism experience. Dive deep into all operational aspects of Kitonga Farm: visit modern horticulture greenhouses, interact with dairy and poultry livestock, and taste farm-fresh organic items right from the soil.',
                'inclusions' => json_encode([
                    'Complete farm tour covering all zones',
                    'Greenhouses and modern horticulture admission',
                    'Livestock interaction (cattle, goats, poultry)',
                    'Mini-bar beverage & farm yogurt tasting',
                    'Swimming pool access'
                ]),
                'highlights' => json_encode([
                    'High-tech horticulture and vegetable greenhouses',
                    'Interacting with dairy cows and poultry birds',
                    'Local yogurt and milk tasting session'
                ]),
                'seo_description' => 'Detailed wider tour including livestock sections (dairy/cattle, goat farm, poultry houses: chicken, turkeys, ducks) plus modern horticulture greenhouses.',
                'updated_at' => now(),
            ]);

        // 3. Update yogurt product descriptions
        DB::table('products')
            ->where('sku', 'KFV-YOGURT-1L')
            ->update([
                'description' => 'Smooth, velvety artisanal drinking yogurt cultured from fresh morning milk, rich in natural probiotics and authentic farm sweetness in a 1L bottle.',
                'updated_at' => now(),
            ]);

        DB::table('products')
            ->where('sku', 'HARV-HONEY-01')
            ->update([
                'description' => 'Smooth, rich artisanal drinking yoghurt crafted from fresh morning milk, rich in natural probiotics and authentic farm sweetness.',
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
