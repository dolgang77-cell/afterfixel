<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\NotificationSetting;
use App\Models\SavedFilter;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function edit(Request $request)
    {
        $sessionId = $request->session()->getId();
        $setting = NotificationSetting::forSession($sessionId);
        $savedFilters = SavedFilter::listForViewer($sessionId, auth()->id());
        $savedFilterFeatureAvailable = SavedFilter::available();

        return view('notifications.settings', compact('setting', 'savedFilters', 'savedFilterFeatureAvailable'));
    }

    public function update(Request $request)
    {
        $sessionId = $request->session()->getId();
        $setting = NotificationSetting::forSession($sessionId);

        $validated = $request->validate([
            'enabled'                => 'boolean',
            'remind_before'          => 'array',
            'remind_before.*'        => 'in:' . implode(',', array_keys(NotificationSetting::$remindOptions)),
            'preferred_areas'        => 'array',
            'preferred_areas.*'      => 'in:' . implode(',', Club::$areas),
            'preferred_genres'       => 'array',
            'preferred_genres.*'     => 'in:' . implode(',', Club::$genres),
            'new_party_alert'        => 'boolean',
            'tonight_recommendation' => 'boolean',
        ]);

        $setting->update([
            'enabled'                => $request->boolean('enabled'),
            'remind_before'          => $validated['remind_before'] ?? [],
            'preferred_areas'        => $validated['preferred_areas'] ?? [],
            'preferred_genres'       => $validated['preferred_genres'] ?? [],
            'new_party_alert'        => $request->boolean('new_party_alert'),
            'tonight_recommendation' => $request->boolean('tonight_recommendation'),
            'user_id'                => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '알림 설정이 저장되었습니다.');
    }
}
