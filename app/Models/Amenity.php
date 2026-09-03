<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name', 'icon'];

    public function accommodationTypes()
    {
        return $this->belongsToMany(AccommodationType::class, 'accommodation_amenity');
    }
}
