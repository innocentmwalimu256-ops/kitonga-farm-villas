<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    protected $fillable = [
        'booking_id',
        'full_name',
        'passport_number',
        'nationality',
        'phone',
        'is_primary'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
