<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id', 'target_type', 'target_id',
        'reason', 'detail', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public static array $reasons = [
        'abuse'      => '욕설/비방',
        'spam'       => '도배/광고',
        'adult'      => '음란/선정성',
        'false_info' => '허위정보',
        'privacy'    => '개인정보노출',
        'other'      => '기타',
    ];

    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
}
