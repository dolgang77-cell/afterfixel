<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Party;
use App\Models\CommunityPost;
use Illuminate\Http\JsonResponse;

class HomeApiController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'data' => [
                    'today_parties' => Party::with('club')->today()->upcoming()->orderBy('start_time')->get(),
                    'hot_clubs'     => Club::active()->orderByDesc('rating_avg')->limit(6)->get(),
                    'recent_posts'  => CommunityPost::with('club')->visible()->recent()->limit(5)->get(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => '데이터를 불러올 수 없습니다.'], 500);
        }
    }
}
