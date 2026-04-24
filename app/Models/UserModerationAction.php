<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModerationAction extends Model
{
    protected $fillable = [
        'user_id', 'action_type', 'reason', 'note',
        'starts_at', 'ends_at', 'is_permanent', 'is_active', 'created_by',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'is_permanent' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * 만료된 제재 자동 비활성화
     */
    public function isExpired(): bool
    {
        return !$this->is_permanent && $this->ends_at && $this->ends_at->isPast();
    }
}
