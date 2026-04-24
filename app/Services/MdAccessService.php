<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\MdProfile;
use App\Models\Party;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MdAccessService
{
    private ?array $assignedClubIds = null;
    private ?array $assignedPartyIds = null;

    public function user(): User
    {
        $user = auth()->user();

        abort_unless($user?->isMd() && $user->isActive(), 403, 'MD 권한이 필요합니다.');

        return $user;
    }

    public function mdProfile(): MdProfile
    {
        $profile = $this->user()->mdProfile;

        abort_unless($profile, 403, 'MD 프로필이 연결되지 않았습니다.');

        return $profile;
    }

    public function assignedClubIds(): array
    {
        if ($this->assignedClubIds !== null) {
            return $this->assignedClubIds;
        }

        return $this->assignedClubIds = DB::table('md_club')
            ->where('md_profile_id', $this->mdProfile()->id)
            ->where('visible', true)
            ->pluck('club_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function assignedPartyIds(): array
    {
        if ($this->assignedPartyIds !== null) {
            return $this->assignedPartyIds;
        }

        return $this->assignedPartyIds = DB::table('md_party')
            ->where('md_profile_id', $this->mdProfile()->id)
            ->where('visible', true)
            ->pluck('party_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function authorizeClub(Club $club): void
    {
        abort_unless(in_array($club->id, $this->assignedClubIds(), true), 403, '담당 클럽이 아닙니다.');
    }

    public function authorizeParty(Party $party): void
    {
        abort_unless(in_array($party->id, $this->assignedPartyIds(), true), 403, '담당 파티가 아닙니다.');
    }

    public function authorizeInquiry(Inquiry $inquiry): void
    {
        abort_unless($inquiry->assigned_md_id === $this->mdProfile()->id, 403, '담당 문의가 아닙니다.');
    }

    public function authorizeMediaOwner(string $ownerType, int $ownerId): void
    {
        match ($ownerType) {
            'md_profile' => $this->authorizeMdProfileOwner($ownerId),
            'club' => abort_unless(in_array($ownerId, $this->assignedClubIds(), true), 403, '담당 클럽이 아닙니다.'),
            'party' => abort_unless(in_array($ownerId, $this->assignedPartyIds(), true), 403, '담당 파티가 아닙니다.'),
            default => abort(403, 'MD 권한으로는 해당 대상을 관리할 수 없습니다.'),
        };
    }

    public function canManageMedia(Media $media): bool
    {
        return match ($media->owner_type) {
            'md_profile' => (int) $media->owner_id === $this->mdProfile()->id,
            'club' => in_array((int) $media->owner_id, $this->assignedClubIds(), true),
            'party' => in_array((int) $media->owner_id, $this->assignedPartyIds(), true),
            default => false,
        };
    }

    public function authorizeMedia(Media $media): void
    {
        abort_unless($this->canManageMedia($media), 403, '담당 대상의 미디어만 관리할 수 있습니다.');
    }

    private function authorizeMdProfileOwner(int $ownerId): void
    {
        abort_unless($ownerId === $this->mdProfile()->id, 403, '본인 MD 프로필만 관리할 수 있습니다.');
    }
}
