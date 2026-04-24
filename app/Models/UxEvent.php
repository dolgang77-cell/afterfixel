<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UxEvent extends Model
{
    protected $fillable = [
        'event_name',
        'page_type',
        'target_type',
        'target_id',
        'context',
        'metadata',
        'path',
        'referrer',
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
