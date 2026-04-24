<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ModerationService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $data = $request->validate([
            'target_type' => 'required|in:community_post,review,media',
            'target_id'   => 'required|integer',
            'reason'      => 'required|in:' . implode(',', array_keys(Report::$reasons)),
            'detail'      => 'nullable|string|max:500',
        ]);

        // 중복 신고 방지
        $exists = Report::where('reporter_id', auth()->id())
            ->where('target_type', $data['target_type'])
            ->where('target_id', $data['target_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', '이미 신고한 항목입니다.');
        }

        Report::create($data + ['reporter_id' => auth()->id()]);

        // 자동숨김 처리
        ModerationService::processReport($data['target_type'], $data['target_id']);

        return back()->with('success', '신고가 접수되었습니다.');
    }
}
