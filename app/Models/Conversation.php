<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'starter_id',
        'last_message_id',
        'last_message_preview',
        'last_message_at',
        'last_read_user_one_at',
        'last_read_user_two_at',
        'left_by_user_one_at',
        'left_by_user_two_at',
        'blocked_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_read_user_one_at' => 'datetime',
        'last_read_user_two_at' => 'datetime',
        'left_by_user_one_at' => 'datetime',
        'left_by_user_two_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function starter()
    {
        return $this->belongsTo(User::class, 'starter_id');
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($subQuery) use ($userId) {
            $subQuery->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        });
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    public function otherUserId(int $userId): ?int
    {
        if ($this->user_one_id === $userId) {
            return $this->user_two_id;
        }

        if ($this->user_two_id === $userId) {
            return $this->user_one_id;
        }

        return null;
    }
}
