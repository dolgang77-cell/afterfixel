<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NearbyMessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NearbyUserApiController extends Controller
{
    public function __construct(
        private readonly NearbyMessagingService $service,
    ) {}

    public function status(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        return response()->json([
            'data' => $this->service->visibilityStatusFor($request->user()),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:20',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:30',
            'intentions' => 'nullable|array',
            'intentions.*' => 'string|max:30',
            'foreign_only' => 'nullable|boolean',
        ]);

        return response()->json($this->service->getNearbyUsers($request->user(), $validated));
    }

    public function updateSettings(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'share_scope' => 'nullable|in:off,venue_only,nearby',
            'hide_exact_venue' => 'nullable|boolean',
            'foreign_mode' => 'nullable|boolean',
            'preferred_languages' => 'nullable|array',
            'preferred_languages.*' => 'string|max:20',
            'preferred_interests' => 'nullable|array',
            'preferred_interests.*' => 'string|max:30',
            'preferred_intentions' => 'nullable|array',
            'preferred_intentions.*' => 'string|max:30',
            'profile_gender' => 'nullable|string|max:20',
            'profile_age_band' => 'nullable|string|max:20',
            'auto_hide_after_minutes' => 'nullable|integer|min:1|max:60',
        ]);

        return response()->json([
            'data' => $this->service->updateVisibility($request->user(), $validated),
        ]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy_m' => 'nullable|numeric|min:0|max:10000',
            'venue_type' => 'nullable|in:club,party',
            'venue_id' => 'nullable|integer|min:1',
            'source' => 'nullable|string|max:20',
        ]);

        return response()->json([
            'data' => $this->service->updateLocation($request->user(), $validated, $request->session()->getId()),
        ]);
    }

    public function checkin(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'venue_type' => 'required|in:club,party',
            'venue_id' => 'required|integer|min:1',
            'source' => 'nullable|string|max:20',
        ]);

        return response()->json([
            'data' => $this->service->upsertVenueCheckin(
                $request->user(),
                $validated['venue_type'],
                (int) $validated['venue_id'],
                $validated['source'] ?? 'manual'
            ),
        ], 201);
    }
}
