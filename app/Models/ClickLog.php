<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    protected $fillable = [
        'target_type', 'target_id', 'source',
        'session_id', 'user_id', 'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
