<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\NiteNotification;
use App\Models\NotificationSetting;
use App\Models\UserPreference;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InquiryNotificationService
{
    private static array $statusLabels = [
        'pending'                  => '접수됨',
        'in_progress'              => '상담중',
        'answered'                 => '답변 완료',
        'reservation_confirmed'    => '예약 확정',
        'consultation_completed'   => '상담 완료',
        'closed'                   => '종료',
    ];

    /**
     * 회원에게 문의 접수 알림
     */
    public static function notifyUserInquiryCreated(Inquiry $inquiry): void
    {
        self::createForUser($inquiry->user_id, $inquiry,
            '문의가 접수되었습니다',
            "'{$inquiry->subject}' 문의가 접수되었습니다. 담당자가 곧 확인합니다.",
            '/my/inquiries/' . $inquiry->id
        );
    }

    /**
     * 회원에게 MD/관리자 답변 알림
     */
    public static function notifyUserNewReply(Inquiry $inquiry, string $authorType): void
    {
        $who = $authorType === 'md' ? '담당 MD' : '관리자';
        self::createForUser($inquiry->user_id, $inquiry,
            "{$who}가 답변을 남겼습니다",
            "'{$inquiry->subject}' 문의에 새 답변이 등록되었습니다.",
            '/my/inquiries/' . $inquiry->id
        );
    }

    /**
     * 회원에게 상태 변경 알림
     */
    public static function notifyUserStatusChanged(Inquiry $inquiry): void
    {
        $label = self::$statusLabels[$inquiry->status] ?? $inquiry->status;
        $title = match ($inquiry->status) {
            'reservation_confirmed' => '예약이 확정되었습니다!',
            'consultation_completed' => '상담이 완료되었습니다',
            default => "문의 상태가 변경되었습니다: {$label}",
        };

        self::createForUser($inquiry->user_id, $inquiry, $title,
            "'{$inquiry->subject}' 문의 상태: {$label}",
            '/my/inquiries/' . $inquiry->id
        );
    }

    /**
     * 문의 대상에 연결된 담당 MD들에게 새 문의 알림
     */
    public static function notifyMdNewInquiry(Inquiry $inquiry): void
    {
        $mdUserIds = self::resolveMdUserIds($inquiry);

        foreach ($mdUserIds as $mdUserId) {
            self::createForUser(
                (int) $mdUserId,
                $inquiry,
                '새로운 문의가 접수되었습니다',
                "'{$inquiry->subject}' 문의가 접수되었습니다. 확인해주세요.",
                '/md-dashboard/inquiries/' . $inquiry->id
            );
        }
    }

    public static function notifyMdReminderRequested(Inquiry $inquiry): void
    {
        $mdUserIds = self::resolveMdUserIds($inquiry);

        foreach ($mdUserIds as $mdUserId) {
            self::createForUser(
                (int) $mdUserId,
                $inquiry,
                '회원이 답변 재확인을 요청했습니다',
                "'{$inquiry->subject}' 문의에 재알림 요청이 도착했습니다.",
                '/md-dashboard/inquiries/' . $inquiry->id,
                'inquiry_reminder'
            );
        }
    }

    public static function notifyMdUserFollowUp(Inquiry $inquiry): void
    {
        $mdUserIds = self::resolveMdUserIds($inquiry);

        foreach ($mdUserIds as $mdUserId) {
            self::createForUser(
                (int) $mdUserId,
                $inquiry,
                '회원이 추가 메시지를 남겼습니다',
                "'{$inquiry->subject}' 문의에 새 회원 메시지가 도착했습니다.",
                '/md-dashboard/inquiries/' . $inquiry->id
            );
        }
    }

    public static function latestReminderAt(Inquiry $inquiry): ?CarbonInterface
    {
        return NiteNotification::where('type', 'inquiry_reminder')
            ->where('data->inquiry_id', $inquiry->id)
            ->latest('id')
            ->value('created_at');
    }

    private static function createForUser(
        int $userId,
        Inquiry $inquiry,
        string $title,
        string $body,
        ?string $link = null,
        string $type = 'inquiry_update'
    ): void
    {
        try {
            NiteNotification::create([
                'session_id' => self::resolveSessionId($userId),
                'user_id'  => $userId,
                'type'     => $type,
                'title'    => $title,
                'body'     => $body,
                'link'     => $link,
                'data'     => ['inquiry_id' => $inquiry->id, 'status' => $inquiry->status],
                'channel'  => 'in_app',
                'is_read'  => false,
                'sent_at'  => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Inquiry notification create failed', [
                'inquiry_id' => $inquiry->id,
                'user_id' => $userId,
                'title' => $title,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private static function resolveMdUserIds(Inquiry $inquiry)
    {
        if ($inquiry->assigned_md_id) {
            $assignedUserId = DB::table('md_profiles')
                ->where('id', $inquiry->assigned_md_id)
                ->where('visible', true)
                ->where('status', 'active')
                ->value('user_id');

            if ($assignedUserId) {
                return collect([(int) $assignedUserId]);
            }
        }

        $table = $inquiry->target_type === 'club' ? 'md_club' : 'md_party';
        $foreignKey = $inquiry->target_type === 'club' ? 'club_id' : 'party_id';

        return DB::table($table)
            ->join('md_profiles', 'md_profiles.id', '=', "{$table}.md_profile_id")
            ->where("{$table}.{$foreignKey}", $inquiry->target_id)
            ->where("{$table}.visible", true)
            ->where('md_profiles.visible', true)
            ->where('md_profiles.status', 'active')
            ->whereNotNull('md_profiles.user_id')
            ->distinct()
            ->pluck('md_profiles.user_id');
    }

    private static function resolveSessionId(int $userId): string
    {
        $sessionId = NotificationSetting::where('user_id', $userId)->value('session_id')
            ?? UserPreference::where('user_id', $userId)->value('session_id')
            ?? NiteNotification::where('user_id', $userId)->whereNotNull('session_id')->latest('id')->value('session_id');

        if ($sessionId) {
            return $sessionId;
        }

        try {
            $currentSessionId = session()->getId();
        } catch (\Throwable) {
            $currentSessionId = null;
        }

        return $currentSessionId ?: 'user:' . $userId;
    }
}
