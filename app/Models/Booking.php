<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'customer_id',
        'accommodation_unit_id',
        'check_in',
        'check_out',
        'guests_count',
        'status',
        'source',
        'subtotal',
        'discount',
        'tax',
        'total',
        'amount_paid',
        'balance',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function getCheckInAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function getCheckOutAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function unit()
    {
        return $this->belongsTo(AccommodationUnit::class, 'accommodation_unit_id');
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDurationInNightsAttribute()
    {
        if ($this->check_in && $this->check_out) {
            return (int) $this->check_in->diffInDays($this->check_out);
        }
        return 0;
    }
}
