<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplyTemplate extends Model
{
    protected $fillable = [
        'actor_type',
        'category',
        'title',
        'body',
        'intent_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
