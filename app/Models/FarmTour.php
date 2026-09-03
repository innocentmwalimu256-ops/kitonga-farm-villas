<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmTour extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'capacity_per_slot',
        'active',
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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'featured' => 'boolean',
        'gallery' => 'array',
        'inclusions' => 'array',
        'highlights' => 'array',
        'sort_order' => 'integer',
    ];
}
