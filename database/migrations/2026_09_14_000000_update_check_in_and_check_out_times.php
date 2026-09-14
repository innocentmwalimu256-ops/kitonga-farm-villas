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
        DB::table('settings')->updateOrInsert(
            ['key' => 'check_out_time'],
            [
                'value' => '12:00',
                'description' => 'Check-out is strictly 12:00 PM (Saa sita kamili mchana)',
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'check_in_time'],
            [
                'value' => '13:00',
                'description' => 'Check-in is 1:00 PM (Saa saba mchana), or from 12:00 PM (Saa sita kamili) if no guests are checking out on that day',
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'check_out_time')->update([
            'value' => '11:00',
            'updated_at' => now(),
        ]);

        DB::table('settings')->where('key', 'check_in_time')->update([
            'value' => '14:00',
            'updated_at' => now(),
        ]);
    }
};
