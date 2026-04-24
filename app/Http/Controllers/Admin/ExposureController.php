<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Banner;
use App\Models\Club;
use App\Models\Party;
use Illuminate\Http\Request;

class ExposureController extends Controller
{
    public function index()
    {
        // 홈 노출 클럽 (sort_order 기준, 0이면 미설정)
        $clubs = Club::active()
            ->orderByDesc('sort_order')
            ->orderByDesc('rating_avg')
            ->get();

        // 오늘/예정 파티
        $parties = Party::with('club')
            ->where('event_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        // 배너 (위치별 그룹)
        $banners = Banner::orderBy('position')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('position');

        $positionLabels = [
            'home_top'    => '홈 상단',
            'home_middle' => '홈 중간',
            'party_top'   => '파티 상단',
            'club_top'    => '클럽 상단',
        ];

        return view('admin.exposure.index', compact('clubs', 'parties', 'banners', 'positionLabels'));
    }

    /**
     * 클럽 노출 순서 일괄 저장
     */
    public function updateClubOrder(Request $request)
    {
        $request->validate([
            'orders'   => 'required|array',
            'orders.*' => 'integer|min:0',
        ]);

        $updated = 0;
        foreach ($request->orders as $clubId => $sortOrder) {
            $club = Club::find($clubId);
            if ($club && $club->sort_order != $sortOrder) {
                $club->update(['sort_order' => $sortOrder]);
                $updated++;
            }
        }

        if ($updated) {
            AdminLog::record('update_priority', 'club', null, ['count' => $updated, 'action' => 'bulk_order']);
        }

        return back()->with('success', "{$updated}개 클럽의 노출 순서가 변경되었습니다.");
    }

    /**
     * 파티 홈 고정 토글
     */
    public function togglePartyPin(Request $request, Party $party)
    {
        $current = $party->sort_order ?? 0;
        $newOrder = $current > 0 ? 0 : 100;
        $party->update(['sort_order' => $newOrder]);

        $action = $newOrder > 0 ? '고정' : '고정 해제';
        AdminLog::record('update_priority', 'party', $party->id, ['action' => $action]);

        return back()->with('success', "파티 '{$party->name}'이(가) {$action}되었습니다.");
    }
}
