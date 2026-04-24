<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfileImage extends Model
{
    protected $fillable = [
        'user_id',
        'upload_uuid',
        'disk',
        'original_path',
        'image_path',
        'thumb_path',
        'mime_type',
        'original_size',
        'optimized_size',
        'width',
        'height',
        'moderation_provider',
        'moderation_verdict',
        'moderation_score',
        'moderation_labels',
        'status',
        'is_current',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'moderation_labels' => 'array',
        'is_current' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)->where('status', 'approved');
    }

    public function getImageUrlAttribute(): string
    {
        return route('profile-image.file', ['profileImage' => $this->id, 'variant' => 'image']);
    }

    public function getThumbUrlAttribute(): string
    {
        return route('profile-image.file', ['profileImage' => $this->id, 'variant' => 'thumb']);
    }

    public function getOriginalUrlAttribute(): ?string
    {
        if (!$this->original_path) {
            return null;
        }

        return route('profile-image.file', ['profileImage' => $this->id, 'variant' => 'original']);
    }

    public function approve(?int $adminId = null): void
    {
        DB::transaction(function () use ($adminId) {
            static::where('user_id', $this->user_id)->where('is_current', true)->update(['is_current' => false]);

            $this->update([
                'status' => 'approved',
                'is_current' => true,
                'approved_by' => $adminId,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);
        });
    }

    public function reject(?int $adminId = null, ?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'is_current' => false,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        static::syncCurrentForUser($this->user_id);
    }

    public static function syncCurrentForUser(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            static::where('user_id', $userId)->update(['is_current' => false]);

            $currentId = static::where('user_id', $userId)
                ->where('status', 'approved')
                ->orderByDesc('approved_at')
                ->orderByDesc('id')
                ->value('id');

            if ($currentId) {
                static::whereKey($currentId)->update(['is_current' => true]);
            }
        });
    }
}
