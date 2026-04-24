<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\ReplyTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReplyTemplateService
{
    private ?bool $tableExists = null;

    public function for(string $actorType, Inquiry $inquiry): Collection
    {
        $templates = collect($this->defaultTemplates($actorType, $inquiry))
            ->map(fn (array $template, int $index) => $template + ['source' => 'default', 'sort_order' => $index]);

        if (!$this->tableExists()) {
            return $templates->values();
        }

        $dbTemplates = ReplyTemplate::query()
            ->active()
            ->whereIn('actor_type', ['shared', $actorType])
            ->where(function ($query) use ($inquiry) {
                $query->whereNull('intent_type')
                    ->orWhere('intent_type', $inquiry->intent_type);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['title', 'body', 'category', 'actor_type', 'sort_order'])
            ->map(fn (ReplyTemplate $template) => [
                'title' => $template->title,
                'body' => $template->body,
                'category' => $template->category ?: '운영',
                'source' => 'db',
                'sort_order' => $template->sort_order,
            ]);

        if ($dbTemplates->isEmpty()) {
            return $templates->values();
        }

        return $dbTemplates
            ->merge($templates)
            ->unique(fn (array $template) => $template['title'] . '|' . $template['body'])
            ->values();
    }

    private function tableExists(): bool
    {
        return $this->tableExists ??= Schema::hasTable('reply_templates');
    }

    private function defaultTemplates(string $actorType, Inquiry $inquiry): array
    {
        $visitDate = $inquiry->visit_date?->format('Y-m-d') ?: '희망 일정';
        $partySize = $inquiry->party_size ? $inquiry->party_size . '명' : '인원';
        $budget = $inquiry->budget_text ?: '예산';

        $shared = [
            [
                'title' => '접수 안내',
                'category' => '초기 응답',
                'body' => "문의 확인했습니다.\n{$visitDate} / {$partySize} 기준으로 가능 여부를 먼저 확인하고 다시 안내드리겠습니다.",
            ],
            [
                'title' => '정보 추가 요청',
                'category' => '초기 응답',
                'body' => "빠른 안내를 위해 방문 시간대와 예산 범위를 조금 더 알려주시면 바로 맞춰서 확인하겠습니다.",
            ],
            [
                'title' => '확정 유도',
                'category' => '후속 조치',
                'body' => "현재 기준으로 안내 가능한 조건을 정리해 두었습니다.\n방문 확정 여부를 알려주시면 바로 다음 단계로 진행하겠습니다.",
            ],
        ];

        $quoteTemplates = [
            [
                'title' => '견적 요청 응답',
                'category' => '견적',
                'body' => "요청하신 조건 기준으로 견적을 확인 중입니다.\n현재 파악된 범위는 {$budget}이며, 확정 가능한 옵션을 정리해서 다시 안내드리겠습니다.",
            ],
        ];

        $reservationTemplates = [
            [
                'title' => '예약 요청 응답',
                'category' => '예약',
                'body' => "예약 요청으로 접수했습니다.\n{$visitDate} / {$partySize} 기준으로 좌석과 입장 가능 여부를 먼저 확인한 뒤 바로 안내드리겠습니다.",
            ],
            [
                'title' => '예약 확정 전 확인',
                'category' => '예약',
                'body' => "확정 전 마지막 확인이 필요합니다.\n도착 예정 시간과 대표 연락 수단을 알려주시면 예약 진행을 마무리하겠습니다.",
            ],
        ];

        $actorSpecific = $actorType === 'md'
            ? [
                [
                    'title' => 'MD 현장 안내',
                    'category' => '현장 운영',
                    'body' => "현장 상황 기준으로 확인 후 바로 안내드리겠습니다.\n대기 여부나 입장 동선까지 같이 보시려면 도착 예정 시간을 알려주세요.",
                ],
            ]
            : [
                [
                    'title' => '운영팀 확인중',
                    'category' => '운영',
                    'body' => "운영팀에서 내용 확인 중입니다.\n배정된 담당자와 조건을 맞춘 뒤 다시 안내드리겠습니다.",
                ],
            ];

        return collect($shared)
            ->merge(match ($inquiry->intent_type) {
                'quote_request' => $quoteTemplates,
                'reservation_request' => $reservationTemplates,
                default => [],
            })
            ->merge($actorSpecific)
            ->all();
    }
}
