<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryReply extends Model
{
    protected $fillable = [
        'inquiry_id', 'author_type', 'author_id', 'message', 'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
