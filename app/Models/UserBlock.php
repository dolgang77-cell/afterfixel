<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'reason',
    ];

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    public function scopeBetween($query, int $userId, int $otherUserId)
    {
        return $query->where(function ($outerQuery) use ($userId, $otherUserId) {
            $outerQuery->where(function ($subQuery) use ($userId, $otherUserId) {
                $subQuery->where('blocker_id', $userId)->where('blocked_id', $otherUserId);
            })->orWhere(function ($subQuery) use ($userId, $otherUserId) {
                $subQuery->where('blocker_id', $otherUserId)->where('blocked_id', $userId);
            });
        });
    }
}
