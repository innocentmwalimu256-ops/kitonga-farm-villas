<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorEvent extends Model
{
    use HasFactory;

    protected $table = 'visitor_events';

    protected $fillable = [
        'visitor_session_id',
        'event_name',
        'event_data',
        'page_url',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'visitor_session_id');
    }
}
