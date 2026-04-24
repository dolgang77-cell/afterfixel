<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourRecommendRequest;
use App\Services\TourRecommendationService;
use Illuminate\Http\JsonResponse;

class TourApiController extends Controller
{
    public function __construct(
        private readonly TourRecommendationService $tourService,
    ) {}

    public function recommend(TourRecommendRequest $request): JsonResponse
    {
        $results = $this->tourService->recommend($request->validated());

        return response()->json(['data' => $results]);
    }
}
