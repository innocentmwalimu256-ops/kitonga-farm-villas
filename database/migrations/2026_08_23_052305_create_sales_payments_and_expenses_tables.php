<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->index();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('category')->default('product')->index(); // tour, product, bar, other
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->string('status')->default('completed')->index(); // completed, cancelled, refunded
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('service_name')->nullable(); // for non-product services (e.g. custom tours)
            $table->string('description_snapshot');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('cost_snapshot', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('method')->index(); // cash, mobile_money, bank_transfer, card, other
            $table->string('reference')->nullable()->index(); // transaction hash or receipt number
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('status')->default('completed')->index(); // pending, completed, failed, refunded
            $table->timestamp('paid_at')->nullable()->index();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index(); // Farm, Food, Electricity, Water, Salaries, Maintenance, Transport, Supplies, Marketing, Other
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('date')->index();
            $table->text('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('approved')->index(); // pending, approved, rejected
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
