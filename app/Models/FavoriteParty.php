<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteParty extends Model
{
    protected $fillable = ['session_id', 'user_id', 'party_id'];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
