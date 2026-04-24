<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourRecommendation extends Model
{
    protected $fillable = [
        'session_id', 'user_id', 'input_condition', 'ordered_stops',
        'total_cost', 'total_travel_minutes', 'probability_score',
        'calorie_score', 'summary', 'like_count',
    ];

    protected $casts = [
        'input_condition' => 'array',
        'ordered_stops'   => 'array',
        'total_cost'      => 'integer',
        'probability_score' => 'float',
        'calorie_score'   => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
