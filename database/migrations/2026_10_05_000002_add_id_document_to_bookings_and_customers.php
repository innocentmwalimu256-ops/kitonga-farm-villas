<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('id_type')->nullable()->after('guests_count'); // nida, passport, driving_license, voter_id, other
            $table->string('id_number')->nullable()->after('id_type');
            $table->string('id_document_path')->nullable()->after('id_number');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('id_type')->nullable()->after('email');
            $table->string('id_number')->nullable()->after('id_type');
            $table->string('id_document_path')->nullable()->after('id_number');
        });

        if (Schema::hasTable('booking_guests')) {
            Schema::table('booking_guests', function (Blueprint $table) {
                $table->string('id_type')->nullable()->after('passport_number');
                $table->string('id_document_path')->nullable()->after('id_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['id_type', 'id_number', 'id_document_path']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['id_type', 'id_number', 'id_document_path']);
        });

        if (Schema::hasTable('booking_guests')) {
            Schema::table('booking_guests', function (Blueprint $table) {
                $table->dropColumn(['id_type', 'id_document_path']);
            });
        }
    }
};
