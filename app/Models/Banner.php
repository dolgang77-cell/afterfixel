<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'image_url', 'link_url', 'position',
        'sort_order', 'is_active', 'start_date', 'end_date',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', today()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()));
    }

    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }
}
