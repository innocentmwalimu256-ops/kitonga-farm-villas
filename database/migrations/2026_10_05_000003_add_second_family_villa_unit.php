<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $familyType = AccommodationType::where('slug', 'family-villa')
            ->orWhere('name', 'like', '%Family%')
            ->first();

        if ($familyType) {
            AccommodationUnit::firstOrCreate(
                ['name' => 'F2 - Family House 2'],
                [
                    'accommodation_type_id' => $familyType->id,
                    'status' => 'active',
                    'housekeeping_status' => 'clean',
                    'notes' => 'Second unit of Family Villa with 2 bedrooms and interior kitchen.',
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        AccommodationUnit::where('name', 'F2 - Family House 2')->delete();
    }
};
