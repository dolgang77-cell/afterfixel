<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\Party;
use App\Models\Review;
use App\Services\MdAccessService;
use App\Services\ReplyTemplateService;
use App\Services\RichContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MdDashboardController extends Controller
{
    public function __construct(private readonly MdAccessService $mdAccess)
    {
    }

    // ── 대시보드 홈 ──
    public function index()
    {
        $md = $this->mdAccess->mdProfile();
        $clubIds = $this->mdAccess->assignedClubIds();
        $partyIds = $this->mdAccess->assignedPartyIds();

        return view('md-dashboard.index', [
            'md'              => $md,
            'clubCount'       => count($clubIds),
            'partyCount'      => count($partyIds),
            'liveMediaCount'  => Media::where('uploaded_by', auth()->id())->where('approval_status', 'approved')->count(),
            'pendingInquiries' => Inquiry::where('assigned_md_id', $md->id)->pending()->count(),
            'recentReviewCount' => Review::visible()
                ->where(fn($q) => $q->where(fn($sub) => $sub->where('target_type', 'club')->whereIn('target_id', $clubIds ?: [0]))
                    ->orWhere(fn($sub) => $sub->where('target_type', 'party')->whereIn('target_id', $partyIds ?: [0])))
                ->count(),
            'recentReviews'   => Review::visible()
                ->where(fn($q) => $q->where(fn($sub) => $sub->where('target_type', 'club')->whereIn('target_id', $clubIds ?: [0]))
                    ->orWhere(fn($sub) => $sub->where('target_type', 'party')->whereIn('target_id', $partyIds ?: [0])))
                ->latest()->limit(5)->get(),
        ]);
    }

    // ── 프로필 ──
    public function profile()
    {
        $md = $this->mdAccess->mdProfile();
        $profileMedia = Media::forOwner('md_profile', $md->id)->orderBy('sort_order')->get();

        return view('md-dashboard.profile', compact('md', 'profileMedia'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'display_name'  => 'required|string|max:50',
            'intro'         => 'nullable|string|max:2000',
            'external_link' => 'nullable|string|max:500',
            'contact_info'  => 'nullable|string|max:200',
        ]);
        $this->mdAccess->mdProfile()->update($data);
        return back()->with('success', '프로필이 수정되었습니다.');
    }

    // ── 담당 클럽 ──
    public function clubs()
    {
        $clubs = Club::whereIn('id', $this->mdAccess->assignedClubIds())
            ->orderBy('name')
            ->get()
            ->each(function (Club $club) {
                $club->setAttribute('visible_media_count', Media::forOwner('club', $club->id)->public()->count());
                $club->setAttribute('review_count', Review::forTarget('club', $club->id)->visible()->count());
            });

        return view('md-dashboard.clubs', compact('clubs'));
    }

    public function editClubContent(Club $club)
    {
        $this->mdAccess->authorizeClub($club);
        $media = Media::forOwner('club', $club->id)->orderBy('sort_order')->get();
        return view('md-dashboard.club-content', compact('club', 'media'));
    }

    public function updateClubContent(Request $request, Club $club)
    {
        $this->mdAccess->authorizeClub($club);
        $data = $request->validate([
            'short_description' => 'nullable|string|max:200',
            'full_description'  => 'nullable|string|max:50000',
            'intro_title'       => 'nullable|string|max:100',
            'guide_text'        => 'nullable|string|max:50000',
        ]);
        $data['full_description'] = RichContentService::sanitize($data['full_description'] ?? null);
        $data['guide_text'] = RichContentService::sanitize($data['guide_text'] ?? null);
        $club->update($data);
        return back()->with('success', '클럽 소개가 수정되었습니다.');
    }

    // ── 담당 파티 ──
    public function parties()
    {
        $parties = Party::with('club')
            ->whereIn('id', $this->mdAccess->assignedPartyIds())
            ->orderByDesc('event_date')
            ->get()
            ->each(function (Party $party) {
                $party->setAttribute('visible_media_count', Media::forOwner('party', $party->id)->public()->count());
                $party->setAttribute('review_count', Review::forTarget('party', $party->id)->visible()->count());
            });
        return view('md-dashboard.parties', compact('parties'));
    }

    public function editPartyContent(Party $party)
    {
        $this->mdAccess->authorizeParty($party);
        $media = Media::forOwner('party', $party->id)->orderBy('sort_order')->get();
        return view('md-dashboard.party-content', compact('party', 'media'));
    }

    public function updatePartyContent(Request $request, Party $party)
    {
        $this->mdAccess->authorizeParty($party);
        $data = $request->validate([
            'short_description' => 'nullable|string|max:200',
            'full_description'  => 'nullable|string|max:50000',
            'intro_title'       => 'nullable|string|max:100',
            'guide_text'        => 'nullable|string|max:50000',
        ]);
        $data['full_description'] = RichContentService::sanitize($data['full_description'] ?? null);
        $data['guide_text'] = RichContentService::sanitize($data['guide_text'] ?? null);
        $party->update($data);
        return back()->with('success', '파티 소개가 수정되었습니다.');
    }

    // ── 후기 ──
    public function reviews(Request $request)
    {
        $clubIds = $this->mdAccess->assignedClubIds();
        $partyIds = $this->mdAccess->assignedPartyIds();

        $reviews = Review::with('user')
            ->where(fn($q) => $q
                ->where(fn($sub) => $sub->where('target_type', 'club')->whereIn('target_id', $clubIds ?: [0]))
                ->orWhere(fn($sub) => $sub->where('target_type', 'party')->whereIn('target_id', $partyIds ?: [0]))
            )
            ->when($request->target_type, fn($q, $v) => $q->where('target_type', $v))
            ->when($request->target_id, fn($q, $v) => $q->where('target_id', $v))
            ->latest()
            ->paginate(20);

        return view('md-dashboard.reviews', compact('reviews'));
    }

    // ── 문의 ──
    public function inquiries(Request $request)
    {
        $mdId = $this->mdAccess->mdProfile()->id;

        $allInquiries = Inquiry::with(['user', 'publicReplies'])
            ->where('assigned_md_id', $mdId)
            ->get();

        $slaTracked = $allInquiries
            ->filter(fn (Inquiry $inquiry) => $inquiry->responseDelayMinutes() !== null)
            ->values();

        $sla10Ids = $slaTracked
            ->filter(fn (Inquiry $inquiry) => ($inquiry->responseDelayMinutes() ?? 0) >= 10)
            ->pluck('id')
            ->all();

        $sla30Ids = $slaTracked
            ->filter(fn (Inquiry $inquiry) => ($inquiry->responseDelayMinutes() ?? 0) >= 30)
            ->pluck('id')
            ->all();

        $sla60Ids = $slaTracked
            ->filter(fn (Inquiry $inquiry) => ($inquiry->responseDelayMinutes() ?? 0) >= 60)
            ->pluck('id')
            ->all();

        $inquiries = Inquiry::with(['user', 'publicReplies'])
            ->where('assigned_md_id', $mdId)
            ->when($request->queue === 'unanswered', fn($q) => $q->where('status', 'pending'))
            ->when($request->queue === 'delayed', fn($q) => $q->whereIn('id', $sla30Ids ?: [0]))
            ->when($request->queue === 'sla_10', fn($q) => $q->whereIn('id', $sla10Ids ?: [0]))
            ->when($request->queue === 'sla_30', fn($q) => $q->whereIn('id', $sla30Ids ?: [0]))
            ->when($request->queue === 'sla_60', fn($q) => $q->whereIn('id', $sla60Ids ?: [0]))
            ->when($request->queue === 'follow_up', fn($q) => $q->where('status', 'in_progress'))
            ->when($request->queue === 'confirmation_waiting', fn($q) => $q->where('status', 'answered'))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->intent_type, fn($q, $v) => $q->where('intent_type', $v))
            ->leadInboxOrder()
            ->paginate(20)
            ->withQueryString();

        $summaryInquiries = Inquiry::with('publicReplies')
            ->where('assigned_md_id', $mdId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $leadSummary = [
            'pendingCount' => Inquiry::where('assigned_md_id', $mdId)->where('status', 'pending')->count(),
            'delayedCount' => count($sla30Ids),
            'sla10Count' => count($sla10Ids),
            'sla30Count' => count($sla30Ids),
            'sla60Count' => count($sla60Ids),
            'followUpCount' => Inquiry::where('assigned_md_id', $mdId)->where('status', 'in_progress')->count(),
            'confirmationWaitingCount' => Inquiry::where('assigned_md_id', $mdId)->where('status', 'answered')->count(),
            'avgFirstResponseText' => $this->formatMinutes(
                $summaryInquiries
                    ->map(fn (Inquiry $inquiry) => $inquiry->firstResponseMinutes())
                    ->filter()
                    ->avg()
            ),
            'confirmationRate' => $summaryInquiries->isNotEmpty()
                ? (int) round(($summaryInquiries->where('status', 'reservation_confirmed')->count() / $summaryInquiries->count()) * 100)
                : null,
            'avgEstimatedValueText' => $this->formatCurrency(
                $summaryInquiries
                    ->map(fn (Inquiry $inquiry) => $inquiry->estimatedValue())
                    ->filter()
                    ->avg()
            ),
            'priorityItems' => $allInquiries
                ->sortBy(fn (Inquiry $inquiry) => sprintf(
                    '%d-%d-%05d-%010d',
                    in_array($inquiry->status, ['pending', 'in_progress'], true) ? 0 : 1,
                    match ($inquiry->slaLevel()) {
                        'critical' => 0,
                        'warning' => 1,
                        'attention' => 2,
                        default => 3,
                    },
                    99999 - min(99999, $inquiry->leadPriorityScore()),
                    $inquiry->created_at->timestamp
                ))
                ->take(3)
                ->values(),
        ];

        return view('md-dashboard.inquiries', compact('inquiries', 'leadSummary'));
    }

    public function showInquiry(Inquiry $inquiry, ReplyTemplateService $replyTemplateService)
    {
        $this->mdAccess->authorizeInquiry($inquiry);
        $inquiry->load('user', 'replies.author', 'publicReplies.author', 'internalReplies.author');

        $sameUserInquiryCount = $inquiry->user_id
            ? Inquiry::where('assigned_md_id', $this->mdAccess->mdProfile()->id)
                ->where('user_id', $inquiry->user_id)
                ->count()
            : 1;

        $replyTemplates = $replyTemplateService->for('md', $inquiry);

        return view('md-dashboard.inquiry-show', compact('inquiry', 'sameUserInquiryCount', 'replyTemplates'));
    }

    public function replyInquiry(Request $request, Inquiry $inquiry)
    {
        $this->mdAccess->authorizeInquiry($inquiry);
        $request->validate([
            'message' => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ]);

        $inquiry->addReply('md', auth()->id(), $request->message, $request->boolean('is_internal'));

        if (!$request->boolean('is_internal')) {
            try {
                \App\Services\InquiryNotificationService::notifyUserNewReply($inquiry, 'md');
            } catch (\Throwable $exception) {
                Log::warning('MD inquiry reply notification failed', [
                    'inquiry_id' => $inquiry->id,
                    'md_user_id' => auth()->id(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('success', $request->boolean('is_internal') ? '내부 메모가 저장되었습니다.' : '답변이 등록되었습니다.');
    }

    public function updateInquiryStatus(Request $request, Inquiry $inquiry)
    {
        $this->mdAccess->authorizeInquiry($inquiry);
        $request->validate(['status' => 'required|in:in_progress,answered,reservation_confirmed,consultation_completed']);
        $inquiry->update(['status' => $request->status]);

        try {
            \App\Services\InquiryNotificationService::notifyUserStatusChanged($inquiry);
        } catch (\Throwable $exception) {
            Log::warning('MD inquiry status notification failed', [
                'inquiry_id' => $inquiry->id,
                'md_user_id' => auth()->id(),
                'status' => $request->status,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', '상태가 변경되었습니다.');
    }

    // ── 미디어 ──
    public function media()
    {
        $media = Media::query()
            ->where(fn ($query) => $query
                ->where('uploaded_by', auth()->id())
                ->orWhere(fn ($sub) => $sub
                    ->where('owner_type', 'md_profile')
                    ->where('owner_id', $this->mdAccess->mdProfile()->id))
                ->orWhere(fn ($sub) => $sub
                    ->where('owner_type', 'club')
                    ->whereIn('owner_id', $this->mdAccess->assignedClubIds() ?: [0]))
                ->orWhere(fn ($sub) => $sub
                    ->where('owner_type', 'party')
                    ->whereIn('owner_id', $this->mdAccess->assignedPartyIds() ?: [0])))
            ->when(request('owner_type'), fn ($query, $value) => $query->where('owner_type', $value))
            ->orderBy('owner_type')
            ->orderBy('sort_order')
            ->paginate(30)
            ->withQueryString();
        return view('md-dashboard.media', compact('media'));
    }

    private function formatMinutes(?float $minutes): string
    {
        if ($minutes === null) {
            return '데이터 준비중';
        }

        $rounded = (int) round($minutes);

        if ($rounded < 60) {
            return '평균 ' . $rounded . '분';
        }

        $hours = intdiv($rounded, 60);
        $remain = $rounded % 60;

        if ($remain === 0) {
            return '평균 ' . $hours . '시간';
        }

        return '평균 ' . $hours . '시간 ' . $remain . '분';
    }

    private function formatCurrency(?float $amount): string
    {
        if ($amount === null) {
            return '데이터 준비중';
        }

        return number_format((int) round($amount)) . '원';
    }
}
