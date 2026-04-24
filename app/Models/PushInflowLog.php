<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushInflowLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['campaign_id', 'user_id', 'path', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];
}
