<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushCampaign extends Model
{
    protected $fillable = [
        'title', 'body', 'image', 'link', 'campaign_type',
        'target_type', 'target_query', 'send_type', 'scheduled_at',
        'status', 'target_count', 'sent_count', 'failed_count',
        'clicked_count', 'inflow_count', 'created_by', 'sent_at',
    ];

    protected $casts = [
        'target_query' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public static array $types = [
        'notice'    => '공지',
        'event'     => '이벤트',
        'party'     => '파티 추천',
        'system'    => '시스템',
        'marketing' => '마케팅',
    ];

    public static array $statuses = [
        'draft'     => '작성중',
        'scheduled' => '예약됨',
        'sending'   => '발송중',
        'sent'      => '발송완료',
        'failed'    => '실패',
        'cancelled' => '취소',
    ];

    public static array $retentionPresets = [
        'recent_view_no_inquiry' => '최근 본 후 미문의',
        'favorite_no_inquiry' => '찜 후 미문의',
        'inquiry_reply_unread' => '응답 도착 후 미확인',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(PushDeliveryLog::class, 'campaign_id');
    }

    public function inflowLogs()
    {
        return $this->hasMany(PushInflowLog::class, 'campaign_id');
    }

    public function scopeScheduledReady($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
