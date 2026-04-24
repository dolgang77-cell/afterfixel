@extends('layouts.app')
@section('title', __('notif_setting.title'))

@section('content')
<div class="px-4 py-6 space-y-5" x-data="notifSettings()">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ url()->previous() }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-[20px] font-extrabold text-white">{{ __('notif_setting.title') }}</h1>
    </div>

    <form method="POST" action="{{ route('notification-settings.update') }}" class="space-y-5">
        @csrf

        {{-- 마스터 토글 --}}
        <div class="card p-4 flex items-center justify-between">
            <div>
                <p class="text-[14px] font-bold text-white">{{ __('notif_setting.enable') }}</p>
                <p class="text-[12px] text-gray-400 mt-0.5">{{ __('notif_setting.enable_desc') }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="enabled" value="0">
                <input type="checkbox" name="enabled" value="1" x-model="enabled"
                       class="sr-only peer" {{ $setting->enabled ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-dark-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
            </label>
        </div>

        <div :class="!enabled && 'opacity-40 pointer-events-none'" class="space-y-5 transition-opacity">

            {{-- 파티 시작 전 알림 --}}
            <div class="card p-4">
                <h3 class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg gradient-purple flex items-center justify-center text-[11px]">⏰</span>
                    {{ __('notif_setting.party_remind') }}
                </h3>
                <p class="text-[11px] text-gray-500 mb-3">{{ __('notif_setting.party_remind_desc') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\NotificationSetting::$remindOptions as $val => $label)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="remind_before[]" value="{{ $val }}"
                                   {{ in_array($val, $setting->remind_before ?? []) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <span class="block px-3 py-1.5 rounded-full text-[12px] font-medium border border-white/[0.08] bg-dark-700/60 text-gray-400 peer-checked:border-purple-500/50 peer-checked:bg-purple-500/10 peer-checked:text-purple-400 transition-all">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 관심 지역 --}}
            <div class="card p-4">
                <h3 class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg gradient-pink flex items-center justify-center text-[11px]">📍</span>
                    {{ __('notif_setting.interest_area') }}
                </h3>
                <p class="text-[11px] text-gray-500 mb-3">{{ __('notif_setting.interest_area_desc') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\Club::$areas as $area)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="preferred_areas[]" value="{{ $area }}"
                                   {{ in_array($area, $setting->preferred_areas ?? []) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <span class="block px-3 py-1.5 rounded-full text-[12px] font-medium border border-white/[0.08] bg-dark-700/60 text-gray-400 peer-checked:border-pink-500/50 peer-checked:bg-pink-500/10 peer-checked:text-pink-400 transition-all">
                                {{ $area }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 관심 장르 --}}
            <div class="card p-4">
                <h3 class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg gradient-accent flex items-center justify-center text-[11px]">🎵</span>
                    {{ __('notif_setting.interest_genre') }}
                </h3>
                <p class="text-[11px] text-gray-500 mb-3">{{ __('notif_setting.interest_genre_desc') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\Club::$genres as $genre)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="preferred_genres[]" value="{{ $genre }}"
                                   {{ in_array($genre, $setting->preferred_genres ?? []) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <span class="block px-3 py-1.5 rounded-full text-[12px] font-medium border border-white/[0.08] bg-dark-700/60 text-gray-400 peer-checked:border-accent/50 peer-checked:bg-accent/10 peer-checked:text-accent transition-all">
                                {{ $genre }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 추가 옵션 --}}
            <div class="card p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-semibold text-gray-200">{{ __('notif_setting.new_party_match') }}</p>
                        <p class="text-[11px] text-gray-500">{{ __('notif_setting.new_party_match_desc') }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="new_party_alert" value="0">
                        <input type="checkbox" name="new_party_alert" value="1" class="sr-only peer"
                               {{ $setting->new_party_alert ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <div class="border-t border-white/[0.04]"></div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-semibold text-gray-200">{{ __('notif_setting.tonight_recommend') }}</p>
                        <p class="text-[11px] text-gray-500">{{ __('notif_setting.tonight_recommend_desc') }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="tonight_recommendation" value="0">
                        <input type="checkbox" name="tonight_recommendation" value="1" class="sr-only peer"
                               {{ $setting->tonight_recommendation ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>
            </div>

            <div class="card p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-[13px] font-bold text-gray-300 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/15 flex items-center justify-center text-[11px] text-amber-300">🔖</span>
                            저장 필터
                        </h3>
                        <p class="mt-1 text-[11px] text-gray-500">리스트에서 저장한 조건을 여기서 확인하고 해제할 수 있습니다.</p>
                    </div>
                    @if($savedFilterFeatureAvailable)
                        <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-[10px] font-semibold text-amber-200">{{ $savedFilters->count() }}개</span>
                    @endif
                </div>

                @if(!$savedFilterFeatureAvailable)
                    <p class="mt-4 text-[12px] text-gray-500">저장 필터 기능은 마이그레이션 적용 후 활성화됩니다.</p>
                @elseif($savedFilters->isEmpty())
                    <p class="mt-4 text-[12px] text-gray-500">아직 저장한 필터가 없습니다. 클럽/파티 리스트에서 조건을 먼저 저장해 보세요.</p>
                @else
                    <div class="mt-4 space-y-2">
                        @foreach($savedFilters as $savedFilter)
                            <div class="flex items-center gap-3 rounded-2xl border border-white/[0.06] bg-dark-700/50 px-3 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[12px] font-semibold text-gray-100">{{ $savedFilter->name }}</p>
                                    <p class="mt-1 text-[10px] text-gray-500">
                                        {{ $savedFilter->target_type === 'club' ? '클럽 필터' : '파티 필터' }}
                                        · {{ $savedFilter->created_at->format('n.j H:i') }}
                                    </p>
                                </div>
                                <form action="{{ route('saved-filters.destroy', $savedFilter) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to" value="{{ route('notification-settings.edit') }}">
                                    <button type="submit" class="rounded-xl border border-white/[0.08] bg-dark-800 px-3 py-2 text-[11px] font-semibold text-gray-300">해제</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- 저장 --}}
        <button type="submit" class="btn-primary w-full py-3.5 rounded-2xl font-bold text-[14px] text-white">
            {{ __('notif_setting.save') }}
        </button>
    </form>

    {{-- 링크 --}}
    <div class="flex justify-center gap-6 pt-2">
        <a href="{{ route('favorites.index') }}" class="text-[12px] text-gray-400 hover:text-accent">{{ __('notif_setting.view_favorites') }}</a>
        <a href="{{ route('notifications.index') }}" class="text-[12px] text-gray-400 hover:text-accent">{{ __('notif_setting.view_notifications') }}</a>
    </div>
</div>

@push('scripts')
<script>
function notifSettings() {
    return { enabled: {{ $setting->enabled ? 'true' : 'false' }} };
}
</script>
@endpush
@endsection
