<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('base_price', 15, 2)->default(0.00);
            $table->integer('capacity')->default(2);
            $table->integer('bedrooms')->default(1);
            $table->integer('beds')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->boolean('has_interior_kitchen')->default(false);
            $table->string('featured_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('minimum_stay')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('accommodation_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_type_id')->constrained()->onDelete('restrict');
            $table->string('name')->unique();
            $table->string('status')->default('active'); // active, maintenance, blocked
            $table->string('housekeeping_status')->default('clean'); // clean, dirty, inspect
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodation_amenity', function (Blueprint $table) {
            $table->foreignId('accommodation_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained()->onDelete('cascade');
            $table->primary(['accommodation_type_id', 'amenity_id']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->index();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('accommodation_unit_id')->nullable()->constrained()->onDelete('restrict');
            $table->date('check_in')->index();
            $table->date('check_out')->index();
            $table->integer('guests_count')->default(1);
            $table->string('status')->default('pending')->index(); // inquiry, pending, confirmed, checked_in, checked_out, cancelled, no_show
            $table->string('source')->default('online')->index(); // online, walk_in, phone, whatsapp, other
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('item_type'); // accommodation, experience, product, other
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('description_snapshot');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price_snapshot', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('booking_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->index();
        });

        Schema::create('availability_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_unit_id')->constrained()->onDelete('cascade');
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->string('reason')->nullable(); // maintenance, owner_use, custom
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_blocks');
        Schema::dropIfExists('booking_status_history');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('accommodation_amenity');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('accommodation_units');
        Schema::dropIfExists('accommodation_types');
    }
};
