<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'recipient_id',
        'body',
        'meta',
        'read_at',
        'expires_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function scopeAlive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
