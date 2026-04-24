<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Party;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartyApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date'  => 'nullable|date',
            'area'  => 'nullable|string|max:20',
            'genre' => 'nullable|string|max:20',
        ]);

        $parties = Party::with('club')
            ->upcoming()
            ->onDate($request->get('date'))
            ->inArea($request->get('area'))
            ->inGenre($request->get('genre'))
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        $parties->each->append([
            'event_card_type',
            'event_card_label',
            'event_card_variant',
            'event_card_notice',
            'is_verified_event',
            'is_operating_card',
        ]);

        return response()->json(['data' => $parties]);
    }

    public function show(Party $party): JsonResponse
    {
        $party->recordView();
        $party->load('club');
        $party->append([
            'event_card_type',
            'event_card_label',
            'event_card_variant',
            'event_card_notice',
            'is_verified_event',
            'is_operating_card',
        ]);

        return response()->json(['data' => $party]);
    }
}
