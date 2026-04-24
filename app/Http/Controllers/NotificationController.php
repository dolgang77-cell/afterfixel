<?php

namespace App\Http\Controllers;

use App\Models\NiteNotification;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        $notifications = NiteNotification::forViewer($sessionId, $userId)
            ->latest()
            ->paginate(20);

        $this->attachEventContext($notifications);

        // 페이지 방문 시 모든 미읽음을 읽음 처리
        NiteNotification::forViewer($sessionId, $userId)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(Request $request)
    {
        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        $count = NiteNotification::forViewer($sessionId, $userId)->unread()->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, NiteNotification $notification)
    {
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return $notification->link ? redirect($notification->link) : back();
    }

    public function markAllRead(Request $request)
    {
        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        NiteNotification::forViewer($sessionId, $userId)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '모든 알림을 읽음 처리했습니다.');
    }

    private function attachEventContext(LengthAwarePaginator $notifications): void
    {
        $collection = $notifications->getCollection();
        $partyIds = $collection
            ->map(function (NiteNotification $notification) {
                $data = $notification->data ?? [];

                if (($data['target_type'] ?? null) === 'party' && !empty($data['target_id'])) {
                    return (int) $data['target_id'];
                }

                if (!empty($data['party_id'])) {
                    return (int) $data['party_id'];
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        $parties = Party::query()
            ->whereIn('id', $partyIds)
            ->get()
            ->keyBy('id');

        $notifications->setCollection(
            $collection->map(function (NiteNotification $notification) use ($parties) {
                $data = $notification->data ?? [];
                $context = null;

                if ($notification->type === NiteNotification::TYPE_TONIGHT_RECOMMENDATION) {
                    $badges = [];

                    if (!empty($data['verified_event_count'])) {
                        $badges[] = ['label' => '실이벤트 ' . $data['verified_event_count'] . '개', 'variant' => 'green'];
                    }

                    if (!empty($data['operating_card_count'])) {
                        $badges[] = ['label' => '운영형 카드 ' . $data['operating_card_count'] . '개', 'variant' => 'cyan'];
                    }

                    if (!empty($badges)) {
                        $context = [
                            'badges' => $badges,
                            'notice' => '오늘밤 추천에서는 실제 확정 이벤트와 운영형 대표 세션 카드를 구분해 보여줍니다.',
                        ];
                    }
                } else {
                    $partyId = null;

                    if (($data['target_type'] ?? null) === 'party' && !empty($data['target_id'])) {
                        $partyId = (int) $data['target_id'];
                    } elseif (!empty($data['party_id'])) {
                        $partyId = (int) $data['party_id'];
                    }

                    $party = $partyId ? $parties->get($partyId) : null;
                    $label = $data['event_card_label'] ?? $party?->event_card_label;
                    $type = $data['event_card_type'] ?? $party?->event_card_type;
                    $notice = $data['event_card_notice'] ?? $party?->event_card_notice;

                    if ($label) {
                        $context = [
                            'badges' => [[
                                'label' => $label,
                                'variant' => $type === 'verified_event' ? 'green' : ($type === 'operating_card' ? 'cyan' : 'default'),
                            ]],
                            'notice' => $notice,
                        ];
                    }
                }

                $notification->event_context = $context;

                return $notification;
            })
        );
    }
}
