<?php

namespace App\Models;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use App\Support\ImageUrl;

class MdProfile extends Model
{
    protected $fillable = [
        'user_id', 'display_name', 'profile_image', 'intro',
        'contact_info', 'external_link', 'areas', 'genres',
        'affiliation', 'admin_memo', 'status', 'visible', 'priority',
    ];

    protected $casts = [
        'areas'   => 'array',
        'genres'  => 'array',
        'visible' => 'boolean',
    ];

    public function getProfileImageAttribute($value): string
    {
        return ImageUrl::normalize($value, ImageUrl::default('profile'));
    }

    public function getProfileImageSrcsetAttribute(): ?string
    {
        return $this->currentProfileMedia()?->file_srcset;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profileMedia()
    {
        return $this->hasMany(Media::class, 'owner_id')
            ->where('owner_type', 'md_profile');
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'md_club')
            ->withPivot('visible', 'priority', 'note')
            ->withTimestamps();
    }

    public function parties()
    {
        return $this->belongsToMany(Party::class, 'md_party')
            ->withPivot('visible', 'priority', 'note')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->active()->where('visible', true);
    }

    private function currentProfileMedia(): ?Media
    {
        if ($this->relationLoaded('profileMedia')) {
            return $this->profileMedia
                ->filter(fn (Media $media) => $media->approval_status === 'approved' && $media->is_visible)
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->first();
        }

        return $this->profileMedia()
            ->public()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }
}
