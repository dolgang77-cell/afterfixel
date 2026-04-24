<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\NearbyMessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationApiController extends Controller
{
    public function __construct(
        private readonly NearbyMessagingService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        return response()->json($this->service->listConversations($request->user()));
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'recipient_user_id' => 'required|integer|exists:users,id',
        ]);

        $conversation = $this->service->startConversation(
            $request->user(),
            User::findOrFail($validated['recipient_user_id'])
        );

        return response()->json([
            'data' => [
                'id' => $conversation->id,
            ],
        ], 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        return response()->json([
            'data' => $this->service->getConversation($request->user(), $conversation),
        ]);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'after_id' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json(
            $this->service->getMessages(
                $request->user(),
                $conversation,
                isset($validated['after_id']) ? (int) $validated['after_id'] : null,
                (int) ($validated['limit'] ?? 50)
            )
        );
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:500',
        ]);

        return response()->json([
            'data' => $this->service->sendMessage($request->user(), $conversation, $validated['body']),
        ], 201);
    }

    public function read(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        return response()->json([
            'updated' => $this->service->markConversationRead($request->user(), $conversation),
        ]);
    }

    public function leave(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $this->service->leaveConversation($request->user(), $conversation);

        return response()->json(['success' => true]);
    }

    public function report(Request $request, Message $message): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'reason' => 'required|in:abuse,spam,adult,false_info,privacy,other',
            'detail' => 'nullable|string|max:1000',
        ]);

        return response()->json([
            'data' => $this->service->reportMessage($request->user(), $message, $validated),
        ], 201);
    }

    public function block(Request $request, User $user): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:120',
        ]);

        return response()->json([
            'data' => $this->service->blockUser($request->user(), $user, $validated['reason'] ?? null),
        ], 201);
    }

    public function unblock(Request $request, User $user): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $this->service->unblockUser($request->user(), $user);

        return response()->json(['success' => true]);
    }
}
