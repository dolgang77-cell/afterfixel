<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Favorite;
use App\Models\Party;
use App\Models\TourRecommendation;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['club', 'party', 'tour'])) {
            abort(404);
        }

        $sessionId = $request->session()->getId();
        $favorited = Favorite::toggle($sessionId, auth()->id(), $type, $id);

        if ($request->expectsJson()) {
            return response()->json(['favorited' => $favorited]);
        }

        return back();
    }

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $tab = $request->get('tab', 'party');

        $parties = Favorite::forSession($sessionId)->ofType('party')
            ->latest()->get()->pluck('target_id');
        $clubs = Favorite::forSession($sessionId)->ofType('club')
            ->latest()->get()->pluck('target_id');
        $tours = Favorite::forSession($sessionId)->ofType('tour')
            ->latest()->get()->pluck('target_id');

        $data = [
            'tab'     => $tab,
            'parties' => Party::with('club')->whereIn('id', $parties)->orderByDesc('event_date')->get(),
            'clubs'   => Club::whereIn('id', $clubs)->get(),
            'tours'   => TourRecommendation::whereIn('id', $tours)->latest()->get(),
            'counts'  => [
                'party' => $parties->count(),
                'club'  => $clubs->count(),
                'tour'  => $tours->count(),
            ],
        ];

        return view('my.favorites', $data);
    }
}
