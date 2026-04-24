@extends('layouts.app')
@section('title', __('auth.login_title'))

@section('content')
<div class="px-4 py-8 max-w-md mx-auto">
    <div class="text-center mb-8">
        <div class="w-14 h-14 mx-auto mb-4 rounded-3xl gradient-accent flex items-center justify-center shadow-glow">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
        </div>
        <h1 class="text-[22px] font-extrabold tracking-tight">{{ __('auth.login_title') }}</h1>
        <p class="text-[13px] text-gray-500 mt-1">{{ __('auth.login_desc') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="card p-4 space-y-4">
            <div>
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">{{ __('auth.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="example@email.com">
                @error('email')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[12px] font-semibold text-gray-400 mb-1.5 block">{{ __('auth.password') }}</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                       placeholder="{{ __('auth.pw_placeholder') }}">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" value="1"
                       class="rounded border-gray-600 bg-dark-700 text-accent focus:ring-accent">
                <span class="text-[12px] text-gray-400">{{ __('auth.remember') }}</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full py-4 rounded-2xl font-bold text-[14px] text-white">
            {{ __('auth.login_cta') }}
        </button>

        <p class="text-center text-[13px] text-gray-500">
            {{ __('auth.no_account') }} <a href="{{ route('register') }}" class="text-accent font-semibold">{{ __('common.register') }}</a>
        </p>
    </form>
</div>
@endsection
