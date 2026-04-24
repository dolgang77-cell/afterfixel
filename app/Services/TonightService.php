<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Favorite;
use App\Models\Party;
use App\Models\RecentView;
use App\Models\UserPreference;
use App\Services\Tour\TravelTimeCalculator;
use App\Support\TimeDisplay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TonightService
{
    private TravelTimeCalculator $travel;
    private AvailabilitySignalService $availabilitySignals;

    public function __construct()
    {
        $this->travel = new TravelTimeCalculator();
        $this->availabilitySignals = app(AvailabilitySignalService::class);
    }

    // ═══════════════════════════════════════════════════════════
    //  시간대 판단
    // ═══════════════════════════════════════════════════════════

    public static function getCurrentTimeSlot(?string $time = null): array
    {
        $hour = (int) ($time ? substr($time, 0, 2) : now()->setTimezone('Asia/Seoul')->format('H'));
        $min  = (int) ($time ? substr($time, 3, 2) : now()->setTimezone('Asia/Seoul')->format('i'));
        $t    = $hour * 100 + $min;

        // 새벽(02:01~06:00)을 전날 밤으로 취급
        if ($t >= 200 && $t <= 600) {
            return [
                'slot'  => 'late_night',
                'label' => '늦은 밤',
                'emoji' => '🌙',
                'desc'  => '아직 운영 중인 곳을 찾아드립니다',
                'cta'   => '지금 갈 수 있는 곳 보기',
                'hour'  => $hour,
            ];
        }
        if ($t < 200 || $t >= 2330) {
            return [
                'slot'  => 'peak_late',
                'label' => '피크 타임',
                'emoji' => '🔥',
                'desc'  => '지금 입장 가능한 핫플을 추천합니다',
                'cta'   => '지금 입장 가능한 곳',
                'hour'  => $hour,
            ];
        }
        if ($t >= 2100) {
            return [
                'slot'  => 'peak',
                'label' => '지금 출발',
                'emoji' => '🚀',
                'desc'  => '지금 출발하기 딱 좋은 시간입니다',
                'cta'   => '지금 출발 추천 받기',
                'hour'  => $hour,
            ];
        }
        if ($t >= 1800) {
            return [
                'slot'  => 'early_evening',
                'label' => '오늘 저녁',
                'emoji' => '🌆',
                'desc'  => '오늘 밤 예정된 파티를 미리 확인하세요',
                'cta'   => '오늘밤 추천 보기',
                'hour'  => $hour,
            ];
        }
        // 낮 시간대
        return [
            'slot'  => 'daytime',
            'label' => '오늘 밤 미리보기',
            'emoji' => '☀️',
            'desc'  => '오늘 밤 일정을 미리 확인해보세요',
            'cta'   => '오늘밤 미리보기',
            'hour'  => $hour,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  시간 상태 판단 (카드 뱃지 동적 갱신용)
    // ═══════════════════════════════════════════════════════════

    public static function getTimeStatus(string $type, $entity): array
    {
        $now = now()->setTimezone('Asia/Seoul');
        $nowTime = (int) $now->format('Hi');

        if ($type === 'party') {
            [$startAt, $endAt] = self::partyWindow($entity);

            if ($now->betweenIncluded($startAt, $endAt)) {
                $minutesLeft = $now->diffInMinutes($endAt, false);
                if ($minutesLeft > 0 && $minutesLeft <= 60) {
                    return ['status' => 'closing_soon', 'label' => '마감 임박', 'color' => 'red'];
                }

                return ['status' => 'open', 'label' => '지금 입장 가능', 'color' => 'green'];
            }

            if ($now->gt($endAt)) {
                return ['status' => 'ended', 'label' => '종료', 'color' => 'gray'];
            }

            $minutesUntil = $now->diffInMinutes($startAt, false);
            if ($minutesUntil > 0 && $minutesUntil <= 60) {
                return ['status' => 'starting_soon', 'label' => '곧 시작', 'color' => 'orange'];
            }

            return ['status' => 'upcoming', 'label' => '오늘 시작', 'color' => 'blue'];
        }

        // Club
        if ($entity->isOpenAt(now()->setTimezone('Asia/Seoul')->format('H:i'))) {
            if ($entity->close_time) {
                $closeInt = (int) str_replace(':', '', $entity->close_time);
                $minutesLeft = self::staticMinutesBetween($nowTime, $closeInt);
                if ($minutesLeft > 0 && $minutesLeft <= 60) {
                    return ['status' => 'closing_soon', 'label' => '마감 임박', 'color' => 'red'];
                }
            }
            return ['status' => 'open', 'label' => '지금 입장 가능', 'color' => 'green'];
        }

        if ($entity->open_time) {
            $openInt = (int) str_replace(':', '', $entity->open_time);
            $minutesUntil = self::staticMinutesBetween($nowTime, $openInt);
            if ($minutesUntil > 0 && $minutesUntil <= 120) {
                return ['status' => 'opening_soon', 'label' => '곧 오픈', 'color' => 'orange'];
            }
        }

        return ['status' => 'closed', 'label' => '영업 전', 'color' => 'gray'];
    }

    private static function staticMinutesBetween(int $fromHHMM, int $toHHMM): int
    {
        $fromMin = intdiv($fromHHMM, 100) * 60 + ($fromHHMM % 100);
        $toMin   = intdiv($toHHMM, 100) * 60 + ($toHHMM % 100);
        $diff = $toMin - $fromMin;
        if ($diff < -360) $diff += 1440;
        return $diff;
    }

    private static function partyWindow($party): array
    {
        $eventDate = $party->event_date
            ? Carbon::parse($party->event_date)->setTimezone('Asia/Seoul')->toDateString()
            : now()->setTimezone('Asia/Seoul')->toDateString();
        $startTime = $party->start_time ?: '22:00';
        $endTime = $party->end_time ?: '05:00';

        $startAt = Carbon::parse($eventDate . ' ' . $startTime, 'Asia/Seoul');
        $endAt = Carbon::parse($eventDate . ' ' . $endTime, 'Asia/Seoul');

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt->addDay();
        }

        return [$startAt, $endAt];
    }

    // ═══════════════════════════════════════════════════════════
    //  파티 스코어링
    // ═══════════════════════════════════════════════════════════

    public function scoreTonightParty(Party $party, array $timeSlot, ?UserPreference $pref, array $context = []): array
    {
        $score   = 0;
        $reasons = [];
        $labels  = [];
        $now     = now()->setTimezone('Asia/Seoul');
        $nowTime = (int) $now->format('Hi');

        [$startAt, $endAt] = self::partyWindow($party);
        $minutesUntilStart = $now->diffInMinutes($startAt, false);

        if ($now->betweenIncluded($startAt, $endAt)) {
            $labels[] = ['text' => '지금 입장 가능', 'color' => 'green'];
            $score += 30;
            $reasons[] = '현재 입장 가능한 파티';

            // 마감 임박 체크
            $minutesUntilEnd = $now->diffInMinutes($endAt, false);
            if ($minutesUntilEnd > 0 && $minutesUntilEnd <= 90) {
                $labels[] = ['text' => '마감 임박', 'color' => 'red'];
                $score -= 10;
            }
        } elseif ($minutesUntilStart <= 60) {
            $labels[] = ['text' => '곧 시작', 'color' => 'orange'];
            $score += 25;
            $reasons[] = "{$minutesUntilStart}분 후 시작";
        } elseif ($minutesUntilStart <= 180) {
            $labels[] = ['text' => '오늘 시작', 'color' => 'blue'];
            $score += 15;
            $reasons[] = "{$party->start_time} 시작 예정";
        } else {
            $labels[] = ['text' => '오늘 시작', 'color' => 'blue'];
            $score += 5;
            $reasons[] = "{$party->start_time} 시작 예정";
        }

        // 시간대 가중치: 늦은 밤일수록 현재 입장 가능 비중 증가
        if (in_array($timeSlot['slot'], ['peak_late', 'late_night'])) {
            $isOpen = collect($labels)->contains('text', '지금 입장 가능');
            if ($isOpen) {
                $score += 15; // 늦은 시간 입장 가능 추가 보너스
            }
        }

        // 요일 보너스 (금/토)
        $dayOfWeek = $now->dayOfWeek;
        if (in_array($dayOfWeek, [5, 6])) { // 금(5), 토(6)
            $score += 5;
            if (!in_array('주말 파티', $reasons)) {
                $reasons[] = '주말 파티';
            }
        }

        // 평점/인기 보너스
        if ($party->club && $party->club->rating_avg >= 4.0) {
            $score += 10;
            $reasons[] = '후기 반응이 좋은 클럽';
            $labels[] = ['text' => '인기 높음', 'color' => 'yellow'];
        }

        // 예산 적합성
        if ($pref && $pref->budget_max && $party->ticket_price_min) {
            if ($party->ticket_price_min <= $pref->budget_max) {
                $score += 8;
                $labels[] = ['text' => '예산 여유', 'color' => 'green'];
                $reasons[] = '예산 범위 내 입장 가능';
            }
        }

        // 장르 매칭
        if ($pref && !empty($pref->preferred_genres) && $party->genre) {
            if (in_array($party->genre, $pref->preferred_genres)) {
                $score += 12;
                $reasons[] = "선호 장르 {$party->genre} 매칭";
            }
        }

        // 지역 매칭
        if ($pref && !empty($pref->preferred_areas) && $party->club) {
            if (in_array($party->club->area, $pref->preferred_areas)) {
                $score += 10;
                $reasons[] = "관심 지역 {$party->club->area}";
            }
        }

        // 외국인 모드
        if ($pref && $pref->foreigner_mode && $party->club) {
            if ($party->club->foreigner_allowed) {
                $score += 5;
                $labels[] = ['text' => '외국인 가능', 'color' => 'blue'];
            } else {
                $score -= 20;
            }
        }

        // 찜한 파티 보너스
        if (!empty($context['favorite_party_ids']) && in_array($party->id, $context['favorite_party_ids'])) {
            $score += 8;
            $reasons[] = '찜한 파티';
        }

        // 최근 본 클럽의 파티 보너스
        if (!empty($context['recent_club_ids']) && $party->club_id && in_array($party->club_id, $context['recent_club_ids'])) {
            $score += 6;
            $reasons[] = '최근 본 클럽의 파티';
        }

        // 이동 시간 (사용자 관심 지역 기준)
        $travelMin = null;
        if ($pref && !empty($pref->preferred_areas) && $party->club) {
            $userArea = $pref->preferred_areas[0];
            $travelMin = $this->travel->between($userArea, $party->club->area);
            if ($travelMin <= 10) {
                $score += 5;
                $labels[] = ['text' => '이동 짧음', 'color' => 'cyan'];
                $reasons[] = '가까운 거리';
            } elseif ($travelMin <= 20) {
                $labels[] = ['text' => '지금 출발 추천', 'color' => 'cyan'];
                $reasons[] = "약 {$travelMin}분 이동";
            }
        }

        $labels[] = [
            'text' => $party->event_card_label,
            'color' => $party->is_verified_event ? 'green' : ($party->is_operating_card ? 'cyan' : 'blue'),
        ];
        $reasons[] = $party->event_card_notice;

        // CTA 문구 결정
        $cta = $this->buildItemCta($labels);

        return [
            'type'       => 'party',
            'entity'     => $party,
            'score'      => max(0, $score),
            'labels'     => $labels,
            'reasons'    => $reasons,
            'travel_min' => $travelMin,
            'cost'       => $party->ticket_price_min,
            'cost_text'  => $party->ticket_price_min ? number_format($party->ticket_price_min) . '원~' : '무료/미정',
            'cta'        => $cta,
            'link'       => route('parties.show', $party),
            'time_display' => TimeDisplay::range($party->start_time, $party->end_time),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  클럽 스코어링
    // ═══════════════════════════════════════════════════════════

    public function scoreTonightClub(Club $club, array $timeSlot, ?UserPreference $pref, array $context = []): array
    {
        $score   = 0;
        $reasons = [];
        $labels  = [];
        $nowTime = now()->setTimezone('Asia/Seoul')->format('H:i');

        $isOpen = $club->isOpenAt($nowTime);

        if ($isOpen) {
            $labels[] = ['text' => '지금 입장 가능', 'color' => 'green'];
            $score += 25;
            $reasons[] = '현재 영업 중';

            // 마감 임박
            if ($club->close_time) {
                $closeInt = (int) str_replace(':', '', $club->close_time);
                $nowInt = (int) str_replace(':', '', $nowTime);
                $minutesLeft = $this->minutesBetween($nowInt, $closeInt);
                if ($minutesLeft > 0 && $minutesLeft <= 90) {
                    $labels[] = ['text' => '마감 임박', 'color' => 'red'];
                    $score -= 5;
                }
            }
        } else {
            // 곧 오픈
            if ($club->open_time) {
                $openInt = (int) str_replace(':', '', $club->open_time);
                $nowInt = (int) str_replace(':', '', $nowTime);
                $minutesUntil = $this->minutesBetween($nowInt, $openInt);
                if ($minutesUntil > 0 && $minutesUntil <= 120) {
                    $labels[] = ['text' => '곧 오픈', 'color' => 'orange'];
                    $score += 10;
                    $reasons[] = "{$club->open_time} 오픈 예정";
                } else {
                    $score -= 30;
                }
            }
        }

        // 시간대 가중치
        if (in_array($timeSlot['slot'], ['peak_late', 'late_night'])) {
            if ($isOpen) {
                $score += 15;
                $reasons[] = '늦은 시간에도 영업 중';
            }
        }

        // 요일 보너스 (금/토)
        $dayOfWeek = now()->dayOfWeek;
        if (in_array($dayOfWeek, [5, 6])) {
            $score += 3;
        }

        // 평점
        if ($club->rating_avg >= 4.0) {
            $score += 10;
            $labels[] = ['text' => '인기 높음', 'color' => 'yellow'];
            $reasons[] = "평점 {$club->rating_avg}점";
        }

        // 예산
        if ($pref && $pref->budget_max && $club->entry_fee_min) {
            if ($club->entry_fee_min <= $pref->budget_max) {
                $score += 8;
                $labels[] = ['text' => '예산 여유', 'color' => 'green'];
                $reasons[] = '예산 범위 내';
            }
        }

        // 장르
        if ($pref && !empty($pref->preferred_genres)) {
            if (in_array($club->genre, $pref->preferred_genres)) {
                $score += 12;
                $reasons[] = "선호 장르 {$club->genre}";
            }
        }

        // 지역
        if ($pref && !empty($pref->preferred_areas)) {
            if (in_array($club->area, $pref->preferred_areas)) {
                $score += 10;
                $reasons[] = "관심 지역 {$club->area}";
            }
        }

        // 외국인
        if ($pref && $pref->foreigner_mode) {
            if ($club->foreigner_allowed) {
                $score += 5;
                $labels[] = ['text' => '외국인 가능', 'color' => 'blue'];
            } else {
                $score -= 20;
            }
        }

        // 찜 보너스
        if (!empty($context['favorite_club_ids']) && in_array($club->id, $context['favorite_club_ids'])) {
            $score += 8;
            $reasons[] = '찜한 클럽';
        }

        // 최근 본 보너스
        if (!empty($context['recent_club_ids']) && in_array($club->id, $context['recent_club_ids'])) {
            $score += 4;
            $reasons[] = '최근 관심을 보인 클럽';
        }

        // 이동 시간
        $travelMin = null;
        if ($pref && !empty($pref->preferred_areas)) {
            $travelMin = $this->travel->between($pref->preferred_areas[0], $club->area);
            if ($travelMin <= 10) {
                $score += 5;
                $labels[] = ['text' => '이동 짧음', 'color' => 'cyan'];
            } elseif ($travelMin <= 20) {
                $labels[] = ['text' => '지금 출발 추천', 'color' => 'cyan'];
            }
        }

        $cta = $this->buildItemCta($labels);

        return [
            'type'       => 'club',
            'entity'     => $club,
            'score'      => max(0, $score),
            'labels'     => $labels,
            'reasons'    => $reasons,
            'travel_min' => $travelMin,
            'cost'       => $club->entry_fee_min,
            'cost_text'  => $club->entry_fee_min ? number_format($club->entry_fee_min) . '원~' : '무료/미정',
            'cta'        => $cta,
            'link'       => route('clubs.show', $club),
            'time_display' => TimeDisplay::range($club->open_time, $club->close_time),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  CTA 문구 빌드
    // ═══════════════════════════════════════════════════════════

    private function buildItemCta(array $labels): string
    {
        $texts = array_column($labels, 'text');

        if (in_array('지금 입장 가능', $texts)) return '지금 바로 가기';
        if (in_array('곧 시작', $texts)) return '출발 준비하기';
        if (in_array('곧 오픈', $texts)) return '오픈 알림 받기';
        if (in_array('마감 임박', $texts)) return '서둘러 가기';
        return '자세히 보기';
    }

    // ═══════════════════════════════════════════════════════════
    //  섹션 빌드 (시간대별 분류)
    // ═══════════════════════════════════════════════════════════

    public function buildTonightSections(
        ?string $sessionId = null,
        ?UserPreference $pref = null,
        ?string $area = null,
        ?string $genre = null,
        ?int $budget = null,
    ): array {
        $timeSlot = self::getCurrentTimeSlot();

        // 데이터 로드
        $todayParties = Party::with('club')
            ->where('event_date', today())
            ->where('status', '!=', 'cancelled')
            ->get();

        $activeClubs = Club::active()->get();

        // 컨텍스트 구성
        $context = [];
        if ($sessionId) {
            $context['favorite_party_ids'] = Favorite::forSession($sessionId)->ofType('party')->pluck('target_id')->toArray();
            $context['favorite_club_ids'] = Favorite::forSession($sessionId)->ofType('club')->pluck('target_id')->toArray();
            $context['recent_club_ids'] = RecentView::forSession($sessionId)->ofType('club')->pluck('target_id')->toArray();
            $context['recent_party_ids'] = RecentView::forSession($sessionId)->ofType('party')->pluck('target_id')->toArray();
        }

        // 필터 적용
        if ($area) {
            $todayParties = $todayParties->filter(fn($p) => $p->club?->area === $area);
            $activeClubs = $activeClubs->filter(fn($c) => $c->area === $area);
        }
        if ($genre) {
            $todayParties = $todayParties->filter(fn($p) => $p->genre === $genre);
            $activeClubs = $activeClubs->filter(fn($c) => $c->genre === $genre);
        }

        // 임시 pref (필터 오버라이드)
        $effectivePref = $pref;
        if ($budget && $effectivePref) {
            $effectivePref = clone $effectivePref;
            $effectivePref->budget_max = $budget;
        } elseif ($budget && !$effectivePref) {
            $effectivePref = new UserPreference();
            $effectivePref->budget_max = $budget;
        }

        // 스코어링
        $scoredParties = $todayParties->map(fn($p) => $this->scoreTonightParty($p, $timeSlot, $effectivePref, $context))
            ->sortByDesc('score')
            ->values();

        $scoredClubs = $activeClubs->map(fn($c) => $this->scoreTonightClub($c, $timeSlot, $effectivePref, $context))
            ->filter(fn($s) => $s['score'] > 0)
            ->sortByDesc('score')
            ->values();

        $signals = $this->availabilitySignals->forTargets(array_merge(
            $todayParties->mapWithKeys(fn ($party) => [
                'party:' . $party->id => [
                    'type' => 'party',
                    'id' => $party->id,
                    'start_time' => $party->start_time,
                ],
            ])->all(),
            $activeClubs->mapWithKeys(fn ($club) => [
                'club:' . $club->id => [
                    'type' => 'club',
                    'id' => $club->id,
                    'open_time' => $club->open_time,
                    'close_time' => $club->close_time,
                ],
            ])->all()
        ));

        $scoredParties = $scoredParties->map(fn (array $item) => $this->mergeAvailabilitySignals($item, $signals['party:' . $item['entity']->id] ?? null));
        $scoredClubs = $scoredClubs->map(fn (array $item) => $this->mergeAvailabilitySignals($item, $signals['club:' . $item['entity']->id] ?? null));

        // 섹션 구성 (시간대에 따라 우선순위 변경)
        $sections = [];

        switch ($timeSlot['slot']) {
            case 'early_evening':
                $sections[] = $this->buildSection('오늘 시작 예정 파티', '🎉', $scoredParties->take(6), 'party', '시작 시간이 다가오고 있어요');
                $sections[] = $this->buildSection('오늘 밤 추천 클럽', '🏢', $scoredClubs->take(4), 'club', '곧 문을 여는 클럽이에요');
                break;

            case 'peak':
                $nowOpen = $scoredParties->filter(fn($s) => collect($s['labels'])->contains('text', '지금 입장 가능'));
                $soonStart = $scoredParties->filter(fn($s) => collect($s['labels'])->contains('text', '곧 시작'));
                $sections[] = $this->buildSection('지금 출발하기 좋은 파티', '🚀', $soonStart->merge($nowOpen)->take(6), 'party', '지금 나서면 딱 좋은 타이밍');
                $sections[] = $this->buildSection('지금 입장 가능한 클럽', '🏢', $scoredClubs->filter(fn($s) => collect($s['labels'])->contains('text', '지금 입장 가능'))->take(4), 'club', '바로 입장할 수 있어요');
                break;

            case 'peak_late':
                $nowOpen = $scoredParties->filter(fn($s) => collect($s['labels'])->contains('text', '지금 입장 가능'));
                $sections[] = $this->buildSection('지금 입장 가능한 파티', '🔥', $nowOpen->take(6), 'party', '아직 입장 가능해요');
                $sections[] = $this->buildSection('아직 영업 중인 클럽', '🌙', $scoredClubs->filter(fn($s) => collect($s['labels'])->contains('text', '지금 입장 가능'))->take(4), 'club', '지금 가도 즐길 수 있어요');
                break;

            case 'late_night':
                $stillOpen = $scoredClubs->filter(fn($s) => collect($s['labels'])->contains('text', '지금 입장 가능'));
                $sections[] = $this->buildSection('아직 갈 수 있는 곳', '🌙', $stillOpen->take(6), 'club', '늦은 시간에도 운영 중이에요');
                // 라운지형 / 바이브 좋은 곳 추천
                $loungeClubs = $scoredClubs->filter(fn($s) => in_array($s['entity']->vibe, ['라운지', '칵테일', '감성', '재즈바']) || $s['entity']->genre === '재즈');
                if ($loungeClubs->isNotEmpty()) {
                    $sections[] = $this->buildSection('늦은 밤 감성 라운지', '🍸', $loungeClubs->take(3), 'club', '분위기 좋은 곳에서 마무리');
                }
                break;

            default: // daytime
                $sections[] = $this->buildSection('오늘 밤 예정 파티', '🎉', $scoredParties->take(6), 'party', '오늘 저녁부터 시작되는 파티');
                $sections[] = $this->buildSection('인기 클럽 미리보기', '🔥', $scoredClubs->take(4), 'club', '오늘 밤 가볼만한 클럽');
        }

        // ── 카테고리별 추가 섹션 ──

        // 예산 추천
        if ($effectivePref && $effectivePref->budget_max) {
            $budgetFit = $scoredParties->merge($scoredClubs)
                ->filter(fn($s) => collect($s['labels'])->contains('text', '예산 여유'))
                ->sortByDesc('score')
                ->take(4);
            if ($budgetFit->isNotEmpty()) {
                $sections[] = $this->buildSection(
                    number_format($effectivePref->budget_max) . '원 이하 추천',
                    '💰', $budgetFit, 'mixed',
                    '예산에 맞는 곳만 모았어요'
                );
            }
        }

        // 관심 지역 기반 추천
        if ($pref && !empty($pref->preferred_areas) && !$area) {
            $areaMatched = $scoredParties->merge($scoredClubs)
                ->filter(fn($s) => in_array('관심 지역', array_map(fn($r) => explode(' ', $r)[0] ?? '', $s['reasons'] ?? [])) ||
                    collect($s['reasons'])->contains(fn($r) => str_contains($r, '관심 지역')))
                ->sortByDesc('score')
                ->take(4);
            if ($areaMatched->isNotEmpty()) {
                $areaLabel = implode('·', array_slice($pref->preferred_areas, 0, 2));
                $sections[] = $this->buildSection(
                    "{$areaLabel} 추천",
                    '📍', $areaMatched, 'mixed',
                    "자주 찾는 {$areaLabel} 근처 추천"
                );
            }
        }

        // 최근 본 항목과 비슷한 추천
        if (!empty($context['recent_club_ids'])) {
            $recentClubGenres = Club::whereIn('id', array_slice($context['recent_club_ids'], 0, 5))
                ->pluck('genre')->filter()->unique()->toArray();
            if (!empty($recentClubGenres)) {
                $similar = $scoredParties->merge($scoredClubs)
                    ->filter(fn($s) => in_array($s['entity']->genre ?? ($s['entity']->club->genre ?? ''), $recentClubGenres))
                    ->sortByDesc('score')
                    ->take(4);
                if ($similar->count() >= 2) {
                    $sections[] = $this->buildSection(
                        '최근 관심과 비슷한 추천',
                        '✨', $similar, 'mixed',
                        '최근 둘러본 곳과 비슷한 분위기'
                    );
                }
            }
        }

        // 외국인 모드 우선 추천
        if ($pref && $pref->foreigner_mode) {
            $foreignerFriendly = $scoredParties->merge($scoredClubs)
                ->filter(fn($s) => collect($s['labels'])->contains('text', '외국인 가능'))
                ->sortByDesc('score')
                ->take(4);
            if ($foreignerFriendly->isNotEmpty()) {
                $sections[] = $this->buildSection(
                    'Foreigner Friendly',
                    '🌍', $foreignerFriendly, 'mixed',
                    '외국인 입장이 가능한 곳'
                );
            }
        }

        // 빈 섹션 제거
        $sections = array_values(array_filter($sections, fn($s) => !empty($s['items'])));

        // 폴백
        $fallback = null;
        if (empty($sections)) {
            $fallback = $this->buildFallbackSuggestions($area, $genre, $budget, $timeSlot);
        }

        return [
            'timeSlot' => $timeSlot,
            'sections' => $sections,
            'fallback' => $fallback,
            'total'    => collect($sections)->sum(fn($s) => count($s['items'])),
            'filters'  => [
                'area' => $area,
                'genre' => $genre,
                'budget' => $budget,
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  추천 이유 생성
    // ═══════════════════════════════════════════════════════════

    public static function generateTonightReason(array $scored, ?UserPreference $pref): string
    {
        $reasons = $scored['reasons'] ?? [];
        if (empty($reasons)) return '오늘 밤 추천';

        // 개인화 이유 우선
        $personal = array_filter($reasons, fn($r) =>
            str_contains($r, '선호') || str_contains($r, '관심') ||
            str_contains($r, '찜') || str_contains($r, '최근'));
        if (!empty($personal)) {
            return implode(' · ', array_slice(array_values($personal), 0, 2));
        }

        // 시간 + 상태 이유
        $timeReasons = array_filter($reasons, fn($r) =>
            str_contains($r, '입장') || str_contains($r, '시작') ||
            str_contains($r, '영업') || str_contains($r, '출발'));
        if (!empty($timeReasons)) {
            return implode(' · ', array_slice(array_values($timeReasons), 0, 2));
        }

        return implode(' · ', array_slice($reasons, 0, 2));
    }

    // ═══════════════════════════════════════════════════════════
    //  개인화 추천 문구 생성
    // ═══════════════════════════════════════════════════════════

    public static function generatePersonalizedSummary(?UserPreference $pref, array $timeSlot): string
    {
        $parts = [];

        if ($pref && !empty($pref->preferred_areas)) {
            $parts[] = implode('·', array_slice($pref->preferred_areas, 0, 2));
        }
        if ($pref && !empty($pref->preferred_genres)) {
            $parts[] = implode('·', array_slice($pref->preferred_genres, 0, 2));
        }
        if ($pref && $pref->budget_max) {
            $parts[] = number_format($pref->budget_max) . '원 이하';
        }

        if (empty($parts)) {
            return $timeSlot['desc'];
        }

        return implode(', ', $parts) . ' 기준 맞춤 추천';
    }

    // ═══════════════════════════════════════════════════════════
    //  폴백 제안
    // ═══════════════════════════════════════════════════════════

    public function buildFallbackSuggestions(?string $area, ?string $genre, ?int $budget, array $timeSlot): array
    {
        $suggestions = [];

        if ($area) {
            $suggestions[] = [
                'text' => '다른 지역도 살펴보기',
                'icon' => '📍',
                'action' => 'expand_area',
                'link' => route('tonight.index'),
            ];
        }

        if ($genre) {
            $suggestions[] = [
                'text' => '모든 장르로 검색',
                'icon' => '🎵',
                'action' => 'expand_genre',
                'link' => route('tonight.index', ['area' => $area]),
            ];
        }

        if ($budget) {
            $suggestions[] = [
                'text' => '예산 범위 넓히기',
                'icon' => '💰',
                'action' => 'expand_budget',
                'link' => route('tonight.index', ['area' => $area, 'genre' => $genre]),
            ];
        }

        // 내일 파티 미리보기
        $tomorrowCount = Party::where('event_date', today()->addDay())
            ->where('status', '!=', 'cancelled')
            ->count();
        if ($tomorrowCount > 0) {
            $suggestions[] = [
                'text' => "내일 파티 {$tomorrowCount}개 미리보기",
                'icon' => '📅',
                'action' => 'tomorrow',
                'link' => route('parties.index', ['date' => today()->addDay()->toDateString()]),
            ];
        }

        $suggestions[] = [
            'text' => 'AI 투어 추천 받기',
            'icon' => '🤖',
            'action' => 'tour',
            'link' => route('tour.index'),
        ];

        // 시간대에 따른 제안
        if ($timeSlot['slot'] === 'late_night') {
            $suggestions[] = [
                'text' => '내일 파티 미리 찜하기',
                'icon' => '❤️',
                'action' => 'tomorrow_fav',
                'link' => route('parties.index'),
            ];
        }

        return $suggestions;
    }

    // ═══════════════════════════════════════════════════════════
    //  홈용 요약 (상위 N개만)
    // ═══════════════════════════════════════════════════════════

    public function getHomeTonightSummary(?string $sessionId, ?UserPreference $pref): array
    {
        $result = $this->buildTonightSections($sessionId, $pref);
        $timeSlot = $result['timeSlot'];

        // 모든 섹션에서 상위 6개만
        $topItems = collect();
        foreach ($result['sections'] as $section) {
            foreach ($section['items'] as $item) {
                $topItems->push($item);
            }
        }

        $topItems = $topItems->sortByDesc('score')->unique(fn($item) =>
            $item['type'] . '-' . $item['entity']->id
        )->take(6)->values();

        $personalSummary = self::generatePersonalizedSummary($pref, $timeSlot);

        return [
            'timeSlot'        => $timeSlot,
            'items'           => $topItems,
            'total'           => $result['total'],
            'personalSummary' => $personalSummary,
            'sections'        => array_slice($result['sections'], 0, 2), // 홈에는 상위 2개 섹션만
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  빠른 추천 (Quick Recommend) - 최소 입력 기반
    // ═══════════════════════════════════════════════════════════

    public function quickRecommend(
        ?string $sessionId,
        ?UserPreference $pref,
        ?string $area = null,
        ?string $genre = null,
        ?int $budget = null,
    ): array {
        // 사용자 설정에서 기본값 가져오기
        $area   = $area ?? ($pref?->preferred_areas[0] ?? null);
        $budget = $budget ?? $pref?->budget_max;

        $result = $this->buildTonightSections($sessionId, $pref, $area, $genre, $budget);

        // 추천 결과가 없으면 필터 완화
        if (empty($result['sections'])) {
            // 1차 완화: 장르 제거
            if ($genre) {
                $result = $this->buildTonightSections($sessionId, $pref, $area, null, $budget);
            }
            // 2차 완화: 지역도 제거
            if (empty($result['sections']) && $area) {
                $result = $this->buildTonightSections($sessionId, $pref, null, null, $budget);
            }
            // 3차 완화: 전체 검색
            if (empty($result['sections'])) {
                $result = $this->buildTonightSections($sessionId, $pref);
            }
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    //  상태 요약 (대시보드/홈 위젯용)
    // ═══════════════════════════════════════════════════════════

    public function getStatusSummary(): array
    {
        $timeSlot = self::getCurrentTimeSlot();
        $now = now();

        $todayPartyCount = Party::where('event_date', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        $openClubCount = Club::active()->get()->filter(fn($c) => $c->isOpenAt($now->format('H:i')))->count();

        $upcomingSoonCount = Party::where('event_date', today())
            ->where('status', '!=', 'cancelled')
            ->get()
            ->filter(function ($p) use ($now) {
                if (!$p->start_time) return false;
                $startInt = (int) str_replace(':', '', $p->start_time);
                $nowInt = (int) $now->format('Hi');
                $minutes = self::staticMinutesBetween($nowInt, $startInt);
                return $minutes > 0 && $minutes <= 120;
            })
            ->count();

        return [
            'timeSlot'          => $timeSlot,
            'todayPartyCount'   => $todayPartyCount,
            'openClubCount'     => $openClubCount,
            'upcomingSoonCount' => $upcomingSoonCount,
            'dayOfWeek'         => $now->locale('ko')->dayName,
            'isWeekend'         => in_array($now->dayOfWeek, [5, 6, 0]),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  내부 헬퍼
    // ═══════════════════════════════════════════════════════════

    private function buildSection(string $title, string $emoji, Collection $items, string $type, string $desc = ''): array
    {
        return [
            'title' => $title,
            'emoji' => $emoji,
            'type'  => $type,
            'desc'  => $desc,
            'items' => $items->values()->toArray(),
        ];
    }

    private function mergeAvailabilitySignals(array $item, ?array $signals): array
    {
        if (!$signals) {
            return $item;
        }

        $item['availability_signal'] = $signals['availability_signal'] ?? null;
        $item['crowd_signal'] = $signals['crowd_signal'] ?? null;
        $item['best_visit_window'] = $signals['best_visit_window'] ?? null;
        $item['availability_summary'] = $signals['availability_summary'] ?? null;
        $item['availability_score'] = $signals['availability_score'] ?? null;
        $item['crowd_score'] = $signals['crowd_score'] ?? null;

        $labels = collect($item['labels'] ?? []);
        foreach (['availability_signal', 'crowd_signal'] as $field) {
            $signal = $signals[$field] ?? null;
            if (!$signal) {
                continue;
            }

            $exists = $labels->contains(fn ($label) => ($label['text'] ?? null) === $signal['label']);
            if (!$exists) {
                $labels->push($signal);
            }
        }

        $item['labels'] = $labels->take(4)->values()->all();

        if (!empty($signals['best_visit_window'])) {
            $item['reasons'][] = $signals['best_visit_window'];
        }

        return $item;
    }

    private function minutesBetween(int $fromHHMM, int $toHHMM): int
    {
        return self::staticMinutesBetween($fromHHMM, $toHHMM);
    }
}
