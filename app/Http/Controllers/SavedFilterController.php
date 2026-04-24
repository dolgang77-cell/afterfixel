<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\SavedFilter;
use Illuminate\Http\Request;

class SavedFilterController extends Controller
{
    public function store(Request $request, string $type)
    {
        if (!in_array($type, ['club', 'party'], true)) {
            abort(404);
        }

        if (!SavedFilter::available()) {
            return back()->with('info', '저장 필터 기능은 마이그레이션 적용 후 활성화됩니다.');
        }

        $validated = $request->validate([
            'area' => 'nullable|in:' . implode(',', Club::$areas),
            'genre' => 'nullable|in:' . implode(',', Club::$genres),
            'date' => $type === 'party' ? 'nullable|date' : 'nullable',
            'foreigner' => $type === 'club' ? 'nullable|boolean' : 'nullable',
            'redirect_to' => 'nullable|string|max:500',
        ]);

        $filters = SavedFilter::normalizeFilters($type, $validated);

        if (empty($filters)) {
            return back()->with('error', '저장할 필터를 먼저 선택해 주세요.');
        }

        SavedFilter::saveForViewer($request->session()->getId(), auth()->id(), $type, $filters);

        return redirect($validated['redirect_to'] ?? url()->previous() ?: route($type === 'club' ? 'clubs.index' : 'parties.index'))
            ->with('success', '저장 필터에 추가되었습니다.');
    }

    public function destroy(Request $request, SavedFilter $savedFilter)
    {
        $sessionId = $request->session()->getId();

        if ($savedFilter->session_id !== $sessionId && $savedFilter->user_id !== auth()->id()) {
            abort(403);
        }

        $redirectTo = $request->input('redirect_to');
        $savedFilter->delete();

        return redirect($redirectTo ?: url()->previous() ?: route('notification-settings.edit'))
            ->with('success', '저장 필터가 해제되었습니다.');
    }
}
