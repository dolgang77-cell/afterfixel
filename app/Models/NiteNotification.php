<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NiteNotification extends Model
{
    protected $table = 'nite_notifications';

    protected $fillable = [
        'session_id', 'user_id', 'type', 'title', 'body',
        'link', 'data', 'channel', 'is_read', 'sent_at', 'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public const TYPE_PARTY_REMINDER        = 'party_reminder';
    public const TYPE_NEW_PARTY_MATCH       = 'new_party_match';
    public const TYPE_TONIGHT_RECOMMENDATION = 'tonight_recommendation';
    public const TYPE_SAVED_FILTER_MATCH    = 'saved_filter_match';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForViewer($query, string $sessionId, ?int $userId = null)
    {
        return $query->where(function ($subQuery) use ($sessionId, $userId) {
            $subQuery->where('session_id', $sessionId);

            if ($userId) {
                $subQuery->orWhere('user_id', $userId);
            }
        });
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
