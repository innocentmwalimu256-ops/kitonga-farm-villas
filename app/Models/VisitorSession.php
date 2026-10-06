<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VisitorSession extends Model
{
    use HasFactory;

    protected $table = 'visitor_sessions';

    protected $fillable = [
        'session_id',
        'visitor_id',
        'first_seen_at',
        'last_seen_at',
        'landing_page',
        'exit_page',
        'referrer',
        'referrer_domain',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'device_type',
        'browser',
        'operating_system',
        'country',
        'country_code',
        'city',
        'is_new_visitor',
        'page_views_count',
        'duration_seconds',
        'ip_hash',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_new_visitor' => 'boolean',
        'page_views_count' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function pageViews()
    {
        return $this->hasMany(VisitorPageView::class, 'visitor_session_id');
    }

    public function events()
    {
        return $this->hasMany(VisitorEvent::class, 'visitor_session_id');
    }

    /**
     * Scope for live active visitors in the last N minutes (default 5).
     */
    public function scopeActive($query, int $minutes = 5)
    {
        return $query->where('last_seen_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    /**
     * Helper to get Country Flag Emoji.
     */
    public function getFlagEmojiAttribute(): string
    {
        $code = strtoupper($this->country_code ?? 'TZ');
        if (strlen($code) !== 2) return '🌍';
        $firstChar = mb_chr(ord($code[0]) - 65 + 0x1F1E6, 'UTF-8');
        $secondChar = mb_chr(ord($code[1]) - 65 + 0x1F1E6, 'UTF-8');
        return $firstChar . $secondChar;
    }
}
