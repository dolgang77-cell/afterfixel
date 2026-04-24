<?php

namespace App\Models;

use App\Models\Traits\HasPriceRange;
use App\Models\Traits\HasThumbnail;
use App\Models\Traits\HasViewCount;
use App\Support\CuratedNightlifeData;
use App\Support\TimeDisplay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Party extends Model
{
    use HasFactory, HasPriceRange, HasThumbnail, HasViewCount;

    protected string $priceMinField = 'ticket_price_min';
    protected string $priceMaxField = 'ticket_price_max';
    protected string $defaultThumbnail = '/images/placeholders/party-thumbnail.svg';

    protected $fillable = [
        'club_id', 'name', 'slug', 'event_date', 'start_time', 'end_time',
        'lineup', 'ticket_price_min', 'ticket_price_max', 'genre', 'tags',
        'booking_link', 'dress_code', 'entry_condition', 'description',
        'short_description', 'full_description', 'intro_title', 'guide_text', 'highlight_tags',
        'images', 'thumbnail', 'status', 'view_count', 'sort_order',
    ];

    protected $casts = [
        'event_date'       => 'date',
        'tags'             => 'array',
        'highlight_tags'   => 'array',
        'images'           => 'array',
        'ticket_price_min' => 'integer',
        'ticket_price_max' => 'integer',
    ];

    // ── 관계 ──
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function mdProfiles()
    {
        return $this->belongsToMany(MdProfile::class, 'md_party')
            ->withPivot('visible', 'priority', 'note')
            ->withTimestamps();
    }

    public function activeMds()
    {
        return $this->mdProfiles()
            ->wherePivot('visible', true)
            ->where('md_profiles.status', 'active')
            ->where('md_profiles.visible', true)
            ->orderBy('md_party.priority');
    }

    public function reviews()
    {
        return Review::forTarget('party', $this->id)->visible()->with('user')->latest();
    }

    public function approvedMedia()
    {
        return Media::forOwner('party', $this->id)->public()->orderBy('sort_order');
    }

    // ── 별칭: 기존 ticket_price_text -> price_text 통합, 하위호환 유지 ──
    public function getTicketPriceTextAttribute(): string
    {
        return $this->price_text;
    }

    public function getTimeRangeTextAttribute(): string
    {
        return TimeDisplay::range($this->start_time, $this->end_time) ?? '시간 안내 예정';
    }

    // ── 접근자 ──
    public function getIsUpcomingAttribute(): bool
    {
        return $this->event_date >= now()->toDateString();
    }

    public function getIsVerifiedEventAttribute(): bool
    {
        return $this->matchesGeneratedPrefix(CuratedNightlifeData::specialPartyPrefixes());
    }

    public function getIsOperatingCardAttribute(): bool
    {
        return $this->matchesGeneratedPrefix(CuratedNightlifeData::rollingPartyPrefixes());
    }

    public function getEventCardTypeAttribute(): string
    {
        if ($this->is_verified_event) {
            return 'verified_event';
        }

        if ($this->is_operating_card) {
            return 'operating_card';
        }

        return 'general_event';
    }

    public function getEventCardLabelAttribute(): string
    {
        return match ($this->event_card_type) {
            'verified_event' => '실이벤트',
            'operating_card' => '운영형 카드',
            default => '일반 이벤트',
        };
    }

    public function getEventCardVariantAttribute(): string
    {
        return match ($this->event_card_type) {
            'verified_event' => 'green',
            'operating_card' => 'cyan',
            default => 'default',
        };
    }

    public function getEventCardNoticeAttribute(): string
    {
        return match ($this->event_card_type) {
            'verified_event' => '외부 이벤트 공지나 공식 일정이 확인된 실제 행사 카드입니다.',
            'operating_card' => '특정 게스트나 회차가 확정된 실이벤트가 아니라, 공식 운영시간·예약 가이드·최근 운영 패턴을 바탕으로 만든 대표 세션 카드입니다.',
            default => '관리자 또는 제휴 경로에서 등록된 일반 일정 카드입니다.',
        };
    }

    // ── 스코프 ──
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', today())
                     ->where('status', '!=', 'cancelled');
    }

    public function scopeOnDate($query, ?string $date)
    {
        return $date ? $query->where('event_date', $date) : $query;
    }

    public function scopeInArea($query, ?string $area)
    {
        return $area
            ? $query->whereHas('club', fn($q) => $q->where('area', $area))
            : $query;
    }

    public function scopeInGenre($query, ?string $genre)
    {
        return $genre ? $query->where('genre', $genre) : $query;
    }

    public function scopeToday($query)
    {
        return $query->where('event_date', today());
    }

    private function matchesGeneratedPrefix(array $prefixes): bool
    {
        if (!is_string($this->slug) || $this->slug === '') {
            return false;
        }

        foreach ($prefixes as $prefix) {
            if (Str::startsWith($this->slug, $prefix . '-')) {
                return true;
            }
        }

        return false;
    }
}
