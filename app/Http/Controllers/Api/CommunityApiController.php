<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunityPostRequest;
use App\Models\CommunityPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|in:all,review,tip,realtime',
        ]);

        $type = $request->get('type', 'all');

        $posts = CommunityPost::with('club')
            ->visible()
            ->ofType($type)
            ->recent()
            ->paginate(20);

        return response()->json(['data' => $posts]);
    }

    public function store(StoreCommunityPostRequest $request): JsonResponse
    {
        $post = CommunityPost::create(
            $request->validated() + ['session_id' => $request->header('X-Session-Id', session()->getId())]
        );

        return response()->json(['data' => $post], 201);
    }
}
