<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Inquiry;
use App\Models\MdProfile;
use App\Services\ReplyTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $inboxSummary = $this->buildInboxSummary();

        $inquiries = Inquiry::with('user', 'assignedMd', 'publicReplies')
            ->when($request->queue === 'unanswered', fn($q) => $q->where('status', 'pending'))
            ->when($request->queue === 'delayed', fn($q) => $q->whereIn('id', $inboxSummary['delayed_ids'] ?: [0]))
            ->when($request->queue === 'sla_10', fn($q) => $q->whereIn('id', $inboxSummary['sla_10_ids'] ?: [0]))
            ->when($request->queue === 'sla_30', fn($q) => $q->whereIn('id', $inboxSummary['sla_30_ids'] ?: [0]))
            ->when($request->queue === 'sla_60', fn($q) => $q->whereIn('id', $inboxSummary['sla_60_ids'] ?: [0]))
            ->when($request->queue === 'quote_needed', fn($q) => $q
                ->whereIn('intent_type', ['quote_request', 'reservation_request'])
                ->whereNotIn('status', ['reservation_confirmed', 'consultation_completed', 'closed', 'hidden']))
            ->when($request->queue === 'confirmation_waiting', fn($q) => $q->where('status', 'answered'))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->intent_type, fn($q, $v) => $q->where('intent_type', $v))
            ->when($request->target_type, fn($q, $v) => $q->where('target_type', $v))
            ->when($request->md_id, fn($q, $v) => $q->where('assigned_md_id', $v))
            ->leadInboxOrder()
            ->paginate(20)
            ->withQueryString();

        $mds = MdProfile::orderBy('display_name')->pluck('display_name', 'id');

        return view('admin.inquiries.index', compact('inquiries', 'mds', 'inboxSummary'));
    }

    public function show(Inquiry $inquiry, ReplyTemplateService $replyTemplateService)
    {
        $inquiry->load('user', 'replies.author', 'publicReplies.author', 'internalReplies.author', 'assignedMd');
        $mds = MdProfile::active()->orderBy('display_name')->pluck('display_name', 'id');
        $replyTemplates = $replyTemplateService->for('admin', $inquiry);

        return view('admin.inquiries.show', compact('inquiry', 'mds', 'replyTemplates'));
    }

    public function updateStatus(Request $request, Inquiry $inquiry)
    {
        $request->validate(['status' => 'required|in:' . implode(',', array_keys(Inquiry::$statuses))]);
        $oldStatus = $inquiry->status;
        $inquiry->update(['status' => $request->status]);
        AdminLog::record('update_status', 'inquiry', $inquiry->id, ['from' => $oldStatus, 'to' => $request->status]);

        // 상태 변경 알림
        if ($oldStatus !== $request->status) {
            try {
                \App\Services\InquiryNotificationService::notifyUserStatusChanged($inquiry);
            } catch (\Throwable $exception) {
                Log::warning('Admin inquiry status notification failed', [
                    'inquiry_id' => $inquiry->id,
                    'admin_user_id' => auth()->id(),
                    'status' => $request->status,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('success', '문의 상태가 변경되었습니다.');
    }

    public function assignMd(Request $request, Inquiry $inquiry)
    {
        $request->validate(['assigned_md_id' => 'nullable|exists:md_profiles,id']);
        $inquiry->update(['assigned_md_id' => $request->assigned_md_id]);
        AdminLog::record('assign_md', 'inquiry', $inquiry->id, ['md_id' => $request->assigned_md_id]);
        return back()->with('success', '담당 MD가 변경되었습니다.');
    }

    public function reply(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'message'     => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ]);

        $inquiry->addReply('admin', auth()->id(), $request->message, $request->boolean('is_internal'));

        if (!$request->boolean('is_internal')) {
            try {
                \App\Services\InquiryNotificationService::notifyUserNewReply($inquiry, 'admin');
            } catch (\Throwable $exception) {
                Log::warning('Admin inquiry reply notification failed', [
                    'inquiry_id' => $inquiry->id,
                    'admin_user_id' => auth()->id(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        AdminLog::record($request->boolean('is_internal') ? 'internal_note' : 'reply', 'inquiry', $inquiry->id);

        return back()->with('success', $request->boolean('is_internal') ? '내부 메모가 저장되었습니다.' : '답변이 등록되었습니다.');
    }

    private function buildInboxSummary(): array
    {
        $openInquiries = Inquiry::with('publicReplies')
            ->whereNotIn('status', ['consultation_completed', 'closed', 'hidden'])
            ->get();

        $slaTracked = $openInquiries
            ->filter(fn (Inquiry $inquiry) => $inquiry->responseDelayMinutes() !== null)
            ->values();

        $sla10Ids = $slaTracked
            ->filter(fn (Inquiry $inquiry) => ($inquiry->responseDelayMinutes() ?? 0) >= 10)
            ->pluck('id')
            ->all();

        $sla30Ids = $slaTracked
            ->filter(fn (Inquiry $inquiry) => ($inquiry->responseDelayMinutes() ?? 0) >= 30)
            ->pluck('id')
            ->all();

        $sla60Ids = $slaTracked
            ->filter(fn (Inquiry $inquiry) => ($inquiry->responseDelayMinutes() ?? 0) >= 60)
            ->pluck('id')
            ->all();

        return [
            'unanswered_count' => $openInquiries->where('status', 'pending')->count(),
            'delayed_count' => count($sla30Ids),
            'sla_10_count' => count($sla10Ids),
            'sla_30_count' => count($sla30Ids),
            'sla_60_count' => count($sla60Ids),
            'quote_needed_count' => $openInquiries
                ->whereIn('intent_type', ['quote_request', 'reservation_request'])
                ->whereNotIn('status', ['reservation_confirmed', 'consultation_completed', 'closed', 'hidden'])
                ->count(),
            'confirmation_waiting_count' => $openInquiries->where('status', 'answered')->count(),
            'delayed_ids' => $sla30Ids,
            'sla_10_ids' => $sla10Ids,
            'sla_30_ids' => $sla30Ids,
            'sla_60_ids' => $sla60Ids,
        ];
    }
}
