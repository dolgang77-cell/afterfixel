@props(['items'])

<div class="grid grid-cols-2 gap-2.5">
    @foreach($items as $item)
    <div class="bg-dark-700/40 rounded-2xl p-3.5 border border-white/[0.03]">
        <p class="text-[11px] text-gray-500 mb-1.5 font-medium">{{ $item['icon'] ?? '' }} {{ $item['label'] }}</p>
        <p class="text-[13px] font-semibold text-gray-200">{{ $item['value'] }}</p>
    </div>
    @endforeach
</div>
