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
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique()->index();
            $table->string('visitor_id', 64)->index();
            $table->timestamp('first_seen_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->string('landing_page', 255)->nullable();
            $table->string('exit_page', 255)->nullable();
            $table->text('referrer')->nullable();
            $table->string('referrer_domain', 150)->nullable()->index();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('device_type', 30)->default('desktop')->index(); // mobile, desktop, tablet
            $table->string('browser', 50)->default('Other')->index();
            $table->string('operating_system', 50)->default('Other')->index();
            $table->string('country', 100)->default('Tanzania')->index();
            $table->string('country_code', 10)->default('TZ')->index();
            $table->string('city', 100)->nullable();
            $table->boolean('is_new_visitor')->default(true);
            $table->unsignedInteger('page_views_count')->default(1);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('visitor_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained('visitor_sessions')->cascadeOnDelete();
            $table->text('url');
            $table->string('route_name', 100)->nullable()->index();
            $table->string('page_title', 255)->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('visited_at')->nullable()->index();
            $table->unsignedInteger('time_on_page')->default(0);
            $table->timestamps();
        });

        Schema::create('visitor_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained('visitor_sessions')->cascadeOnDelete();
            $table->string('event_name', 100)->index();
            $table->text('event_data')->nullable();
            $table->text('page_url')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
        Schema::dropIfExists('visitor_page_views');
        Schema::dropIfExists('visitor_sessions');
    }
};
