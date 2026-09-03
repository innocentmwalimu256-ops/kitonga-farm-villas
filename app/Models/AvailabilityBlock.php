<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityBlock extends Model
{
    protected $fillable = ['accommodation_unit_id', 'start_date', 'end_date', 'reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(AccommodationUnit::class, 'accommodation_unit_id');
    }
}
