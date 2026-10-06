<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorPageView extends Model
{
    use HasFactory;

    protected $table = 'visitor_page_views';

    protected $fillable = [
        'visitor_session_id',
        'url',
        'route_name',
        'page_title',
        'referrer',
        'visited_at',
        'time_on_page',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'time_on_page' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'visitor_session_id');
    }
}
