<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'nickname', 'email', 'phone', 'password', 'role', 'status',
        'last_login_at',
        'favorites', 'recent_views', 'preferred_genres', 'recent_areas',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'favorites'         => 'array',
            'recent_views'      => 'array',
            'preferred_genres'  => 'array',
            'recent_areas'      => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isMd(): bool
    {
        return $this->role === 'md';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function moderationActions()
    {
        return $this->hasMany(UserModerationAction::class);
    }

    public function canWrite(): bool
    {
        return $this->isActive() && \App\Services\ModerationService::canWrite($this->id);
    }

    public function canUpload(): bool
    {
        return $this->isActive() && \App\Services\ModerationService::canUpload($this->id);
    }

    public function mdProfile()
    {
        return $this->hasOne(MdProfile::class);
    }

    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class);
    }

    public function favoriteParties()
    {
        return $this->hasMany(FavoriteParty::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function recentViews()
    {
        return $this->hasMany(RecentView::class);
    }

    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function profileImages()
    {
        return $this->hasMany(ProfileImage::class);
    }

    public function niteNotifications()
    {
        return $this->hasMany(NiteNotification::class);
    }

    public function nearbyVisibilitySetting()
    {
        return $this->hasOne(NearbyVisibilitySetting::class);
    }

    public function locationStatus()
    {
        return $this->hasOne(UserLocationStatus::class);
    }

    public function venueCheckins()
    {
        return $this->hasMany(VenueCheckin::class);
    }

    public function blocksIssued()
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function blocksReceived()
    {
        return $this->hasMany(UserBlock::class, 'blocked_id');
    }

    public function conversationsAsUserOne()
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    public function conversationsAsUserTwo()
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }
}
