<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SavedFilter extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'target_type',
        'name',
        'filters',
        'filter_hash',
        'notification_enabled',
    ];

    protected $casts = [
        'filters' => 'array',
        'notification_enabled' => 'boolean',
    ];

    private static ?bool $tableExists = null;

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function scopeOfType($query, string $type)
    {
        return $query->where('target_type', $type);
    }

    public function scopeNotifiable($query)
    {
        return $query->where('notification_enabled', true);
    }

    public static function available(): bool
    {
        return self::$tableExists ??= Schema::hasTable('saved_filters');
    }

    public static function normalizeFilters(string $targetType, array $filters): array
    {
        $normalized = array_filter(match ($targetType) {
            'club' => [
                'area' => $filters['area'] ?? null,
                'genre' => $filters['genre'] ?? null,
                'foreigner' => !empty($filters['foreigner']) ? 1 : null,
            ],
            'party' => [
                'date' => $filters['date'] ?? null,
                'area' => $filters['area'] ?? null,
                'genre' => $filters['genre'] ?? null,
            ],
            default => [],
        }, fn ($value) => $value !== null && $value !== '');

        ksort($normalized);

        return $normalized;
    }

    public static function hashFor(string $targetType, array $filters): string
    {
        return sha1($targetType . ':' . json_encode(self::normalizeFilters($targetType, $filters), JSON_UNESCAPED_UNICODE));
    }

    public static function labelFor(string $targetType, array $filters): string
    {
        $normalized = self::normalizeFilters($targetType, $filters);
        $parts = [$targetType === 'club' ? '클럽' : '파티'];

        foreach (['date', 'area', 'genre'] as $key) {
            if (!empty($normalized[$key])) {
                $parts[] = (string) $normalized[$key];
            }
        }

        if (!empty($normalized['foreigner'])) {
            $parts[] = '외국인 OK';
        }

        return implode(' · ', $parts);
    }

    public static function currentForViewer(string $sessionId, ?int $userId, string $targetType, array $filters): ?self
    {
        if (!self::available()) {
            return null;
        }

        $normalized = self::normalizeFilters($targetType, $filters);

        if (empty($normalized)) {
            return null;
        }

        return self::query()
            ->forViewer($sessionId, $userId)
            ->ofType($targetType)
            ->where('filter_hash', self::hashFor($targetType, $normalized))
            ->latest('id')
            ->first();
    }

    public static function listForViewer(string $sessionId, ?int $userId): Collection
    {
        if (!self::available()) {
            return collect();
        }

        return self::query()
            ->forViewer($sessionId, $userId)
            ->latest('id')
            ->get();
    }

    public static function saveForViewer(string $sessionId, ?int $userId, string $targetType, array $filters): ?self
    {
        if (!self::available()) {
            return null;
        }

        $normalized = self::normalizeFilters($targetType, $filters);

        if (empty($normalized)) {
            return null;
        }

        $attributes = [
            'session_id' => $sessionId,
            'target_type' => $targetType,
            'filter_hash' => self::hashFor($targetType, $normalized),
        ];

        $savedFilter = self::firstOrNew($attributes);
        $savedFilter->fill([
            'user_id' => $userId,
            'name' => self::labelFor($targetType, $normalized),
            'filters' => $normalized,
            'notification_enabled' => true,
        ]);
        $savedFilter->save();

        return $savedFilter;
    }
}
