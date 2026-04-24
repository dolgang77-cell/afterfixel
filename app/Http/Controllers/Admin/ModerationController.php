<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ForbiddenWord;
use App\Models\MessageReport;
use App\Models\ModerationPolicy;
use App\Models\Report;
use App\Models\User;
use App\Models\UserModerationAction;
use App\Services\ForbiddenWordFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModerationController extends Controller
{
    // ── 신고 관리 ──

    public function reports(Request $request)
    {
        $reports = Report::with('reporter')
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->target_type, fn($q, $v) => $q->where('target_type', $v))
            ->orderByDesc('created_at')
            ->paginate(30)->withQueryString();

        $pendingCount = Report::pending()->count();
        return view('admin.moderation.reports', compact('reports', 'pendingCount'));
    }

    public function reviewReport(Request $request, Report $report)
    {
        $request->validate(['status' => 'required|in:reviewed,dismissed']);
        $report->update([
            'status'      => $request->status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        AdminLog::record($request->status, 'report', $report->id);
        return back()->with('success', '신고가 처리되었습니다.');
    }

    public function messageReports(Request $request)
    {
        abort_unless(Schema::hasTable('message_reports'), 404);

        $reports = MessageReport::with(['reporter', 'reportedUser'])
            ->when($request->status, fn ($query, $value) => $query->where('status', $value))
            ->when($request->reason, fn ($query, $value) => $query->where('reason', $value))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $pendingCount = MessageReport::pending()->count();

        return view('admin.moderation.message-reports', compact('reports', 'pendingCount'));
    }

    public function reviewMessageReport(Request $request, MessageReport $messageReport)
    {
        abort_unless(Schema::hasTable('message_reports'), 404);

        $data = $request->validate([
            'status' => 'required|in:reviewed,dismissed',
            'action_type' => 'nullable|in:warning,restrict_write,restrict_upload,suspend,ban',
            'duration' => 'nullable|integer|min:1|max:365',
            'note' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($data, $messageReport) {
            $messageReport->update([
                'status' => $data['status'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            if (!empty($data['action_type']) && $messageReport->reported_user_id) {
                UserModerationAction::create([
                    'user_id' => $messageReport->reported_user_id,
                    'action_type' => $data['action_type'],
                    'reason' => 'reports',
                    'note' => $data['note'] ?? '메시지 신고 검토 후 자동 제재',
                    'starts_at' => now(),
                    'ends_at' => !in_array($data['action_type'], ['ban'], true) && !empty($data['duration'])
                        ? now()->addDays((int) $data['duration'])
                        : null,
                    'is_permanent' => $data['action_type'] === 'ban',
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);

                if (in_array($data['action_type'], ['suspend', 'ban'], true)) {
                    User::whereKey($messageReport->reported_user_id)
                        ->update(['status' => $data['action_type'] === 'ban' ? 'banned' : 'suspended']);
                }
            }
        });

        AdminLog::record('review_message_report', 'message_report', $messageReport->id, [
            'status' => $data['status'],
            'action_type' => $data['action_type'] ?? null,
            'reported_user_id' => $messageReport->reported_user_id,
        ]);

        return back()->with('success', '메시지 신고가 처리되었습니다.');
    }

    // ── 차단 유저 ──

    public function bannedUsers(Request $request)
    {
        $actions = UserModerationAction::with('user', 'admin')
            ->where('is_active', true)
            ->when($request->action_type, fn($q, $v) => $q->where('action_type', $v))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('admin.moderation.banned-users', compact('actions'));
    }

    public function createAction(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'action_type' => 'required|in:warning,restrict_write,restrict_upload,suspend,ban',
            'reason'      => 'required|in:abuse,spam,ad,adult,reports,manual,other',
            'note'        => 'nullable|string|max:500',
            'duration'    => 'nullable|integer|min:1',        // 일수
            'is_permanent' => 'boolean',
        ]);

        UserModerationAction::create([
            'user_id'      => $data['user_id'],
            'action_type'  => $data['action_type'],
            'reason'       => $data['reason'],
            'note'         => $data['note'] ?? null,
            'starts_at'    => now(),
            'ends_at'      => !($data['is_permanent'] ?? false) && isset($data['duration'])
                ? now()->addDays($data['duration']) : null,
            'is_permanent' => $data['is_permanent'] ?? false,
            'is_active'    => true,
            'created_by'   => auth()->id(),
        ]);

        // 심각한 제재 시 사용자 상태도 변경
        if (in_array($data['action_type'], ['suspend', 'ban'])) {
            \App\Models\User::where('id', $data['user_id'])
                ->update(['status' => $data['action_type'] === 'ban' ? 'banned' : 'suspended']);
        }

        AdminLog::record('moderate', 'user', $data['user_id'], [
            'action' => $data['action_type'],
            'reason' => $data['reason'],
        ]);

        return back()->with('success', '제재가 적용되었습니다.');
    }

    public function deactivateAction(UserModerationAction $action)
    {
        $action->update(['is_active' => false]);

        // 활성 제재가 없으면 사용자 상태 복구
        $hasActive = UserModerationAction::where('user_id', $action->user_id)
            ->where('is_active', true)
            ->whereIn('action_type', ['suspend', 'ban'])
            ->exists();

        if (!$hasActive) {
            \App\Models\User::where('id', $action->user_id)->update(['status' => 'active']);
        }

        AdminLog::record('lift_moderation', 'user', $action->user_id);
        return back()->with('success', '제재가 해제되었습니다.');
    }

    // ── 금칙어 관리 ──

    public function forbiddenWords()
    {
        $words = ForbiddenWord::orderBy('category')->orderBy('word')->paginate(50);
        return view('admin.moderation.forbidden-words', compact('words'));
    }

    public function storeForbiddenWord(Request $request)
    {
        $data = $request->validate([
            'word'        => 'required|string|max:100',
            'match_type'  => 'required|in:exact,contains,regex',
            'action_type' => 'required|in:block,mask,review,warn',
            'category'    => 'required|in:abuse,spam,adult,discrimination,general',
            'severity'    => 'required|integer|min:1|max:5',
        ]);
        ForbiddenWord::create($data);
        ForbiddenWordFilter::clearCache();
        return back()->with('success', '금칙어가 등록되었습니다.');
    }

    public function destroyForbiddenWord(ForbiddenWord $word)
    {
        $word->delete();
        ForbiddenWordFilter::clearCache();
        return back()->with('success', '금칙어가 삭제되었습니다.');
    }

    public function toggleForbiddenWord(ForbiddenWord $word)
    {
        $word->update(['is_active' => !$word->is_active]);
        ForbiddenWordFilter::clearCache();
        return back()->with('success', $word->is_active ? '활성화됨' : '비활성화됨');
    }

    // ── 정책 설정 ──

    public function policies()
    {
        $policies = ModerationPolicy::all();
        return view('admin.moderation.policies', compact('policies'));
    }

    public function updatePolicies(Request $request)
    {
        foreach ($request->input('policies', []) as $key => $value) {
            ModerationPolicy::where('key', $key)->update(['value' => $value]);
        }
        ModerationPolicy::clearCache();
        AdminLog::record('update', 'moderation_policy', null);
        return back()->with('success', '정책이 저장되었습니다.');
    }
}
