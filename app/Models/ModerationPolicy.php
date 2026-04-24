<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ModerationPolicy extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    public static function get(string $key, string $default = ''): string
    {
        return Cache::remember("moderation_policy_{$key}", 300, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, (string) $default);
    }

    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("moderation_policy_{$key}");
        }
    }
}
