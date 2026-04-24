<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Banner;
use App\Models\Club;
use App\Models\ClickLog;
use App\Models\CommunityPost;
use App\Models\Inquiry;
use App\Models\InquiryReply;
use App\Models\NiteNotification;
use App\Models\Party;
use App\Models\TourRecommendation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'clubs'            => Club::count(),
            'active_clubs'     => Club::active()->count(),
            'parties'          => Party::count(),
            'upcoming_parties' => Party::upcoming()->count(),
            'today_parties'    => Party::today()->count(),
            'posts'            => CommunityPost::count(),
            'reported_posts'   => CommunityPost::where('report_count', '>', 0)->where('is_hidden', false)->count(),
            'hidden_posts'     => CommunityPost::where('is_hidden', true)->count(),
            'recommendations'  => TourRecommendation::count(),
            'today_recommendations' => TourRecommendation::whereDate('created_at', today())->count(),
            'banners'          => Banner::active()->count(),
            'today_clicks'     => ClickLog::whereDate('created_at', today())->count(),
            'week_clicks'      => ClickLog::where('created_at', '>=', now()->subWeek())->count(),
            'total_notifications' => NiteNotification::count(),
            'today_notifications' => NiteNotification::whereDate('created_at', today())->count(),
        ];

        // 오늘 운영 중 클럽 수
        $allClubs = Club::active()->get();
        $stats['open_clubs_now'] = $allClubs->filter(fn($c) => $c->isOpenAt(now()->format('H:i')))->count();

        // 최근 7일 클릭 추이
        $weeklyClicks = ClickLog::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // 최근 7일 추천 추이
        $weeklyRecs = TourRecommendation::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $recentPosts = CommunityPost::where('report_count', '>', 0)
            ->where('is_hidden', false)
            ->orderByDesc('report_count')
            ->limit(5)
            ->get();

        $recentLogs = AdminLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // 최근 알림 발송
        $recentNotifications = NiteNotification::latest()
            ->limit(5)
            ->get();

        $inboxSummary = $this->buildInquiryInboxSummary();

        return view('admin.dashboard', compact(
            'stats', 'recentPosts', 'recentLogs', 'recentNotifications',
            'weeklyClicks', 'weeklyRecs', 'inboxSummary'
        ));
    }

    private function buildInquiryInboxSummary(): array
    {
        $openInquiries = Inquiry::with('user', 'assignedMd', 'publicReplies')
            ->whereNotIn('status', ['consultation_completed', 'closed', 'hidden'])
            ->leadInboxOrder()
            ->get();

        $delayedIds = $openInquiries
            ->filter(function (Inquiry $inquiry) {
                if (!in_array($inquiry->status, ['pending', 'in_progress'], true)) {
                    return false;
                }

                $hasAgentReply = $inquiry->publicReplies->contains(
                    fn (InquiryReply $reply) => in_array($reply->author_type, ['md', 'admin'], true)
                );

                return !$hasAgentReply && $inquiry->created_at->lte(now()->subMinutes(30));
            })
            ->pluck('id')
            ->all();

        return [
            'unanswered_count' => $openInquiries->where('status', 'pending')->count(),
            'delayed_count' => count($delayedIds),
            'quote_needed_count' => $openInquiries
                ->whereIn('intent_type', ['quote_request', 'reservation_request'])
                ->whereNotIn('status', ['reservation_confirmed', 'consultation_completed', 'closed', 'hidden'])
                ->count(),
            'confirmation_waiting_count' => $openInquiries->where('status', 'answered')->count(),
            'priority_items' => $openInquiries->take(5),
        ];
    }
}
