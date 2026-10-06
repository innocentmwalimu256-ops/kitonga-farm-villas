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
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique()->index();
            $table->string('customer_name', 255);
            $table->string('customer_phone', 50);
            $table->string('customer_email', 255)->nullable();
            $table->string('format', 50)->default('physical'); // physical, online
            $table->date('preferred_date');
            $table->string('preferred_time', 100);
            $table->decimal('fee', 12, 2)->default(100000.00);
            $table->string('topic', 255)->default('General Agritourism & Farm Strategy');
            $table->text('message')->nullable();
            $table->string('status', 50)->default('request_created')->index();
            // Statuses: request_created, whatsapp_initiated, awaiting_staff_response, awaiting_payment, payment_verified, confirmed, completed, cancelled
            
            $table->timestamp('whatsapp_clicked_at')->nullable();
            $table->text('staff_notes')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->dateTime('confirmed_date_time')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};
