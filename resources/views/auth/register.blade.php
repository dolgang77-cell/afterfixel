@extends('layouts.app')
@section('title', __('auth.register_title'))

@section('content')
<div class="px-4 py-8 max-w-md mx-auto">
    <div class="text-center mb-8">
        <div class="w-14 h-14 mx-auto mb-4 rounded-3xl gradient-accent flex items-center justify-center shadow-glow">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
        </div>
        <h1 class="text-[22px] font-extrabold tracking-tight">{{ __('auth.register_title') }}</h1>
        <p class="text-[13px] text-gray-500 mt-1">{{ __('auth.register_desc') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="card p-4 space-y-4">
            <div x-data="{ nickname: '{{ old('nickname') }}', status: '', checking: false }">
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">닉네임 <span class="text-pink-400">*</span> <span class="text-[10px] text-gray-600">({{ __('auth.nickname_desc') }})</span></label>
                <input type="text" name="nickname" x-model="nickname" required minlength="2" maxlength="20"
                       x-on:input.debounce.500ms="if(nickname.length >= 2) { checking=true; fetch('/auth/check-nickname?nickname='+nickname).then(r=>r.json()).then(d=>{ status=d.available?'ok':'taken'; checking=false; }) }"
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="{{ __('auth.nickname_placeholder') }}">
                <p x-show="status==='ok'" class="text-[11px] text-green-400 mt-1">{{ __('auth.nickname_available') }}</p>
                <p x-show="status==='taken'" class="text-[11px] text-red-400 mt-1">{{ __('auth.nickname_taken') }}</p>
                <p x-show="checking" class="text-[11px] text-gray-500 mt-1">{{ __('auth.nickname_checking') }}</p>
                @error('nickname')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">{{ __('auth.email') }} <span class="text-pink-400">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="example@email.com">
                @error('email')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">{{ __('auth.phone') }}</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="010-0000-0000">
                @error('phone')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">{{ __('auth.password') }} <span class="text-pink-400">*</span></label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="{{ __('auth.pw_min') }}">
                @error('password')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">{{ __('auth.password_confirm') }} <span class="text-pink-400">*</span></label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="{{ __('auth.pw_confirm_ph') }}">
            </div>
        </div>

        <p class="text-[11px] text-gray-600 text-center">
            {!! __('auth.terms_text', ['terms' => '<a href="'.route('terms').'" target="_blank" class="text-accent underline">'.__('auth.terms_link').'</a>', 'privacy' => '<a href="'.route('privacy').'" target="_blank" class="text-accent underline">'.__('auth.privacy_link').'</a>']) !!}
        </p>

        <button type="submit" class="btn-primary w-full py-4 rounded-2xl font-bold text-[14px] text-white">
            {{ __('auth.register_cta') }}
        </button>

        <p class="text-center text-[13px] text-gray-500">
            {{ __('auth.has_account') }} <a href="{{ route('login') }}" class="text-accent font-semibold">{{ __('common.login') }}</a>
        </p>
    </form>
</div>
@endsection
