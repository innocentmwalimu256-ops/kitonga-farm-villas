<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farm_tours', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('duration')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('video')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('highlights')->nullable();
            $table->text('good_to_know')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('status')->default('published');
        });
    }

    public function down(): void
    {
        Schema::table('farm_tours', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'duration',
                'featured_image',
                'gallery',
                'video',
                'inclusions',
                'highlights',
                'good_to_know',
                'featured',
                'sort_order',
                'seo_title',
                'seo_description',
                'status',
            ]);
        });
    }
};
