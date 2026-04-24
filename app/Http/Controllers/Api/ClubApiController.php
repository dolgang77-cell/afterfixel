<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClubApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'area'      => 'nullable|string|max:20',
            'genre'     => 'nullable|string|max:20',
            'foreigner' => 'nullable|boolean',
        ]);

        $clubs = Club::active()
            ->inArea($request->get('area'))
            ->inGenre($request->get('genre'))
            ->when($request->get('foreigner'), fn($q) => $q->foreignerFriendly())
            ->orderByDesc('rating_avg')
            ->get();

        return response()->json(['data' => $clubs]);
    }

    public function show(Club $club): JsonResponse
    {
        $club->recordView();
        $club->load('upcomingParties');

        return response()->json(['data' => $club]);
    }
}
