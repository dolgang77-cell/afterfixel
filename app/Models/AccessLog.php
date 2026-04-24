<?php

namespace App\Models;

use App\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'session_id', 'device_id', 'guest_id',
        'ip_address', 'user_agent',
        'device_type', 'device_source', 'os', 'os_version',
        'device_model', 'browser', 'is_mobile',
        'app_version', 'build_version',
        'path', 'referer', 'is_logged_in',
        'client_timezone', 'client_timezone_offset', 'language',
        'created_at',
    ];

    protected $casts = [
        'is_mobile'    => 'boolean',
        'is_logged_in' => 'boolean',
        'created_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── KST 접근자 ──

    public function getCreatedAtKstAttribute(): string
    {
        return DateHelper::toKstFull($this->created_at);
    }

    public function getCreatedAtKstShortAttribute(): string
    {
        return DateHelper::toKstShort($this->created_at);
    }

    /**
     * 접속 로그 기록
     */
    public static function record(
        ?int $userId,
        ?string $sessionId,
        string $ip,
        ?string $userAgent,
        string $path,
        ?string $referer,
        array $extra = []
    ): void {
        $ua = strtolower($userAgent ?? '');

        // 기기 유형
        $isMobile = (bool) preg_match('/mobile|android|iphone|ipod/i', $ua);
        $isTablet = (bool) preg_match('/tablet|ipad/i', $ua);
        $deviceType = $isTablet ? 'tablet' : ($isMobile ? 'mobile' : 'desktop');

        // OS + 버전
        $os = 'Unknown';
        $osVersion = null;
        if (preg_match('/android\s*([\d.]+)?/i', $ua, $m)) { $os = 'Android'; $osVersion = $m[1] ?? null; }
        elseif (preg_match('/(?:iphone|ipad|ipod).*?os\s*([\d_]+)/i', $ua, $m)) { $os = 'iOS'; $osVersion = str_replace('_', '.', $m[1] ?? ''); }
        elseif (preg_match('/windows\s*nt\s*([\d.]+)/i', $ua, $m)) { $os = 'Windows'; $osVersion = $m[1] ?? null; }
        elseif (preg_match('/mac\s*os\s*x?\s*([\d_.]+)?/i', $ua, $m)) { $os = 'macOS'; $osVersion = str_replace('_', '.', $m[1] ?? ''); }
        elseif (preg_match('/linux/i', $ua)) { $os = 'Linux'; }

        // 브라우저
        $browser = 'Unknown';
        if (preg_match('/edg/i', $ua)) $browser = 'Edge';
        elseif (preg_match('/opr|opera/i', $ua)) $browser = 'Opera';
        elseif (preg_match('/chrome|crios/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/firefox|fxios/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/samsung/i', $ua)) $browser = 'Samsung';

        // 기기 모델 (모바일)
        $deviceModel = null;
        if (preg_match('/\(.*?;\s*([^;)]+)\s*build/i', $userAgent ?? '', $m)) {
            $deviceModel = trim($m[1]);
        }

        // device source 판별
        $deviceSource = 'web';
        if (!empty($extra['app_version'])) {
            $deviceSource = 'app';
        } elseif (preg_match('/standalone|wv|webview/i', $ua)) {
            $deviceSource = 'pwa';
        }

        static::create([
            'user_id'                => $userId,
            'session_id'             => $sessionId,
            'device_id'              => $extra['device_id'] ?? null,
            'guest_id'               => $extra['guest_id'] ?? null,
            'ip_address'             => $ip,
            'user_agent'             => $userAgent ? mb_substr($userAgent, 0, 500) : null,
            'device_type'            => $deviceType,
            'device_source'          => $extra['device_source'] ?? $deviceSource,
            'os'                     => $os,
            'os_version'             => $osVersion ? mb_substr($osVersion, 0, 30) : null,
            'device_model'           => $deviceModel ? mb_substr($deviceModel, 0, 100) : null,
            'browser'                => $browser,
            'is_mobile'              => $isMobile || $isTablet,
            'app_version'            => $extra['app_version'] ?? null,
            'build_version'          => $extra['build_version'] ?? null,
            'path'                   => mb_substr($path, 0, 500),
            'referer'                => $referer ? mb_substr($referer, 0, 500) : null,
            'is_logged_in'           => $userId !== null,
            'client_timezone'        => $extra['client_timezone'] ?? null,
            'client_timezone_offset' => $extra['client_timezone_offset'] ?? null,
            'language'               => $extra['language'] ?? null,
            'created_at'             => now(),
        ]);

        // 기기 레지스트리 갱신
        if (!empty($extra['device_id'])) {
            UserDevice::upsert($extra['device_id'], [
                'user_id'      => $userId,
                'platform'     => $extra['device_source'] ?? $deviceSource,
                'device_model' => $deviceModel ? mb_substr($deviceModel, 0, 100) : null,
                'os'           => $os,
                'os_version'   => $osVersion ? mb_substr($osVersion, 0, 30) : null,
                'browser'      => $browser,
                'app_version'  => $extra['app_version'] ?? null,
                'build_version' => $extra['build_version'] ?? null,
                'last_ip'      => $ip,
            ]);
        }
    }
}
