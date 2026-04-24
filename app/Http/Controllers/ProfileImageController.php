<?php

namespace App\Http\Controllers;

use App\Models\ProfileImage;
use App\Models\User;
use App\Services\ProfileImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{
    public function store(Request $request, ProfileImageProcessor $processor)
    {
        abort_unless(auth()->check(), 401, '로그인 후 업로드할 수 있습니다.');

        $user = $request->user();
        abort_unless($user?->isActive(), 403, '활성 사용자만 이용할 수 있습니다.');
        abort_unless($user?->canUpload(), 403, '현재는 이미지 업로드가 제한되어 있습니다.');

        $request->validate([
            'image' => 'required|file|max:' . (int) config('profile-images.max_upload_size_kb', 10240),
        ]);

        $profileImage = $processor->storeForUser($user, $request->file('image'));
        $current = ProfileImage::where('user_id', $user->id)->current()->first();

        return response()->json([
            'id' => $profileImage->id,
            'status' => $profileImage->status,
            'message' => $this->statusMessage($profileImage),
            'image_url' => $profileImage->image_url,
            'thumb_url' => $profileImage->thumb_url,
            'current_image_url' => $current?->image_url ?? $this->defaultProfileImageUrl(),
            'current_thumb_url' => $current?->thumb_url ?? $this->defaultProfileImageUrl(),
            'rejection_reason' => $profileImage->rejection_reason,
            'moderation_provider' => $profileImage->moderation_provider,
            'moderation_verdict' => $profileImage->moderation_verdict,
            'created_at' => optional($profileImage->created_at)?->toIso8601String(),
        ], $profileImage->status === 'approved' ? 201 : 202);
    }

    public function show(User $user)
    {
        $current = $user->profileImages()->current()->latest('approved_at')->first();
        $latest = $user->profileImages()->latest()->first();

        return response()->json([
            'user_id' => $user->id,
            'image_url' => $current?->image_url ?? $this->defaultProfileImageUrl(),
            'thumb_url' => $current?->thumb_url ?? $this->defaultProfileImageUrl(),
            'is_default' => !$current,
            'status' => $latest?->status ?? 'none',
            'message' => $latest ? $this->statusMessage($latest) : '등록된 프로필 이미지가 없습니다.',
            'rejection_reason' => $latest?->status === 'rejected' ? $latest->rejection_reason : null,
            'updated_at' => optional($current?->approved_at ?? $latest?->created_at)?->toIso8601String(),
        ]);
    }

    public function file(Request $request, ProfileImage $profileImage, string $variant = 'image')
    {
        $path = match ($variant) {
            'thumb' => $profileImage->thumb_path,
            'original' => $profileImage->original_path,
            default => $profileImage->image_path,
        };

        abort_unless($path, 404, '이미지 파일을 찾을 수 없습니다.');

        $viewer = $request->user();
        $isPublic = $profileImage->status === 'approved' && $profileImage->is_current;
        $canViewPrivate = $viewer
            && ($viewer->id === $profileImage->user_id || $viewer->isAdmin());

        abort_unless($isPublic || $canViewPrivate, 403, '이미지에 접근할 수 없습니다.');

        $disk = Storage::disk($profileImage->disk);
        abort_unless($disk->exists($path), 404, '이미지 파일을 찾을 수 없습니다.');

        $headers = [
            'Content-Type' => $disk->mimeType($path) ?: $profileImage->mime_type,
            'Cache-Control' => $isPublic ? 'public, max-age=300' : 'private, no-store',
        ];

        return response()->file($disk->path($path), $headers);
    }

    private function statusMessage(ProfileImage $profileImage): string
    {
        return match ($profileImage->status) {
            'approved' => '프로필 이미지가 바로 적용되었습니다.',
            'rejected' => $profileImage->rejection_reason ?: '이미지가 반려되었습니다.',
            default => '관리자 승인 후 적용됩니다.',
        };
    }

    private function defaultProfileImageUrl(): string
    {
        return asset('images/default-profile.svg');
    }
}
