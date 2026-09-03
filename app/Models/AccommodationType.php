<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'base_price',
        'capacity',
        'bedrooms',
        'beds',
        'bathrooms',
        'has_interior_kitchen',
        'featured_image',
        'gallery_images',
        'active',
        'minimum_stay',
        'sort_order'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'has_interior_kitchen' => 'boolean',
        'active' => 'boolean',
        'gallery_images' => 'array',
    ];

    public function units()
    {
        return $this->hasMany(AccommodationUnit::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_amenity');
    }
}
