<?php

namespace App\Models;

use App\Models\Traits\HasPriceRange;
use App\Models\Traits\HasThumbnail;
use App\Models\Traits\HasViewCount;
use App\Support\TimeDisplay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Club extends Model
{
    use HasFactory, HasPriceRange, HasThumbnail, HasViewCount;

    protected string $priceMinField = 'entry_fee_min';
    protected string $priceMaxField = 'entry_fee_max';
    protected string $defaultThumbnail = '/images/placeholders/club-thumbnail.svg';

    protected $fillable = [
        'name', 'slug', 'area', 'genre', 'subgenre', 'vibe',
        'open_time', 'close_time', 'entry_fee_min', 'entry_fee_max',
        'foreigner_allowed', 'dress_code', 'dress_code_detail',
        'lat', 'lng', 'address', 'phone', 'instagram',
        'rating_avg', 'rating_count', 'rating_summary',
        'description', 'short_description', 'full_description',
        'intro_title', 'guide_text', 'highlight_tags',
        'images', 'thumbnail', 'tags',
        'is_active', 'sort_order', 'view_count',
    ];

    protected $casts = [
        'images'            => 'array',
        'tags'              => 'array',
        'highlight_tags'    => 'array',
        'foreigner_allowed' => 'boolean',
        'is_active'         => 'boolean',
        'entry_fee_min'     => 'integer',
        'entry_fee_max'     => 'integer',
        'rating_avg'        => 'float',
    ];

    // ── 상수 ──
    public static array $areas  = [
        '홍대', '이태원', '강남', '압구정', '성수', '종로', '신촌', '건대',
        '부산 서면', '부산 광안리', '영종도', '대구 동성로', '대전 둔산', '광주 상무지구', '제주시', '중문',
    ];
    public static array $genres = ['힙합', 'EDM', 'R&B', '테크노', '하우스', '팝', '케이팝', '재즈', '록'];

    // ── 관계 ──
    public function parties()
    {
        return $this->hasMany(Party::class);
    }

    public function mdProfiles()
    {
        return $this->belongsToMany(MdProfile::class, 'md_club')
            ->withPivot('visible', 'priority', 'note')
            ->withTimestamps();
    }

    public function activeMds()
    {
        return $this->mdProfiles()
            ->wherePivot('visible', true)
            ->where('md_profiles.status', 'active')
            ->where('md_profiles.visible', true)
            ->orderBy('md_club.priority');
    }

    public function upcomingParties()
    {
        return $this->parties()
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(5);
    }

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function recentReviews(int $limit = 5)
    {
        return $this->communityPosts()
            ->where('is_hidden', false)
            ->orderByDesc('created_at')
            ->limit($limit);
    }

    public function reviews()
    {
        return Review::forTarget('club', $this->id)->visible()->with('user')->latest();
    }

    public function approvedMedia()
    {
        return Media::forOwner('club', $this->id)->public()->orderBy('sort_order');
    }

    // ── 별칭: 기존 entry_fee_text -> price_text 로 통합, 하위호환 유지 ──
    public function getEntryFeeTextAttribute(): string
    {
        return $this->price_text;
    }

    public function getOperatingHoursTextAttribute(): string
    {
        return TimeDisplay::range($this->open_time, $this->close_time) ?? '운영시간 안내 예정';
    }

    // ── 비즈니스 로직 ──
    public function isOpenAt(string $time): bool
    {
        if (!$this->open_time || !$this->close_time) return true;
        $open  = (int) str_replace(':', '', $this->open_time);
        $close = (int) str_replace(':', '', $this->close_time);
        $t     = (int) str_replace(':', '', $time);
        if ($close < $open) {
            return $t >= $open || $t <= $close;
        }
        return $t >= $open && $t <= $close;
    }

    // ── 스코프 ──
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInArea($query, ?string $area)
    {
        return $area ? $query->where('area', $area) : $query;
    }

    public function scopeInGenre($query, ?string $genre)
    {
        return $genre ? $query->where('genre', $genre) : $query;
    }

    public function scopeForeignerFriendly($query)
    {
        return $query->where('foreigner_allowed', true);
    }
}
