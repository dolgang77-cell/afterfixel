<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\UserPreference;
use App\Services\TonightService;
use Illuminate\Http\Request;

class TonightController extends Controller
{
    public function __construct(
        private readonly TonightService $tonight,
    ) {}

    /**
     * 오늘밤 추천 전용 페이지
     */
    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        $area   = $request->get('area');
        $genre  = $request->get('genre');
        $budget = $request->get('budget') ? (int) $request->get('budget') : null;

        $result = $this->tonight->buildTonightSections($sessionId, $pref, $area, $genre, $budget);
        $status = $this->tonight->getStatusSummary();

        return view('tonight.index', [
            'result'  => $result,
            'status'  => $status,
            'pref'    => $pref,
            'filters' => compact('area', 'genre', 'budget'),
            'areas'   => Club::$areas,
            'genres'  => Club::$genres,
        ]);
    }

    /**
     * 빠른 추천 (최소 입력 기반)
     */
    public function quickRecommend(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        $area   = $request->get('area');
        $genre  = $request->get('genre');
        $budget = $request->get('budget') ? (int) $request->get('budget') : null;

        $result = $this->tonight->quickRecommend($sessionId, $pref, $area, $genre, $budget);
        $status = $this->tonight->getStatusSummary();

        if ($request->expectsJson()) {
            return response()->json([
                'result' => $result,
                'status' => $status,
            ]);
        }

        return view('tonight.index', [
            'result'  => $result,
            'status'  => $status,
            'pref'    => $pref,
            'filters' => compact('area', 'genre', 'budget'),
            'areas'   => Club::$areas,
            'genres'  => Club::$genres,
            'isQuick' => true,
        ]);
    }

    /**
     * 상태 요약 (홈 위젯/JSON)
     */
    public function statusSummary(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        $summary = $this->tonight->getHomeTonightSummary($sessionId, $pref);
        $status  = $this->tonight->getStatusSummary();

        return response()->json([
            'summary' => $summary,
            'status'  => $status,
        ]);
    }
}
