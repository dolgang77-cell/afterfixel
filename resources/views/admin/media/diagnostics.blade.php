@extends('admin.layouts.app')
@section('title', '미디어 점검')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">미디어 점검</h1>
        <p class="mt-1 text-sm text-gray-500">원본, 썸네일, responsive variant, MD 대표 이미지 동기화 상태를 확인합니다.</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('admin.media.bulk-regenerate-variants') }}">
            @csrf
            <input type="hidden" name="scope" value="{{ $scope }}">
            <input type="hidden" name="owner_type" value="{{ request('owner_type') }}">
            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
            <input type="hidden" name="uploaded_by_role" value="{{ request('uploaded_by_role') }}">
            <input type="hidden" name="issue_type" value="{{ $issueType ?? '' }}">
            <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">현재 필터 일괄 재생성</button>
        </form>
        <a href="{{ route('admin.media.index') }}" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300">미디어 관리</a>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500">전체 미디어</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500">이슈 건수</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ $summary['issueCount'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500">원본 누락</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['missingOriginalCount'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500">썸네일 누락</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['missingThumbnailCount'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500">variant 누락</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['missingVariantCount'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500">MD 미동기화</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['mdProfileMismatchCount'] }}</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">표시 범위</label>
        <select name="scope" class="px-3 py-2 border rounded-lg text-sm">
            <option value="issues" {{ $scope === 'issues' ? 'selected' : '' }}>이슈만</option>
            <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>전체</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">대상</label>
        <select name="owner_type" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['md_profile' => 'MD', 'club' => '클럽', 'party' => '파티', 'review' => '후기', 'community' => '커뮤니티', 'push' => '푸쉬'] as $value => $label)
                <option value="{{ $value }}" {{ request('owner_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="approval_status" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['pending' => '승인대기', 'approved' => '승인됨', 'rejected' => '반려', 'hidden' => '숨김'] as $value => $label)
                <option value="{{ $value }}" {{ request('approval_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">업로드 주체</label>
        <select name="uploaded_by_role" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['admin' => '관리자', 'md' => 'MD', 'user' => '일반'] as $value => $label)
                <option value="{{ $value }}" {{ request('uploaded_by_role') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">이슈 종류</label>
        <select name="issue_type" class="px-3 py-2 border rounded-lg text-sm">
            <option value="" {{ ($issueType ?? '') === '' ? 'selected' : '' }}>전체</option>
            <option value="original" {{ ($issueType ?? '') === 'original' ? 'selected' : '' }}>원본 누락</option>
            <option value="thumbnail" {{ ($issueType ?? '') === 'thumbnail' ? 'selected' : '' }}>썸네일 누락</option>
            <option value="variant" {{ ($issueType ?? '') === 'variant' ? 'selected' : '' }}>variant 누락</option>
            <option value="md_profile_sync" {{ ($issueType ?? '') === 'md_profile_sync' ? 'selected' : '' }}>MD 미동기화</option>
            <option value="healthy" {{ ($issueType ?? '') === 'healthy' ? 'selected' : '' }}>정상만</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg">적용</button>
    <a href="{{ route('admin.media.diagnostics') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg">초기화</a>
</form>

<div class="space-y-4">
    @forelse($rows as $row)
        @php $media = $row['media']; @endphp
        <div class="bg-white rounded-xl shadow-sm border {{ $row['issues'] ? 'border-red-200' : 'border-gray-100' }} overflow-hidden">
            <div class="p-4 flex gap-4">
                <div class="w-28 shrink-0">
                    <img src="{{ $media->thumbnail_url ?? $media->file_url }}" class="w-28 h-28 rounded-xl object-cover bg-gray-100" alt="">
                </div>
                <div class="flex-1 min-w-0 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">#{{ $media->id }} · {{ $media->owner_type }} #{{ $media->owner_id }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $media->file_path }}</p>
                            <p class="text-xs text-gray-400 mt-1">업로더 {{ $media->uploader?->name ?? '-' }} · {{ $media->uploaded_by_role }} · {{ $media->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                        <form action="{{ route('admin.media.regenerate-variants', $media) }}" method="POST">
                            @csrf
                            <button class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">파생 이미지 재생성</button>
                        </form>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        @forelse($row['issues'] as $issue)
                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-700">{{ $issue }}</span>
                        @empty
                            <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">정상</span>
                        @endforelse
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs text-gray-600">
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-gray-400">원본</p>
                            <p class="mt-1 font-medium">{{ $row['missing_original'] ? '누락' : '정상' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-gray-400">썸네일</p>
                            <p class="mt-1 font-medium">{{ $row['missing_thumbnail'] ? '누락' : '정상' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-gray-400">variant 수</p>
                            <p class="mt-1 font-medium">{{ count($media->variant_paths ?? []) }} / {{ count($media->expectedVariantWidths()) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-gray-400">MD 동기화</p>
                            <p class="mt-1 font-medium">{{ $row['md_profile_mismatch'] ? '불일치' : '정상' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">표시할 미디어가 없습니다.</div>
    @endforelse
</div>

<div class="mt-6">{{ $rows->links() }}</div>
@endsection
