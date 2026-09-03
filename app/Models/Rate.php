<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'accommodation_type_id',
        'name',
        'start_date',
        'end_date',
        'day_of_week',
        'rate_adjustment_type',
        'value'
    ];

    public function type()
    {
        return $this->belongsTo(AccommodationType::class, 'accommodation_type_id');
    }
}
