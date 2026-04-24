<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CompareItem extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'target_type',
        'target_id',
    ];

    public const TYPE_CLUB = 'club';
    public const TYPE_PARTY = 'party';
    public const MAX_ITEMS = 4;

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('target_type', $type);
    }

    public static function comparedIds(string $sessionId, string $type): array
    {
        if (!static::tableExists()) {
            return [];
        }

        return static::query()
            ->forSession($sessionId)
            ->ofType($type)
            ->orderBy('id')
            ->pluck('target_id')
            ->all();
    }

    public static function isCompared(string $sessionId, string $type, int $targetId): bool
    {
        if (!static::tableExists()) {
            return false;
        }

        return static::query()
            ->forSession($sessionId)
            ->ofType($type)
            ->where('target_id', $targetId)
            ->exists();
    }

    public static function toggle(string $sessionId, ?int $userId, string $type, int $targetId): array
    {
        if (!static::tableExists()) {
            return ['added' => false, 'removed' => false, 'limit_reached' => false, 'unavailable' => true];
        }

        $existing = static::query()
            ->forSession($sessionId)
            ->ofType($type)
            ->where('target_id', $targetId)
            ->first();

        if ($existing) {
            $existing->delete();

            return ['added' => false, 'removed' => true, 'limit_reached' => false];
        }

        $count = static::query()
            ->forSession($sessionId)
            ->ofType($type)
            ->count();

        if ($count >= static::MAX_ITEMS) {
            return ['added' => false, 'removed' => false, 'limit_reached' => true];
        }

        static::query()->create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'target_type' => $type,
            'target_id' => $targetId,
        ]);

        return ['added' => true, 'removed' => false, 'limit_reached' => false];
    }

    public static function clear(string $sessionId, string $type): void
    {
        if (!static::tableExists()) {
            return;
        }

        static::query()
            ->forSession($sessionId)
            ->ofType($type)
            ->delete();
    }

    public static function resolvedItems(string $sessionId, string $type): Collection
    {
        if (!static::tableExists()) {
            return collect();
        }

        $ids = static::comparedIds($sessionId, $type);

        if (empty($ids)) {
            return collect();
        }

        $targets = match ($type) {
            static::TYPE_CLUB => Club::query()->withCount(['activeMds as active_md_count'])->whereIn('id', $ids)->get()->keyBy('id'),
            static::TYPE_PARTY => Party::query()->with(['club'])->withCount(['activeMds as active_md_count'])->whereIn('id', $ids)->get()->keyBy('id'),
            default => collect(),
        };

        return collect($ids)
            ->map(fn (int $id) => $targets->get($id))
            ->filter()
            ->values();
    }

    private static function tableExists(): bool
    {
        static $exists;

        return $exists ??= Schema::hasTable('compare_items');
    }
}
