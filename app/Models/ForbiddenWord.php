<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForbiddenWord extends Model
{
    protected $fillable = [
        'word', 'match_type', 'action_type', 'category', 'severity', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query) { return $query->where('is_active', true); }
}
