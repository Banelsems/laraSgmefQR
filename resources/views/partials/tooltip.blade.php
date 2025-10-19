@props(['text'])
<span x-data="{show:false}" @mouseenter="show=true" @mouseleave="show=false" class="relative cursor-pointer">
    <span>{{ $slot }}</span>
    <span x-show="show" x-transition class="absolute z-50 left-1/2 -translate-x-1/2 mt-2 px-2 py-1 bg-gray-800 text-white text-xs rounded shadow-lg whitespace-nowrap" style="display:none;">
        {{ $text }}
    </span>
</span>
