<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    public static array $intentLabels = [
        'question' => '빠른 문의',
        'quote_request' => '견적 요청',
        'reservation_request' => '예약 요청',
    ];

    public static array $visitTimeSlots = [
        'before_22' => '22시 이전 도착',
        '22_24' => '22시~24시 도착',
        'after_24' => '24시 이후 도착',
        'flexible' => '시간 조율 가능',
    ];

    public static array $statuses = [
        'pending'                  => '접수됨',
        'in_progress'              => '상담중',
        'answered'                 => '답변완료',
        'reservation_confirmed'    => '예약확정',
        'consultation_completed'   => '상담완료',
        'closed'                   => '종료',
        'hidden'                   => '숨김',
    ];

    protected $fillable = [
        'user_id', 'target_type', 'target_id', 'assigned_md_id',
        'status', 'intent_type', 'subject', 'message', 'preferred_contact',
        'visit_date', 'party_size', 'budget_min', 'budget_max',
        'visit_time_slot', 'gender_mix', 'special_request',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function getStatusLabelAttribute(): string
    {
        return static::$statuses[$this->status] ?? $this->status;
    }

    public function getIntentLabelAttribute(): string
    {
        return static::$intentLabels[$this->intent_type] ?? $this->intent_type;
    }

    public function getVisitTimeSlotLabelAttribute(): ?string
    {
        if (!$this->visit_time_slot) {
            return null;
        }

        return static::$visitTimeSlots[$this->visit_time_slot] ?? $this->visit_time_slot;
    }

    public function getBudgetTextAttribute(): ?string
    {
        if (!$this->budget_min && !$this->budget_max) {
            return null;
        }

        if ($this->budget_min && $this->budget_max) {
            return number_format($this->budget_min) . ' ~ ' . number_format($this->budget_max) . '원';
        }

        if ($this->budget_min) {
            return number_format($this->budget_min) . '원 이상';
        }

        return number_format($this->budget_max) . '원 이하';
    }

    public function priorityScore(): int
    {
        $score = match ($this->intent_type) {
            'reservation_request' => 30,
            'quote_request' => 20,
            default => 10,
        };

        if ($this->visit_date) {
            $daysUntilVisit = now()->startOfDay()->diffInDays($this->visit_date->startOfDay(), false);

            if ($daysUntilVisit <= 0) {
                $score += 20;
            } elseif ($daysUntilVisit <= 2) {
                $score += 12;
            } elseif ($daysUntilVisit <= 7) {
                $score += 6;
            }
        }

        if ($this->budget_max && $this->budget_max >= 300000) {
            $score += 10;
        } elseif ($this->budget_max && $this->budget_max >= 150000) {
            $score += 5;
        }

        if (($this->party_size ?? 0) >= 4) {
            $score += 6;
        }

        if ($this->status === 'pending') {
            $score += 8;
        }

        return $score;
    }

    public function responseDelayMinutes(): ?int
    {
        if (!in_array($this->status, ['pending', 'in_progress'], true)) {
            return null;
        }

        if ($this->hasAgentReply()) {
            return null;
        }

        return max(1, $this->created_at->diffInMinutes(now()));
    }

    public function slaLevel(): string
    {
        $minutes = $this->responseDelayMinutes();

        if ($minutes === null || $minutes < 10) {
            return 'ok';
        }

        return match (true) {
            $minutes >= 60 => 'critical',
            $minutes >= 30 => 'warning',
            default => 'attention',
        };
    }

    public function slaLabel(): ?string
    {
        $minutes = $this->responseDelayMinutes();

        if ($minutes === null || $minutes < 10) {
            return null;
        }

        return match (true) {
            $minutes >= 60 => '60분 이상 미응답',
            $minutes >= 30 => '30분 미응답',
            default => '10분 미응답',
        };
    }

    public function slaToneClass(string $surface = 'dark'): string
    {
        $palette = match ($this->slaLevel()) {
            'critical' => ['dark' => 'bg-rose-500/15 text-rose-200', 'light' => 'bg-rose-100 text-rose-700'],
            'warning' => ['dark' => 'bg-amber-500/15 text-amber-200', 'light' => 'bg-amber-100 text-amber-700'],
            'attention' => ['dark' => 'bg-sky-500/15 text-sky-200', 'light' => 'bg-sky-100 text-sky-700'],
            default => ['dark' => 'bg-white/[0.06] text-slate-400', 'light' => 'bg-gray-100 text-gray-500'],
        };

        return $palette[$surface] ?? $palette['dark'];
    }

    public function slaPriorityWeight(): int
    {
        return match ($this->slaLevel()) {
            'critical' => 40,
            'warning' => 24,
            'attention' => 12,
            default => 0,
        };
    }

    public function leadPriorityScore(): int
    {
        return $this->priorityScore() + $this->slaPriorityWeight();
    }

    public function leadGradeLabel(): string
    {
        $score = $this->leadPriorityScore();

        return match (true) {
            $score >= 45 => 'A+ 리드',
            $score >= 35 => 'A 리드',
            $score >= 25 => 'B 리드',
            default => 'C 리드',
        };
    }

    public function leadGradeTone(): string
    {
        $score = $this->leadPriorityScore();

        return match (true) {
            $score >= 45 => 'text-rose-300',
            $score >= 35 => 'text-amber-300',
            $score >= 25 => 'text-sky-300',
            default => 'text-slate-500',
        };
    }

    public function estimatedValue(): ?int
    {
        if ($this->budget_min !== null && $this->budget_max !== null) {
            return (int) round(($this->budget_min + $this->budget_max) / 2);
        }

        if ($this->budget_max !== null) {
            return (int) $this->budget_max;
        }

        if ($this->budget_min !== null) {
            return (int) $this->budget_min;
        }

        if (($this->party_size ?? 0) > 0) {
            return (int) $this->party_size * 70000;
        }

        return null;
    }

    public function estimatedValueText(): ?string
    {
        $value = $this->estimatedValue();

        if ($value === null) {
            return null;
        }

        return number_format($value) . '원 예상';
    }

    public function firstResponseMinutes(): ?int
    {
        $firstReply = $this->publicReplyCollection()
            ->first(fn (InquiryReply $reply) => in_array($reply->author_type, ['md', 'admin'], true));

        if (!$firstReply) {
            return null;
        }

        return max(1, $this->created_at->diffInMinutes($firstReply->created_at));
    }

    public function firstResponseText(): string
    {
        $minutes = $this->firstResponseMinutes();

        if ($minutes === null) {
            return '아직 답변 전';
        }

        if ($minutes < 60) {
            return $minutes . '분';
        }

        $hours = intdiv($minutes, 60);
        $remain = $minutes % 60;

        if ($remain === 0) {
            return $hours . '시간';
        }

        return $hours . '시간 ' . $remain . '분';
    }

    public function statusToneClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-rose-500/15 text-rose-300',
            'in_progress' => 'bg-sky-500/15 text-sky-300',
            'answered' => 'bg-emerald-500/15 text-emerald-300',
            'reservation_confirmed' => 'bg-violet-500/15 text-violet-300',
            'consultation_completed' => 'bg-cyan-500/15 text-cyan-300',
            'closed' => 'bg-white/[0.06] text-gray-400',
            default => 'bg-white/[0.06] text-gray-400',
        };
    }

    public function lastPublicReplyAt(): ?CarbonInterface
    {
        return $this->publicReplyCollection()->last()?->created_at;
    }

    public function lastPublicReplyText(): string
    {
        $lastReplyAt = $this->lastPublicReplyAt();

        if (!$lastReplyAt) {
            return '답변 없음';
        }

        return $lastReplyAt->diffForHumans();
    }

    public function hasAgentReply(): bool
    {
        return $this->publicReplyCollection()
            ->contains(fn (InquiryReply $reply) => in_array($reply->author_type, ['md', 'admin'], true));
    }

    public function isResponseDelayed(int $minutes = 30): bool
    {
        $waitMinutes = $this->responseDelayMinutes();

        return $waitMinutes !== null && $waitMinutes >= $minutes;
    }

    public function responseDelayText(int $minutes = 30): ?string
    {
        if (!$this->isResponseDelayed($minutes)) {
            return null;
        }

        $waitMinutes = max($minutes, $this->created_at->diffInMinutes(now()));

        if ($waitMinutes < 60) {
            return $waitMinutes . '분 지연';
        }

        $hours = intdiv($waitMinutes, 60);
        $remain = $waitMinutes % 60;

        return $remain === 0
            ? $hours . '시간 지연'
            : $hours . '시간 ' . $remain . '분 지연';
    }

    public function latestConversationReply(): ?InquiryReply
    {
        return $this->publicReplyCollection()->last();
    }

    public function latestConversationAuthorLabel(): string
    {
        $reply = $this->latestConversationReply();

        if (!$reply) {
            return '회원';
        }

        return match ($reply->author_type) {
            'md' => 'MD',
            'admin' => '관리자',
            default => '회원',
        };
    }

    public function latestConversationPreview(int $limit = 84): string
    {
        $reply = $this->latestConversationReply();
        $text = $reply?->message ?: $this->message;

        return mb_strimwidth(trim((string) $text), 0, $limit, '…', 'UTF-8');
    }

    public function nextReplyStatus(): string
    {
        if ($this->status !== 'pending') {
            return $this->status;
        }

        return match ($this->intent_type) {
            'quote_request', 'reservation_request' => 'in_progress',
            default => 'answered',
        };
    }

    public function nextUserMessageStatus(): string
    {
        return match ($this->status) {
            'answered', 'consultation_completed' => 'in_progress',
            default => $this->status,
        };
    }

    public function addReply(string $authorType, int $authorId, string $message, bool $isInternal = false): InquiryReply
    {
        $reply = $this->replies()->create([
            'author_type' => $authorType,
            'author_id' => $authorId,
            'message' => $message,
            'is_internal' => $isInternal,
        ]);

        if ($isInternal) {
            return $reply;
        }

        $nextStatus = match ($authorType) {
            'user' => $this->nextUserMessageStatus(),
            'md', 'admin' => $this->nextReplyStatus(),
            default => $this->status,
        };

        if ($nextStatus !== $this->status) {
            $this->forceFill(['status' => $nextStatus])->save();
        }

        return $reply;
    }

    public function currentTimelineStepKey(): string
    {
        if (in_array($this->status, ['consultation_completed', 'closed'], true)) {
            return 'completed';
        }

        if ($this->status === 'reservation_confirmed') {
            return 'confirmed';
        }

        if ($this->hasAgentReply() || $this->status === 'answered') {
            return 'replied';
        }

        if ($this->status === 'in_progress') {
            return 'consult';
        }

        if ($this->assigned_md_id) {
            return 'assigned';
        }

        return 'received';
    }

    public function currentTimelineStepLabel(): string
    {
        $step = collect($this->timelineSteps())->firstWhere('key', $this->currentTimelineStepKey());

        return $step['label'] ?? $this->statusLabel;
    }

    public function timelineSteps(): array
    {
        $lastAgentReplyAt = $this->publicReplyCollection()
            ->filter(fn (InquiryReply $reply) => in_array($reply->author_type, ['md', 'admin'], true))
            ->last()?->created_at;

        $stageKey = $this->currentTimelineStepKey();
        $order = ['received', 'assigned', 'consult', 'replied', 'confirmed', 'completed'];
        $activeIndex = array_search($stageKey, $order, true);
        $activeIndex = $activeIndex === false ? 0 : $activeIndex;

        $steps = [
            [
                'key' => 'received',
                'label' => '접수됨',
                'description' => '문의가 정상적으로 접수되었습니다.',
                'at' => $this->created_at,
            ],
            [
                'key' => 'assigned',
                'label' => '담당자 연결',
                'description' => $this->assignedMd?->display_name
                    ? $this->assignedMd->display_name . ' 담당으로 연결되었습니다.'
                    : '담당자 확인을 진행하고 있습니다.',
                'at' => $this->assigned_md_id ? $this->updated_at : null,
            ],
            [
                'key' => 'consult',
                'label' => '상담 진행',
                'description' => '일정, 인원, 예산 기준으로 상담이 진행 중입니다.',
                'at' => in_array($this->status, ['in_progress', 'answered', 'reservation_confirmed', 'consultation_completed', 'closed'], true) ? $this->updated_at : null,
            ],
            [
                'key' => 'replied',
                'label' => '답변 도착',
                'description' => $lastAgentReplyAt ? '담당자 답변이 도착했습니다.' : '담당자 첫 답변을 기다리고 있습니다.',
                'at' => $lastAgentReplyAt,
            ],
            [
                'key' => 'confirmed',
                'label' => '예약 확정',
                'description' => '예약 또는 방문 일정이 확정되었습니다.',
                'at' => $this->status === 'reservation_confirmed' ? $this->updated_at : null,
            ],
            [
                'key' => 'completed',
                'label' => '종료',
                'description' => in_array($this->status, ['consultation_completed', 'closed'], true)
                    ? '상담이 마무리되었습니다.'
                    : '상담 종료 전 상태입니다.',
                'at' => in_array($this->status, ['consultation_completed', 'closed'], true) ? $this->updated_at : null,
            ],
        ];

        foreach ($steps as $index => &$step) {
            $step['completed'] = $index < $activeIndex || ($index === $activeIndex);
            $step['current'] = $index === $activeIndex;
        }

        unset($step);

        return $steps;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedMd()
    {
        return $this->belongsTo(MdProfile::class, 'assigned_md_id');
    }

    public function replies()
    {
        return $this->hasMany(InquiryReply::class)->orderBy('created_at');
    }

    public function publicReplies()
    {
        return $this->hasMany(InquiryReply::class)->where('is_internal', false)->orderBy('created_at');
    }

    public function internalReplies()
    {
        return $this->hasMany(InquiryReply::class)->where('is_internal', true)->orderBy('created_at');
    }

    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeLeadInboxOrder($query)
    {
        return $query
            ->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 0
                    WHEN 'in_progress' THEN 1
                    WHEN 'answered' THEN 2
                    WHEN 'reservation_confirmed' THEN 3
                    WHEN 'consultation_completed' THEN 4
                    ELSE 5
                END
            ")
            ->orderByRaw("
                CASE
                    WHEN status IN ('pending', 'in_progress') AND created_at <= DATE_SUB(NOW(), INTERVAL 60 MINUTE) THEN 0
                    WHEN status IN ('pending', 'in_progress') AND created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 1
                    WHEN status IN ('pending', 'in_progress') AND created_at <= DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 2
                    WHEN status IN ('pending', 'in_progress') THEN 3
                    ELSE 4
                END
            ")
            ->orderByRaw("
                CASE intent_type
                    WHEN 'reservation_request' THEN 0
                    WHEN 'quote_request' THEN 1
                    ELSE 2
                END
            ")
            ->orderByRaw('CASE WHEN visit_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('visit_date')
            ->orderByDesc('budget_max')
            ->orderByDesc('party_size')
            ->orderByRaw("
                CASE
                    WHEN status IN ('pending', 'in_progress') THEN UNIX_TIMESTAMP(created_at)
                    ELSE 9999999999 - UNIX_TIMESTAMP(created_at)
                END
            ");
    }

    private function publicReplyCollection()
    {
        if ($this->relationLoaded('publicReplies')) {
            return $this->publicReplies;
        }

        return $this->publicReplies()->get();
    }


    /**
     * 문의 생성 시 담당 MD 자동 배정
     * 해당 클럽/파티에 매칭된 MD 중 우선순위가 가장 높은 MD 배정
     */
    public static function assignMd(string $targetType, int $targetId): ?int
    {
        $table = $targetType === 'club' ? 'md_club' : 'md_party';
        $fk = $targetType === 'club' ? 'club_id' : 'party_id';

        $mapping = \DB::table($table)
            ->join('md_profiles', 'md_profiles.id', '=', "{$table}.md_profile_id")
            ->where($fk, $targetId)
            ->where("{$table}.visible", true)
            ->where('md_profiles.status', 'active')
            ->orderBy("{$table}.priority")
            ->first();

        return $mapping?->md_profile_id;
    }
}
