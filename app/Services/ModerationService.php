<?php

namespace App\Services;

use App\Models\ModerationPolicy;
use App\Models\Report;
use App\Models\UserModerationAction;
use Illuminate\Support\Facades\DB;

class ModerationService
{
    /**
     * 신고 접수 + 자동숨김 처리
     */
    public static function processReport(string $targetType, int $targetId): void
    {
        $threshold = ModerationPolicy::getInt('auto_hide_report_threshold', 5);
        $reportCount = Report::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('status', '!=', 'dismissed')
            ->count();

        if ($reportCount >= $threshold) {
            self::autoHide($targetType, $targetId, "신고 {$reportCount}건 누적 (기준: {$threshold}건)");
        }
    }

    /**
     * 대상 자동 숨김
     */
    private static function autoHide(string $targetType, int $targetId, string $reason): void
    {
        $model = match ($targetType) {
            'community_post' => \App\Models\CommunityPost::find($targetId),
            'review'         => \App\Models\Review::find($targetId),
            default          => null,
        };

        if ($model && !$model->is_hidden) {
            $model->update(['is_hidden' => true]);

            DB::table('moderation_logs')->insert([
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'action'      => 'auto_hide',
                'trigger_type' => 'auto',
                'reason'      => $reason,
                'created_at'  => now(),
            ]);
        }
    }

    /**
     * 사용자 제재 상태 확인
     */
    public static function canWrite(int $userId): bool
    {
        return !UserModerationAction::where('user_id', $userId)
            ->where('is_active', true)
            ->whereIn('action_type', ['restrict_write', 'suspend', 'ban'])
            ->where(fn($q) => $q->where('is_permanent', true)->orWhere('ends_at', '>', now()))
            ->exists();
    }

    public static function canUpload(int $userId): bool
    {
        return !UserModerationAction::where('user_id', $userId)
            ->where('is_active', true)
            ->whereIn('action_type', ['restrict_upload', 'suspend', 'ban'])
            ->where(fn($q) => $q->where('is_permanent', true)->orWhere('ends_at', '>', now()))
            ->exists();
    }

    /**
     * 도배 체크
     */
    public static function checkSpamLimit(int $userId): bool
    {
        $limit = ModerationPolicy::getInt('spam_post_limit_per_hour', 5);
        $count = \App\Models\CommunityPost::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        return $count < $limit;
    }
}
