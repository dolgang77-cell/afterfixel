<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Media;
use App\Models\MdProfile;
use App\Support\ImageUrl;
use App\Models\Review;
use App\Services\MdAccessService;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaUploadController extends Controller
{
    /**
     * 범용 미디어 업로드 (최적화 파이프라인 적용)
     * owner_type: md_profile, club, party, review, community, push
     * 관리자 → 즉시 approved
     * MD → md_profile/club/party 대상만 즉시 approved
     * 일반 회원 → 기존 pending 유지
     */
    public function upload(Request $request, MdAccessService $mdAccess)
    {
        abort_unless(auth()->check(), 401, '로그인 후 업로드할 수 있습니다.');

        $request->validate([
            'image'          => 'required|image|mimes:jpg,jpeg,png,gif,webp|mimetypes:image/jpeg,image/png,image/x-png,image/gif,image/webp|max:10240',
            'owner_type'     => 'required|in:md_profile,club,party,review,community,push',
            'owner_id'       => 'required|integer',
            'upload_context' => 'nullable|in:library,inline',
        ]);

        $ownerType = $request->input('owner_type');
        $ownerId = (int) $request->input('owner_id');
        $uploadContext = $request->input('upload_context', 'library');
        $folder = $this->resolveFolder($ownerType, $uploadContext === 'inline');

        $user = auth()->user();
        $role = $user->role;

        $this->authorizeUploadTarget($user, $ownerType, $ownerId, $mdAccess);

        if ($uploadContext === 'inline') {
            abort_unless(in_array($ownerType, ['club', 'party'], true), 422, '본문 이미지는 클럽/파티 소개에만 사용할 수 있습니다.');
        }

        // 이미지 최적화 파이프라인
        try {
            $result = ImageOptimizer::process($request->file('image'), $folder);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'image' => $exception->getMessage(),
            ]);
        }

        if ($uploadContext === 'inline') {
            $status = Media::determineInitialStatus($role, $ownerType);

            return response()->json([
                'id' => null,
                'url' => $result['url'],
                'thumbnail' => $result['thumbnail_path'] ? ImageUrl::storage($result['thumbnail_path']) : null,
                'approval_status' => $status,
                'approved_at' => $status === 'approved' ? now()->toIso8601String() : null,
                'is_visible' => $status === 'approved',
                'width' => $result['width'],
                'height' => $result['height'],
            ]);
        }

        $media = Media::create([
            'owner_type'       => $ownerType,
            'owner_id'         => $ownerId,
            'uploaded_by'      => $user->id,
            'uploaded_by_role' => $role,
            'file_path'        => $result['path'],
            'file_url'         => $result['url'],
            'original_name'    => $result['original_name'],
            'original_size'    => $result['original_size'],
            'optimized_size'   => $result['optimized_size'],
            'mime_type'        => $result['mime_type'],
            'width'            => $result['width'],
            'height'           => $result['height'],
            'thumbnail_path'   => $result['thumbnail_path'],
            'variant_paths'    => $result['variant_paths'],
            'sort_order'       => Media::forOwner($ownerType, $ownerId)->max('sort_order') + 1,
        ] + Media::approvalAttributesFor($role, $user->id, $ownerType));

        $this->syncProfileImage($media);

        return response()->json([
            'id'              => $media->id,
            'url'             => $media->file_url,
            'thumbnail'       => $result['thumbnail_path'] ? ImageUrl::storage($result['thumbnail_path']) : null,
            'approval_status' => $media->approval_status,
            'approved_at'     => optional($media->approved_at)?->toIso8601String(),
            'is_visible'      => $media->is_visible,
            'width'           => $result['width'],
            'height'          => $result['height'],
        ]);
    }

    private function resolveFolder(string $ownerType, bool $inline = false): string
    {
        $folder = match ($ownerType) {
            'md_profile' => 'md',
            'club'       => 'clubs',
            'party'      => 'parties',
            'review'     => 'reviews',
            'community'  => 'community',
            'push'       => 'push',
            default      => 'uploads',
        };

        if ($inline) {
            return $folder . '/inline';
        }

        return $folder;
    }

    public function uploadMdImage(Request $request, MdAccessService $mdAccess)
    {
        return $this->upload($request, $mdAccess);
    }

    public function destroy(Request $request, Media $media, MdAccessService $mdAccess)
    {
        $user = auth()->user();

        abort_unless($user, 401, '로그인 후 이용할 수 있습니다.');

        if (!$user->isAdmin()) {
            if ($user->isMd()) {
                $mdAccess->authorizeMedia($media);
            } elseif ($media->uploaded_by !== $user->id) {
                abort(403, '본인 업로드 이미지 또는 담당 대상 이미지만 삭제할 수 있습니다.');
            }
        }

        $ownerType = $media->owner_type;
        $ownerId = (int) $media->owner_id;
        $media->delete();
        $this->renumberOwnerMedia($ownerType, $ownerId);
        $this->syncProfileImageFromOwner($ownerType, $ownerId);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '이미지가 삭제되었습니다.');
    }

    public function updateOrder(Request $request, Media $media, MdAccessService $mdAccess)
    {
        $request->validate([
            'sort_order' => 'nullable|integer|min:0|max:999',
            'direction' => 'nullable|in:up,down',
        ]);

        $user = auth()->user();
        abort_unless($user, 401, '로그인 후 이용할 수 있습니다.');

        if (!$user->isAdmin()) {
            if ($user->isMd()) {
                $mdAccess->authorizeMedia($media);
            } elseif ($media->uploaded_by !== $user->id) {
                abort(403, '본인 이미지 정렬만 변경할 수 있습니다.');
            }
        }

        $siblings = Media::forOwner($media->owner_type, (int) $media->owner_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentIndex = $siblings->search(fn ($item) => $item->id === $media->id);
        abort_if($currentIndex === false, 404, '미디어를 찾을 수 없습니다.');

        $targetIndex = $request->filled('sort_order')
            ? min((int) $request->input('sort_order'), max($siblings->count() - 1, 0))
            : ($request->input('direction') === 'up' ? max($currentIndex - 1, 0) : min($currentIndex + 1, $siblings->count() - 1));

        $orderedIds = $siblings->pluck('id')->values();
        $orderedIds->splice($currentIndex, 1);
        $orderedIds->splice($targetIndex, 0, [$media->id]);

        foreach ($orderedIds->values() as $index => $id) {
            Media::whereKey($id)->update(['sort_order' => $index]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '이미지 순서가 변경되었습니다.');
    }

    private function authorizeUploadTarget($user, string $ownerType, int $ownerId, MdAccessService $mdAccess): void
    {
        if ($user->isAdmin()) {
            $this->assertOwnerExists($ownerType, $ownerId);
            return;
        }

        if ($user->isMd()) {
            if (in_array($ownerType, ['md_profile', 'club', 'party'], true)) {
                $mdAccess->authorizeMediaOwner($ownerType, $ownerId);
                return;
            }

            $this->authorizeGeneralUserUpload($user->id, $ownerType, $ownerId);
            return;
        }

        $this->authorizeGeneralUserUpload($user->id, $ownerType, $ownerId);
    }

    private function authorizeGeneralUserUpload(int $userId, string $ownerType, int $ownerId): void
    {
        match ($ownerType) {
            'community' => $this->authorizeCommunityUpload($userId, $ownerId),
            'review' => $this->authorizeReviewUpload($userId, $ownerId),
            default => abort(403, '해당 업로드는 허용되지 않습니다.'),
        };
    }

    private function authorizeCommunityUpload(int $userId, int $ownerId): void
    {
        if ($ownerId === 0) {
            return;
        }

        abort_unless(
            CommunityPost::whereKey($ownerId)->where('user_id', $userId)->exists(),
            403,
            '본인 커뮤니티 게시글에만 이미지를 연결할 수 있습니다.',
        );
    }

    private function authorizeReviewUpload(int $userId, int $ownerId): void
    {
        abort_unless(
            $ownerId > 0 && Review::whereKey($ownerId)->where('user_id', $userId)->exists(),
            403,
            '본인 후기 이미지 만 업로드할 수 있습니다.',
        );
    }

    private function assertOwnerExists(string $ownerType, int $ownerId): void
    {
        if ($ownerType === 'community' && $ownerId === 0) {
            return;
        }

        $exists = match ($ownerType) {
            'md_profile' => MdProfile::whereKey($ownerId)->exists(),
            'club' => \App\Models\Club::whereKey($ownerId)->exists(),
            'party' => \App\Models\Party::whereKey($ownerId)->exists(),
            'review' => Review::whereKey($ownerId)->exists(),
            'community' => CommunityPost::whereKey($ownerId)->exists(),
            'push' => true,
            default => false,
        };

        abort_unless($exists, 404, '업로드 대상을 찾을 수 없습니다.');
    }

    private function renumberOwnerMedia(string $ownerType, int $ownerId): void
    {
        Media::forOwner($ownerType, $ownerId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(fn (Media $media, int $index) => $media->update(['sort_order' => $index]));
    }

    private function syncProfileImage(Media $media): void
    {
        if ($media->owner_type !== 'md_profile') {
            return;
        }

        MdProfile::whereKey($media->owner_id)->update(['profile_image' => $media->file_url]);
    }

    private function syncProfileImageFromOwner(string $ownerType, int $ownerId): void
    {
        if ($ownerType !== 'md_profile') {
            return;
        }

        $nextImage = Media::forOwner('md_profile', $ownerId)
            ->public()
            ->orderBy('sort_order')
            ->value('file_url');

        MdProfile::whereKey($ownerId)->update(['profile_image' => $nextImage]);
    }
}
