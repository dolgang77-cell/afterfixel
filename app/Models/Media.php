<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Support\ImageUrl;

class Media extends Model
{
    private const RESPONSIVE_WIDTHS = [320, 640, 960];

    protected $table = 'media';

    protected $fillable = [
        'owner_type', 'owner_id', 'uploaded_by', 'uploaded_by_role',
        'file_path', 'file_url',
        'original_name', 'original_size', 'optimized_size',
        'mime_type', 'width', 'height', 'thumbnail_path', 'variant_paths',
        'approval_status', 'approved_by',
        'approved_at', 'rejected_reason', 'is_visible', 'sort_order',
    ];

    protected $casts = [
        'is_visible'  => 'boolean',
        'approved_at' => 'datetime',
        'variant_paths' => 'array',
    ];

    public function getFileUrlAttribute($value): string
    {
        $fallback = $this->file_path
            ? ImageUrl::storage($this->file_path)
            : ImageUrl::default();

        return ImageUrl::normalize($value, $fallback);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return ImageUrl::storage($this->thumbnail_path);
    }

    public function getFileSrcsetAttribute(): ?string
    {
        $srcset = $this->buildLocalSrcset();

        if ($srcset) {
            return $srcset;
        }

        return ImageUrl::srcset($this->file_url);
    }

    public function getThumbnailSrcsetAttribute(): ?string
    {
        return $this->buildLocalSrcset();
    }

    /**
     * @return array<int, int>
     */
    public function expectedVariantWidths(): array
    {
        if (!$this->width) {
            return [];
        }

        return collect(self::RESPONSIVE_WIDTHS)
            ->filter(fn (int $width) => $this->width > $width)
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function missingVariantWidths(): array
    {
        $existing = collect(array_keys($this->variant_paths ?? []))
            ->map(fn ($width) => (int) $width)
            ->all();

        return collect($this->expectedVariantWidths())
            ->reject(fn (int $width) => in_array($width, $existing, true))
            ->values()
            ->all();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── 스코프 ──

    /**
     * 사용자 화면에 노출 가능한 이미지만
     * approved + is_visible
     */
    public function scopePublic($query)
    {
        return $query->where('approval_status', 'approved')->where('is_visible', true);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeForOwner($query, string $type, int $id)
    {
        return $query->where('owner_type', $type)->where('owner_id', $id);
    }

    // ── 승인 관련 ──

    public function approve(int $adminId): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $adminId, ?string $reason = null): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'rejected_reason' => $reason,
        ]);
    }

    /**
     * 업로드 시 역할에 따라 초기 상태 결정
     * admin/super_admin → 즉시 approved
     * md → md_profile/club/party 만 즉시 approved
     * user → pending
     */
    public static function determineInitialStatus(string $role, ?string $ownerType = null): string
    {
        return static::shouldAutoApprove($role, $ownerType) ? 'approved' : 'pending';
    }

    public static function shouldAutoApprove(string $role, ?string $ownerType = null): bool
    {
        if (in_array($role, ['admin', 'super_admin'], true)) {
            return true;
        }

        if ($role !== 'md') {
            return false;
        }

        return in_array($ownerType, ['md_profile', 'club', 'party'], true);
    }

    public static function approvalAttributesFor(string $role, int $uploaderId, ?string $ownerType = null): array
    {
        $status = static::determineInitialStatus($role, $ownerType);
        $autoApproved = $status === 'approved';

        return [
            'approval_status' => $status,
            'approved_by' => $autoApproved ? $uploaderId : null,
            'approved_at' => $autoApproved ? Carbon::now() : null,
            'is_visible' => true,
        ];
    }

    private function buildLocalSrcset(): ?string
    {
        $entries = collect($this->variant_paths ?? [])
            ->filter(fn ($path, $width) => !empty($path) && is_numeric($width))
            ->sortKeys()
            ->map(fn ($path, $width) => ImageUrl::storage($path) . ' ' . (int) $width . 'w')
            ->values();

        if ($this->file_path && $this->width) {
            $entries->push(ImageUrl::storage($this->file_path) . ' ' . (int) $this->width . 'w');
        }

        $entries = $entries->unique()->values();

        return $entries->isNotEmpty() ? $entries->implode(', ') : null;
    }
}
