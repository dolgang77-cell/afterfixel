<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Inquiry;
use App\Models\NiteNotification;
use App\Models\Party;
use App\Services\InquiryNotificationService;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InquiryController extends Controller
{
    public function store(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['club', 'party'])) abort(404);
        if (!auth()->check()) return redirect()->route('login')->with('error', '로그인 후 문의할 수 있습니다.');

        $target = match ($type) {
            'club' => Club::query()->find($id),
            'party' => Party::query()->find($id),
            default => null,
        };

        abort_unless($target, 404);

        $data = $request->validate([
            'intent_type'       => 'nullable|in:question,quote_request,reservation_request',
            'subject'           => 'nullable|string|max:200',
            'message'           => 'required|string|max:3000',
            'preferred_contact' => 'nullable|string|max:100',
            'visit_date'        => 'nullable|date',
            'party_size'        => 'nullable|integer|min:1|max:100',
            'budget_min'        => 'nullable|integer|min:0',
            'budget_max'        => 'nullable|integer|min:0',
            'visit_time_slot'   => 'nullable|in:before_22,22_24,after_24,flexible',
            'gender_mix'        => 'nullable|string|max:50',
            'special_request'   => 'nullable|string|max:2000',
        ]);

        if (
            isset($data['budget_min'], $data['budget_max'])
            && $data['budget_min'] !== null
            && $data['budget_max'] !== null
            && $data['budget_min'] > $data['budget_max']
        ) {
            throw ValidationException::withMessages([
                'budget_min' => '최소 예산은 최대 예산보다 클 수 없습니다.',
            ]);
        }

        $intentType = $data['intent_type'] ?? 'question';
        $subject = filled($data['subject'] ?? null)
            ? trim($data['subject'])
            : $this->buildInquirySubject($type, $target->name, $intentType);

        $assignedMdId = Inquiry::assignMd($type, $id);

        $inquiry = Inquiry::create([
            'user_id'            => auth()->id(),
            'target_type'        => $type,
            'target_id'          => $id,
            'assigned_md_id'     => $assignedMdId,
            'status'             => 'pending',
            'intent_type'        => $intentType,
            'subject'            => $subject,
            'message'            => $data['message'],
            'preferred_contact'  => $data['preferred_contact'] ?? null,
            'visit_date'         => $data['visit_date'] ?? null,
            'party_size'         => $data['party_size'] ?? null,
            'budget_min'         => $data['budget_min'] ?? null,
            'budget_max'         => $data['budget_max'] ?? null,
            'visit_time_slot'    => $data['visit_time_slot'] ?? null,
            'gender_mix'         => $data['gender_mix'] ?? null,
            'special_request'    => $data['special_request'] ?? null,
        ]);

        try {
            InquiryNotificationService::notifyUserInquiryCreated($inquiry);
            InquiryNotificationService::notifyMdNewInquiry($inquiry);
        } catch (\Throwable $exception) {
            Log::warning('Inquiry notification dispatch failed', [
                'inquiry_id' => $inquiry->id,
                'user_id' => auth()->id(),
                'target_type' => $type,
                'target_id' => $id,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', '문의가 접수되었습니다. 마이페이지에서 답변을 확인할 수 있습니다.');
    }

    private function buildInquirySubject(string $type, string $targetName, string $intentType): string
    {
        $suffix = match ($intentType) {
            'quote_request' => '견적 문의',
            'reservation_request' => '예약 문의',
            default => '문의',
        };

        $prefix = $type === 'party' ? '파티' : '클럽';

        return sprintf('%s %s - %s', $prefix, $targetName, $suffix);
    }

    /**
     * 마이페이지 - 내 문의 목록
     */
    public function myInquiries()
    {
        if (!auth()->check()) return redirect()->route('login');

        $inquiries = Inquiry::where('user_id', auth()->id())
            ->with('assignedMd', 'publicReplies')
            ->latest()
            ->paginate(20);

        $reminderNotifications = NiteNotification::where('type', 'inquiry_reminder')
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->filter(fn (NiteNotification $notification) => in_array((int) data_get($notification->data, 'inquiry_id'), $inquiries->pluck('id')->all(), true))
            ->groupBy(fn (NiteNotification $notification) => (int) data_get($notification->data, 'inquiry_id'))
            ->map(fn ($group) => $group->sortByDesc('created_at')->first()?->created_at);

        $inquiries->getCollection()->transform(function (Inquiry $inquiry) use ($reminderNotifications) {
            $inquiry->setAttribute('reminder_meta', $this->buildReminderMeta(
                $inquiry,
                $reminderNotifications->get($inquiry->id)
            ));

            return $inquiry;
        });

        return view('my.inquiries', compact('inquiries'));
    }

    /**
     * 마이페이지 - 문의 상세 + 답변 확인
     */
    public function showMyInquiry(Inquiry $inquiry)
    {
        if ($inquiry->user_id !== auth()->id()) abort(403);

        $inquiry->load('publicReplies.author', 'assignedMd');
        $inquiry->setAttribute('reminder_meta', $this->buildReminderMeta(
            $inquiry,
            InquiryNotificationService::latestReminderAt($inquiry)
        ));

        return view('my.inquiry-show', compact('inquiry'));
    }

    /**
     * 회원 추가 메시지
     */
    public function addMessage(Request $request, Inquiry $inquiry)
    {
        if ($inquiry->user_id !== auth()->id()) abort(403);
        if ($inquiry->status === 'closed') return back()->with('error', '종료된 문의입니다.');

        $request->validate(['message' => 'required|string|max:2000']);

        $inquiry->addReply('user', auth()->id(), $request->message);

        try {
            InquiryNotificationService::notifyMdUserFollowUp($inquiry);
        } catch (\Throwable $exception) {
            Log::warning('Inquiry follow-up notification dispatch failed', [
                'inquiry_id' => $inquiry->id,
                'user_id' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', '메시지가 전송되었습니다.');
    }

    public function sendReminder(Inquiry $inquiry)
    {
        if ($inquiry->user_id !== auth()->id()) abort(403);

        $inquiry->loadMissing('publicReplies');
        $meta = $this->buildReminderMeta($inquiry, InquiryNotificationService::latestReminderAt($inquiry));

        if (!($meta['eligible'] ?? false)) {
            return back()->with('error', $meta['message'] ?? '아직 재알림을 보낼 수 없습니다.');
        }

        try {
            InquiryNotificationService::notifyMdReminderRequested($inquiry);
        } catch (\Throwable $exception) {
            Log::warning('Inquiry reminder notification dispatch failed', [
                'inquiry_id' => $inquiry->id,
                'user_id' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', '재알림 전송 중 문제가 발생했습니다.');
        }

        return back()->with('success', '담당자에게 다시 알림을 보냈습니다.');
    }

    private function buildReminderMeta(Inquiry $inquiry, ?CarbonInterface $lastReminderAt): array
    {
        if (!in_array($inquiry->status, ['pending', 'in_progress'], true)) {
            return [
                'eligible' => false,
                'message' => '답변 대기 중인 문의만 재알림할 수 있습니다.',
            ];
        }

        $lastConversationAt = collect([$inquiry->created_at])
            ->merge($inquiry->publicReplies->pluck('created_at'))
            ->filter()
            ->max();

        $eligibleAt = $lastConversationAt?->copy()->addMinutes(30) ?? now();

        if ($lastReminderAt) {
            $cooldownAt = $lastReminderAt->copy()->addHours(6);
            if ($cooldownAt->greaterThan($eligibleAt)) {
                $eligibleAt = $cooldownAt;
            }
        }

        if (now()->lt($eligibleAt)) {
            return [
                'eligible' => false,
                'message' => '재알림 가능까지 ' . now()->diffForHumans($eligibleAt, [
                    'parts' => 2,
                    'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                ]),
                'available_at' => $eligibleAt,
            ];
        }

        return [
            'eligible' => true,
            'message' => '담당자에게 다시 알림 보내기',
            'available_at' => $eligibleAt,
        ];
    }
}
