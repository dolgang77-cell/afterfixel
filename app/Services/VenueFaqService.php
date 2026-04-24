<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Party;

class VenueFaqService
{
    public function forClub(Club $club, array $inquiryConversion = []): array
    {
        $visitWindow = $inquiryConversion['best_visit_window'] ?? null;
        $responseText = $inquiryConversion['response_time_text'] ?? '응답 데이터 준비중';
        $responseHint = $inquiryConversion['response_hint'] ?? '문의 접수 후 순차적으로 안내됩니다.';
        $budgetGuide = $inquiryConversion['budget_guide_text'] ?? '입장료와 예산은 문의에서 최종 안내됩니다.';
        $contactLabel = (($inquiryConversion['assigned_md_count'] ?? 0) > 0)
            ? '현재 응대 가능한 담당자가 연결된 상태입니다.'
            : '문의 접수 후 운영팀이 순차적으로 확인합니다.';

        return [
            [
                'icon' => '👔',
                'question' => '드레스코드가 있나요?',
                'answer' => $club->dress_code_detail
                    ?: ($club->dress_code
                        ? '기본 드레스코드는 ' . $club->dress_code . '입니다.'
                        : '별도 복장 제한이 명시되지 않았지만 과하게 캐주얼한 복장은 피하는 편이 안전합니다.'),
            ],
            [
                'icon' => '🌍',
                'question' => '외국인도 입장 가능한가요?',
                'answer' => $club->foreigner_allowed
                    ? '외국인 입장 가능으로 안내되는 장소입니다. 현장 상황에 따라 신분 확인이나 대기 여부는 달라질 수 있습니다.'
                    : '외국인 입장 가능 여부가 고정 안내되지 않았습니다. 방문 전 문의로 확인하는 것이 가장 안전합니다.',
            ],
            [
                'icon' => '🛋️',
                'question' => '테이블이나 입장 문의는 어떻게 하나요?',
                'answer' => '상세 하단 문의하기에서 인원, 방문 시간, 예산만 남기면 됩니다. ' . $contactLabel,
            ],
            [
                'icon' => '🕐',
                'question' => '몇 시쯤 가는 게 좋나요?',
                'answer' => $visitWindow
                    ? $visitWindow . ' 시간대를 우선 추천합니다.'
                    : ($club->open_time && $club->close_time
                        ? '운영 시간은 ' . $club->open_time . '부터 ' . $club->close_time . '까지입니다. 방문 희망 시간이 있으면 문의에서 바로 확인할 수 있습니다.'
                        : '고정 방문 추천 시간은 아직 준비중입니다. 원하는 입장 시간을 남기면 가능 여부를 확인해 드립니다.'),
            ],
            [
                'icon' => '💳',
                'question' => '비용과 결제는 어떻게 확인하나요?',
                'answer' => '현재 표시된 기준 가격은 ' . $club->price_text . '입니다. ' . $budgetGuide,
            ],
            [
                'icon' => '💬',
                'question' => '답변은 얼마나 걸리나요?',
                'answer' => $responseText . ' 수준으로 안내되고 있으며, ' . $responseHint,
            ],
            [
                'icon' => '📋',
                'question' => '방문 전에 꼭 확인할 점이 있나요?',
                'answer' => $club->guide_text
                    ?: '도착 시간, 인원, 원하는 테이블 여부를 함께 남기면 더 빠르게 안내받을 수 있습니다.',
            ],
        ];
    }

    public function forParty(Party $party, array $inquiryConversion = []): array
    {
        $visitWindow = $inquiryConversion['best_visit_window'] ?? null;
        $responseText = $inquiryConversion['response_time_text'] ?? '응답 데이터 준비중';
        $responseHint = $inquiryConversion['response_hint'] ?? '문의 접수 후 순차적으로 안내됩니다.';
        $budgetGuide = $inquiryConversion['budget_guide_text'] ?? '티켓/테이블 예산은 문의에서 최종 안내됩니다.';
        $contactLabel = (($inquiryConversion['assigned_md_count'] ?? 0) > 0)
            ? '현재 연결된 담당자를 통해 입장 가능 여부를 빠르게 확인할 수 있습니다.'
            : '문의 접수 후 운영팀이 순차적으로 확인합니다.';
        $dateText = $party->event_date?->format('Y.n.j') ?: '일정 확인 필요';
        $timeText = $party->start_time && $party->end_time
            ? $party->start_time . ' ~ ' . $party->end_time
            : ($party->start_time ?: '시간 안내 예정');

        return [
            [
                'icon' => '🎟️',
                'question' => '파티는 언제 진행되나요?',
                'answer' => $dateText . ' ' . $timeText . ' 기준으로 진행됩니다. 일정 변동이 필요한 경우 문의에서 최신 상태를 확인할 수 있습니다.',
            ],
            [
                'icon' => '👔',
                'question' => '드레스코드나 입장 조건이 있나요?',
                'answer' => $party->entry_condition
                    ?: ($party->dress_code
                        ? '기본 드레스코드는 ' . $party->dress_code . '입니다.'
                        : '별도 입장 조건이 명시되지 않았지만 행사 성격에 맞는 복장을 준비하는 편이 안전합니다.'),
            ],
            [
                'icon' => '🌍',
                'question' => '외국인도 입장 가능한가요?',
                'answer' => $party->club?->foreigner_allowed
                    ? '연결된 클럽은 외국인 입장 가능으로 안내됩니다. 행사별 현장 기준은 다를 수 있으니 문의로 한 번 더 확인하는 것이 좋습니다.'
                    : '외국인 입장 가능 여부가 고정 안내되지 않았습니다. 행사 당일 기준은 문의에서 확인할 수 있습니다.',
            ],
            [
                'icon' => '🛋️',
                'question' => '테이블이나 예매 문의는 어떻게 하나요?',
                'answer' => $party->booking_link
                    ? '공식 예매 링크를 우선 확인하고, 테이블/입장 조건은 문의하기로 추가 확인하면 됩니다. ' . $contactLabel
                    : '상세 하단 문의하기에서 인원, 방문 시간, 예산을 남기면 됩니다. ' . $contactLabel,
            ],
            [
                'icon' => '💳',
                'question' => '티켓 가격과 결제는 어떻게 확인하나요?',
                'answer' => '현재 표시된 기준 가격은 ' . $party->price_text . '입니다. ' . $budgetGuide,
            ],
            [
                'icon' => '🕐',
                'question' => '몇 시쯤 입장하는 게 좋나요?',
                'answer' => $visitWindow
                    ? $visitWindow . ' 시간대를 우선 추천합니다.'
                    : '행사 시작 시간은 ' . $timeText . '입니다. 대기나 혼잡 여부는 문의에서 바로 확인할 수 있습니다.',
            ],
            [
                'icon' => '💬',
                'question' => '답변은 얼마나 걸리나요?',
                'answer' => $responseText . ' 수준으로 안내되고 있으며, ' . $responseHint,
            ],
            [
                'icon' => '📋',
                'question' => '가기 전에 꼭 확인할 점이 있나요?',
                'answer' => $party->guide_text
                    ?: '도착 예정 시간, 인원, 티켓 여부를 함께 남기면 입장 가능 여부를 더 빠르게 확인할 수 있습니다.',
            ],
        ];
    }
}
