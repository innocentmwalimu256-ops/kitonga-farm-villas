<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\FarmTour;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        FarmTour::firstOrCreate(
            ['slug' => 'horse-riding'],
            [
                'name' => 'Horse Riding Experience',
                'slug' => 'horse-riding',
                'description' => 'Experience the beauty of nature on horseback at Kitonga Farm. Guided countryside ride through scenic trails.',
                'price' => 50000.00,
                'capacity_per_slot' => 10,
                'category' => 'Outdoor & Adventure',
                'duration' => '1 Hour',
                'featured_image' => 'horse-01.jpg',
                'gallery' => ['horse-01.jpg', 'horse-02.jpg'],
                'inclusions' => [
                    'Guided scenic horse ride',
                    'Full countryside trail access',
                    'Safety orientation before ride',
                ],
                'highlights' => [
                    'Majestic horses and picturesque views',
                    'Tranquil countryside trails',
                    'Memorable outdoor adventure'
                ],
                'good_to_know' => 'Comfortable riding trousers, closed shoes or boots, and sun protection recommended.',
                'featured' => true,
                'sort_order' => 3,
                'status' => 'published',
                'seo_title' => 'Enjoy Horse Riding at Kitonga Farm',
                'seo_description' => 'Experience the beauty of nature on horseback at Kitonga Farm.',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        FarmTour::where('slug', 'horse-riding')->delete();
    }
};
