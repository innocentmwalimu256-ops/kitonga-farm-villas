<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Alter Expenses table to add category reference
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('category')->constrained()->onDelete('restrict');
        });

        // 2. Rates (Seasonal & Weekend pricing rules)
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('day_of_week')->nullable(); // 0 = Sunday, 6 = Saturday (nullable for date range rules)
            $table->string('rate_adjustment_type')->default('fixed'); // fixed, percentage
            $table->decimal('value', 15, 2);
            $table->timestamps();

            $table->index(['accommodation_type_id', 'start_date', 'end_date']);
        });

        // 3. Booking Guests Register
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('passport_number')->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('booking_id');
        });

        // 4. Refunds (Auditable payment reversals)
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->string('refund_method')->default('cash'); // cash, mobile_money, bank_transfer
            $table->string('gateway_refund_id')->nullable();
            $table->text('reason');
            $table->foreignId('processed_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('rates');

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');
        });

        Schema::dropIfExists('expense_categories');
    }
};
