<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Party;
use App\Models\UserPreference;
use App\Models\ProfileImage;
use App\Models\RecentView;
use App\Services\RevisitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MyPageController extends Controller
{
    public function __construct(
        private readonly RevisitHubService $revisitHub,
    ) {}

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::forSession($sessionId);
        $revisitHub = $this->revisitHub->build($sessionId, auth()->user());
        $favCount = $revisitHub['favCount'];
        $recentCount = $revisitHub['recentCount'];
        $unreadCount = $revisitHub['unreadCount'];

        $currentProfileImage = null;
        $latestProfileImage = null;
        $displayProfileImage = null;
        $defaultProfileImageUrl = asset('images/default-profile.svg');

        $user = auth()->user();

        if ($user && Schema::hasTable('profile_images')) {
            $currentProfileImage = ProfileImage::where('user_id', $user->id)
                ->current()
                ->latest('approved_at')
                ->first();

            $latestProfileImage = ProfileImage::where('user_id', $user->id)
                ->latest()
                ->first();

            $displayProfileImage = $currentProfileImage ?? $latestProfileImage;
        }

        return view('my.index', compact(
            'pref', 'favCount', 'recentCount', 'unreadCount',
            'revisitHub',
            'currentProfileImage', 'latestProfileImage', 'displayProfileImage', 'defaultProfileImageUrl'
        ));
    }

    public function recentViews(Request $request)
    {
        $sessionId = $request->session()->getId();
        $tab = $request->get('tab', 'all');

        $query = RecentView::forSession($sessionId)->orderByDesc('viewed_at');

        if ($tab !== 'all') {
            $query->ofType($tab);
        }

        $views = $query->limit(80)
            ->get()
            ->map(fn ($rv) => $this->resolveTarget($rv))
            ->filter(fn ($rv) => $rv->target !== null)
            ->take(50)
            ->values();

        return view('my.recent', compact('views', 'tab'));
    }

    public function preferences(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::forSession($sessionId);

        return view('my.preferences', compact('pref'));
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'preferred_areas'    => 'array',
            'preferred_areas.*'  => 'in:' . implode(',', Club::$areas),
            'preferred_genres'   => 'array',
            'preferred_genres.*' => 'in:' . implode(',', Club::$genres),
            'budget_min'         => 'nullable|integer|min:0',
            'budget_max'         => 'nullable|integer|min:0',
            'foreigner_mode'     => 'boolean',
        ]);

        $sessionId = $request->session()->getId();
        $pref = UserPreference::forSession($sessionId);
        $pref->update([
            'preferred_areas'  => $validated['preferred_areas'] ?? [],
            'preferred_genres' => $validated['preferred_genres'] ?? [],
            'budget_min'       => $validated['budget_min'] ?? null,
            'budget_max'       => $validated['budget_max'] ?? null,
            'foreigner_mode'   => $request->boolean('foreigner_mode'),
            'user_id'          => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '관심 설정이 저장되었습니다.');
    }

    private function resolveTarget(RecentView $rv): object
    {
        $target = match ($rv->target_type) {
            'club' => Club::find($rv->target_id),
            'party' => Party::with('club')->find($rv->target_id),
            'tour' => \App\Models\TourRecommendation::find($rv->target_id),
            default => null,
        };

        return (object) [
            'type' => $rv->target_type,
            'id' => $rv->target_id,
            'target' => $target,
            'viewed_at' => $rv->viewed_at,
        ];
    }
}
