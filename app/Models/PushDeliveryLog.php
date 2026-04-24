<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushDeliveryLog extends Model
{
    protected $fillable = [
        'campaign_id', 'user_id', 'status', 'sent_at', 'clicked_at',
    ];

    protected $casts = [
        'sent_at'    => 'datetime',
        'clicked_at' => 'datetime',
    ];
}
