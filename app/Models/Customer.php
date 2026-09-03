<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'notes'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
