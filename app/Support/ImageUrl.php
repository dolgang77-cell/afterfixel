<?php

namespace App\Support;

class ImageUrl
{
    public static function normalize(?string $value, ?string $fallback = null): string
    {
        $fallback ??= static::default();
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        if (str_starts_with($value, 'data:')) {
            return $value;
        }

        if (!static::isAbsoluteUrl($value)) {
            return static::path($value);
        }

        $parts = parse_url($value);

        if (!is_array($parts)) {
            return $fallback;
        }

        $host = $parts['host'] ?? null;
        $path = $parts['path'] ?? '';

        if ($host && static::shouldRemapHost($host, $path)) {
            return static::rebuildOnCurrentHost($parts);
        }

        return $value;
    }

    public static function path(string $path): string
    {
        return url('/' . ltrim($path, '/'));
    }

    public static function storage(string $path): string
    {
        return static::path('storage/' . ltrim($path, '/'));
    }

    public static function default(string $type = 'generic'): string
    {
        return match ($type) {
            'club' => static::path('images/placeholders/club-thumbnail.svg'),
            'party' => static::path('images/placeholders/party-thumbnail.svg'),
            'profile' => static::path('images/default-profile.svg'),
            default => static::path('images/placeholders/image-fallback.svg'),
        };
    }

    /**
     * Build a responsive srcset when the upstream image service supports width params.
     *
     * @param array<int, int> $widths
     */
    public static function srcset(?string $value, array $widths = [320, 640, 960]): ?string
    {
        $normalized = static::normalize($value, '');

        if ($normalized === '' || !static::isAbsoluteUrl($normalized)) {
            return null;
        }

        $parts = parse_url($normalized);

        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host !== 'images.unsplash.com') {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);
        $query['fit'] = $query['fit'] ?? 'crop';
        $query['auto'] = $query['auto'] ?? 'format';
        $query['q'] = $query['q'] ?? 80;

        $entries = [];

        foreach ($widths as $width) {
            $query['w'] = $width;
            $entries[] = static::buildAbsoluteUrl($parts, $query) . ' ' . $width . 'w';
        }

        return implode(', ', $entries);
    }

    private static function isAbsoluteUrl(string $value): bool
    {
        return (bool) preg_match('/^https?:\/\//i', $value);
    }

    /**
     * @param array<string, mixed> $parts
     * @param array<string, mixed> $query
     */
    private static function buildAbsoluteUrl(array $parts, array $query): string
    {
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $queryString = http_build_query($query);

        $url = $scheme . '://' . $host . $port . $path;

        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        if (!empty($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }

        return $url;
    }

    private static function shouldRemapHost(string $host, string $path): bool
    {
        if (!$path || !static::isLocalAssetPath($path)) {
            return false;
        }

        $normalizedHost = strtolower($host);

        if (in_array($normalizedHost, static::knownLocalHosts(), true)) {
            return true;
        }

        $appHost = parse_url(config('app.url') ?: '', PHP_URL_HOST);

        return $appHost && strtolower((string) $appHost) === $normalizedHost;
    }

    private static function rebuildOnCurrentHost(array $parts): string
    {
        $url = static::path($parts['path'] ?? '/');
        $query = $parts['query'] ?? null;
        $fragment = $parts['fragment'] ?? null;

        if ($query) {
            $url .= '?' . $query;
        }

        if ($fragment) {
            $url .= '#' . $fragment;
        }

        return $url;
    }

    private static function isLocalAssetPath(string $path): bool
    {
        return str_starts_with($path, '/storage/')
            || str_starts_with($path, '/images/')
            || str_starts_with($path, '/app-icons/')
            || str_starts_with($path, '/icons/');
    }

    /**
     * Legacy private hosts that should be rewritten to the current request host.
     *
     * @return array<int, string>
     */
    private static function knownLocalHosts(): array
    {
        return [
            '127.0.0.1',
            'localhost',
            '172.16.1.83',
        ];
    }
}
