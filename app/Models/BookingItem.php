<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'item_type',
        'item_id',
        'description_snapshot',
        'quantity',
        'unit_price_snapshot',
        'total'
    ];

    protected $casts = [
        'unit_price_snapshot' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
