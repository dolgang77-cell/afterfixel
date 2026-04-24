@props(['tags'])

@if($tags && count($tags) > 0)
<div class="flex flex-wrap gap-1.5">
    @foreach($tags as $tag)
        <span class="text-[11px] font-medium bg-purple-500/[0.06] text-purple-300/80 px-2.5 py-1 rounded-lg border border-purple-500/[0.06]">#{{ $tag }}</span>
    @endforeach
</div>
@endif
