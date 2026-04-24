<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\RecentView;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\NotificationSetting;
use App\Models\NiteNotification;
use App\Models\SavedFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('my.index');
        }

        return view('auth.register');
    }

    public function checkNickname(Request $request)
    {
        $request->validate(['nickname' => 'required|string|min:2|max:20']);
        $exists = User::where('nickname', $request->nickname)->exists();
        return response()->json(['available' => !$exists]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nickname' => 'required|string|min:2|max:20|unique:users,nickname|regex:/^[a-zA-Z0-9가-힣_]+$/',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => 'nullable|string|max:20',
        ], [
            'nickname.regex' => '닉네임은 한글, 영문, 숫자, 밑줄(_)만 사용할 수 있습니다.',
            'nickname.unique' => '이미 사용 중인 닉네임입니다.',
        ]);

        $user = User::create([
            'name'     => $data['nickname'],
            'nickname' => $data['nickname'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'phone'    => $data['phone'] ?? null,
            'role'     => 'user',
            'status'   => 'active',
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        $this->mergeSessionData($request, $user);

        return redirect()->route('my.index')->with('success', '회원가입이 완료되었습니다.');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('my.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => '이메일 또는 비밀번호가 올바르지 않습니다.'])->withInput();
        }

        if ($user->status === 'suspended') {
            return back()->withErrors(['email' => '정지된 계정입니다. 관리자에게 문의하세요.'])->withInput();
        }

        if ($user->status === 'withdrawn') {
            return back()->withErrors(['email' => '탈퇴한 계정입니다.'])->withInput();
        }

        $oldSessionId = $request->session()->getId();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        $this->mergeSessionData($request, $user, $oldSessionId);

        return redirect()->intended(route('my.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * 비회원 세션 데이터를 회원 계정으로 병합
     */
    private function mergeSessionData(Request $request, User $user, ?string $oldSessionId = null): void
    {
        $sessionId = $oldSessionId ?: $request->session()->getId();

        // 찜 데이터 병합
        Favorite::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        // 최근 본 데이터 병합
        RecentView::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        // 관심설정 병합
        $sessionPref = UserPreference::where('session_id', $sessionId)->whereNull('user_id')->first();
        if ($sessionPref) {
            $userPref = UserPreference::where('user_id', $user->id)->first();
            if (!$userPref) {
                $sessionPref->update(['user_id' => $user->id]);
            }
        }

        // 알림설정 병합
        $sessionNotifSetting = NotificationSetting::where('session_id', $sessionId)->whereNull('user_id')->first();
        if ($sessionNotifSetting) {
            $userSetting = NotificationSetting::where('user_id', $user->id)->first();
            if (!$userSetting) {
                $sessionNotifSetting->update(['user_id' => $user->id]);
            }
        }

        // 알림 병합
        NiteNotification::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        // 저장 필터 병합
        if (SavedFilter::available()) {
            SavedFilter::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
        }
    }
}
