@extends('admin.layouts.app')
@section('title', '정책 설정')
@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">운영 정책 설정</h1>
<form method="POST" action="{{ route('admin.moderation.policies.update') }}" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-5">
    @csrf @method('PATCH')
    @foreach($policies as $p)
    <div>
        <label class="block text-sm text-gray-600 mb-1">{{ $p->description }} <span class="text-xs text-gray-400">({{ $p->key }})</span></label>
        <input type="text" name="policies[{{ $p->key }}]" value="{{ $p->value }}" class="w-full px-3 py-2 border rounded-lg text-sm">
    </div>
    @endforeach
    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">저장</button>
</form>
@endsection
