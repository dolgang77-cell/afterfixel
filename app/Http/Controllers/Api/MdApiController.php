<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\Party;
use App\Models\Review;
use App\Services\InquiryNotificationService;
use App\Services\MdAccessService;
use App\Services\RichContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MdApiController extends Controller
{
    public function __construct(private readonly MdAccessService $mdAccess)
    {
    }

    public function me(): JsonResponse
    {
        $profile = $this->mdAccess->mdProfile()->loadMissing('profileMedia');
        $clubIds = $this->mdAccess->assignedClubIds();
        $partyIds = $this->mdAccess->assignedPartyIds();

        return response()->json([
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role,
                'status' => auth()->user()->status,
            ],
            'profile' => [
                'id' => $profile->id,
                'displayName' => $profile->display_name,
                'profileImage' => $profile->profile_image,
                'profileImageSrcset' => $profile->profile_image_srcset,
                'intro' => $profile->intro,
                'contactInfo' => $profile->contact_info,
                'externalLink' => $profile->external_link,
                'areas' => $profile->areas ?? [],
                'genres' => $profile->genres ?? [],
                'affiliation' => $profile->affiliation,
            ],
            'stats' => [
                'assignedClubCount' => count($clubIds),
                'assignedPartyCount' => count($partyIds),
                'unansweredInquiryCount' => Inquiry::where('assigned_md_id', $profile->id)->pending()->count(),
                'recentReviewCount' => Review::visible()
                    ->where(fn ($query) => $query
                        ->where(fn ($sub) => $sub->where('target_type', 'club')->whereIn('target_id', $clubIds ?: [0]))
                        ->orWhere(fn ($sub) => $sub->where('target_type', 'party')->whereIn('target_id', $partyIds ?: [0])))
                    ->count(),
                'approvedMediaCount' => Media::where('uploaded_by', auth()->id())->where('approval_status', 'approved')->count(),
            ],
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:50',
            'intro' => 'nullable|string|max:2000',
            'contact_info' => 'nullable|string|max:200',
            'external_link' => 'nullable|string|max:500',
        ]);

        $this->mdAccess->mdProfile()->update($data);

        return response()->json(['success' => true]);
    }

    public function clubs(): JsonResponse
    {
        $clubs = Club::whereIn('id', $this->mdAccess->assignedClubIds())
            ->orderBy('name')
            ->get()
            ->map(fn (Club $club) => $this->serializeClub($club));

        return response()->json(['data' => $clubs]);
    }

    public function parties(): JsonResponse
    {
        $parties = Party::with('club')
            ->whereIn('id', $this->mdAccess->assignedPartyIds())
            ->orderByDesc('event_date')
            ->get()
            ->map(fn (Party $party) => $this->serializeParty($party));

        return response()->json(['data' => $parties]);
    }

    public function updateClubContent(Request $request, Club $club): JsonResponse
    {
        $this->mdAccess->authorizeClub($club);

        $data = $request->validate([
            'short_description' => 'nullable|string|max:200',
            'full_description' => 'nullable|string|max:50000',
            'intro_title' => 'nullable|string|max:100',
            'guide_text' => 'nullable|string|max:50000',
        ]);
        $data['full_description'] = RichContentService::sanitize($data['full_description'] ?? null);
        $data['guide_text'] = RichContentService::sanitize($data['guide_text'] ?? null);

        $club->update($data);

        return response()->json([
            'success' => true,
            'data' => $this->serializeClub($club->fresh()),
        ]);
    }

    public function updatePartyContent(Request $request, Party $party): JsonResponse
    {
        $this->mdAccess->authorizeParty($party);

        $data = $request->validate([
            'short_description' => 'nullable|string|max:200',
            'full_description' => 'nullable|string|max:50000',
            'intro_title' => 'nullable|string|max:100',
            'guide_text' => 'nullable|string|max:50000',
        ]);
        $data['full_description'] = RichContentService::sanitize($data['full_description'] ?? null);
        $data['guide_text'] = RichContentService::sanitize($data['guide_text'] ?? null);

        $party->update($data);

        return response()->json([
            'success' => true,
            'data' => $this->serializeParty($party->fresh()->load('club')),
        ]);
    }

    public function inquiries(Request $request): JsonResponse
    {
        $inquiries = Inquiry::with('user', 'publicReplies')
            ->where('assigned_md_id', $this->mdAccess->mdProfile()->id)
            ->when($request->status, fn ($query, $value) => $query->where('status', $value))
            ->when($request->intent_type, fn ($query, $value) => $query->where('intent_type', $value))
            ->leadInboxOrder()
            ->paginate(20);

        return response()->json([
            'data' => collect($inquiries->items())->map(fn (Inquiry $inquiry) => $this->serializeInquiry($inquiry)),
            'meta' => [
                'currentPage' => $inquiries->currentPage(),
                'lastPage' => $inquiries->lastPage(),
                'total' => $inquiries->total(),
            ],
        ]);
    }

    public function replyInquiry(Request $request, Inquiry $inquiry): JsonResponse
    {
        $this->mdAccess->authorizeInquiry($inquiry);

        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $inquiry->addReply('md', auth()->id(), $data['message']);

        try {
            InquiryNotificationService::notifyUserNewReply($inquiry, 'md');
        } catch (\Throwable $exception) {
            Log::warning('MD API inquiry reply notification failed', [
                'inquiry_id' => $inquiry->id,
                'md_user_id' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function updateInquiryStatus(Request $request, Inquiry $inquiry): JsonResponse
    {
        $this->mdAccess->authorizeInquiry($inquiry);

        $data = $request->validate([
            'status' => 'required|in:in_progress,answered,reservation_confirmed,consultation_completed',
        ]);

        $inquiry->update(['status' => $data['status']]);

        try {
            InquiryNotificationService::notifyUserStatusChanged($inquiry);
        } catch (\Throwable $exception) {
            Log::warning('MD API inquiry status notification failed', [
                'inquiry_id' => $inquiry->id,
                'md_user_id' => auth()->id(),
                'status' => $data['status'],
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $clubIds = $this->mdAccess->assignedClubIds();
        $partyIds = $this->mdAccess->assignedPartyIds();

        $reviews = Review::with('user')
            ->where(fn ($query) => $query
                ->where(fn ($sub) => $sub->where('target_type', 'club')->whereIn('target_id', $clubIds ?: [0]))
                ->orWhere(fn ($sub) => $sub->where('target_type', 'party')->whereIn('target_id', $partyIds ?: [0])))
            ->when($request->target_type, fn ($query, $value) => $query->where('target_type', $value))
            ->when($request->target_id, fn ($query, $value) => $query->where('target_id', $value))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($reviews->items())->map(fn (Review $review) => [
                'id' => $review->id,
                'targetType' => $review->target_type,
                'targetId' => $review->target_id,
                'content' => $review->content,
                'rating' => $review->rating,
                'tags' => $review->tags ?? [],
                'author' => $review->user?->name ?? '익명',
                'createdAt' => $review->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'currentPage' => $reviews->currentPage(),
                'lastPage' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    private function serializeClub(Club $club): array
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'area' => $club->area,
            'genre' => $club->genre,
            'thumbnailUrl' => $club->thumbnail_url,
            'thumbnailSrcset' => $club->thumbnail_srcset,
            'introTitle' => $club->intro_title,
            'shortDescription' => $club->short_description,
            'fullDescription' => $club->full_description,
            'guideText' => $club->guide_text,
            'media' => Media::forOwner('club', $club->id)->orderBy('sort_order')->get()->map(fn (Media $media) => $this->serializeMedia($media)),
        ];
    }

    private function serializeParty(Party $party): array
    {
        return [
            'id' => $party->id,
            'clubId' => $party->club_id,
            'clubName' => $party->club?->name,
            'name' => $party->name,
            'eventDate' => $party->event_date?->format('Y-m-d'),
            'thumbnailUrl' => $party->thumbnail_url,
            'thumbnailSrcset' => $party->thumbnail_srcset,
            'introTitle' => $party->intro_title,
            'shortDescription' => $party->short_description,
            'fullDescription' => $party->full_description,
            'guideText' => $party->guide_text,
            'media' => Media::forOwner('party', $party->id)->orderBy('sort_order')->get()->map(fn (Media $media) => $this->serializeMedia($media)),
        ];
    }

    private function serializeInquiry(Inquiry $inquiry): array
    {
        return [
            'id' => $inquiry->id,
            'targetType' => $inquiry->target_type,
            'targetId' => $inquiry->target_id,
            'status' => $inquiry->status,
            'intentType' => $inquiry->intent_type,
            'intentLabel' => $inquiry->intent_label,
            'subject' => $inquiry->subject,
            'message' => $inquiry->message,
            'userName' => $inquiry->user?->name,
            'visitDate' => $inquiry->visit_date?->format('Y-m-d'),
            'visitTimeSlot' => $inquiry->visit_time_slot,
            'visitTimeSlotLabel' => $inquiry->visit_time_slot_label,
            'partySize' => $inquiry->party_size,
            'budgetMin' => $inquiry->budget_min,
            'budgetMax' => $inquiry->budget_max,
            'budgetText' => $inquiry->budget_text,
            'genderMix' => $inquiry->gender_mix,
            'specialRequest' => $inquiry->special_request,
            'priorityScore' => $inquiry->priorityScore(),
            'createdAt' => $inquiry->created_at?->toIso8601String(),
            'replies' => $inquiry->publicReplies->map(fn ($reply) => [
                'id' => $reply->id,
                'authorType' => $reply->author_type,
                'message' => $reply->message,
                'createdAt' => $reply->created_at?->toIso8601String(),
            ]),
        ];
    }

    private function serializeMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'ownerType' => $media->owner_type,
            'ownerId' => $media->owner_id,
            'url' => $media->file_url,
            'urlSrcset' => $media->file_srcset,
            'thumbnailUrl' => $media->thumbnail_url,
            'thumbnailSrcset' => $media->thumbnail_srcset,
            'uploadedByRole' => $media->uploaded_by_role,
            'approvalStatus' => $media->approval_status,
            'approvedBy' => $media->approved_by,
            'approvedAt' => $media->approved_at?->toIso8601String(),
            'isVisible' => $media->is_visible,
            'sortOrder' => $media->sort_order,
        ];
    }
}
