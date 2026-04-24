<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ProfileImage;
use Illuminate\Http\Request;

class ProfileImageModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $images = ProfileImage::with('user', 'approver')
            ->when($status, fn ($query, $value) => $query->where('status', $value))
            ->orderByDesc('created_at')
            ->paginate(24)
            ->withQueryString();

        $pendingCount = ProfileImage::pending()->count();

        return view('admin.profile-images.index', compact('images', 'pendingCount', 'status'));
    }

    public function pending(Request $request)
    {
        $images = ProfileImage::with('user')
            ->pending()
            ->orderBy('created_at')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => $images->getCollection()->map(fn (ProfileImage $image) => [
                'id' => $image->id,
                'user_id' => $image->user_id,
                'user_name' => $image->user?->name,
                'user_nickname' => $image->user?->nickname,
                'image_url' => $image->image_url,
                'thumb_url' => $image->thumb_url,
                'created_at' => optional($image->created_at)?->toIso8601String(),
                'moderation_provider' => $image->moderation_provider,
                'moderation_verdict' => $image->moderation_verdict,
                'moderation_score' => $image->moderation_score,
                'moderation_labels' => $image->moderation_labels,
            ]),
            'meta' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'total' => $images->total(),
            ],
        ]);
    }

    public function approve(Request $request)
    {
        $payload = $request->validate([
            'profile_image_id' => 'required|integer|exists:profile_images,id',
        ]);

        $profileImage = ProfileImage::findOrFail($payload['profile_image_id']);
        $profileImage->approve(auth()->id());

        AdminLog::record('approve', 'profile_image', $profileImage->id, [
            'user_id' => $profileImage->user_id,
        ]);

        return $this->response($request, '프로필 이미지가 승인되었습니다.', $profileImage);
    }

    public function reject(Request $request)
    {
        $payload = $request->validate([
            'profile_image_id' => 'required|integer|exists:profile_images,id',
            'reason' => 'required|string|max:500',
        ]);

        $profileImage = ProfileImage::findOrFail($payload['profile_image_id']);
        $profileImage->reject(auth()->id(), $payload['reason']);

        AdminLog::record('reject', 'profile_image', $profileImage->id, [
            'user_id' => $profileImage->user_id,
            'reason' => $payload['reason'],
        ]);

        return $this->response($request, '프로필 이미지가 반려되었습니다.', $profileImage);
    }

    private function response(Request $request, string $message, ProfileImage $profileImage)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $profileImage->id,
                    'status' => $profileImage->status,
                    'is_current' => $profileImage->is_current,
                ],
            ]);
        }

        return back()->with('success', $message);
    }
}
