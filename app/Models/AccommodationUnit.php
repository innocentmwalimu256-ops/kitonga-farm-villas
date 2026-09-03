<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationUnit extends Model
{
    protected $fillable = ['accommodation_type_id', 'name', 'status', 'housekeeping_status', 'notes'];

    public function type()
    {
        return $this->belongsTo(AccommodationType::class, 'accommodation_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function blocks()
    {
        return $this->hasMany(AvailabilityBlock::class);
    }
}
