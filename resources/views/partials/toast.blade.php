@props(['type' => 'success', 'message'])
@php
    $types = [
        'success' => ['bg-green-600', 'fa-check-circle'],
        'error' => ['bg-red-600', 'fa-exclamation-circle'],
        'info' => ['bg-blue-600', 'fa-info-circle'],
        'warning' => ['bg-yellow-500', 'fa-exclamation-triangle'],
    ];
    $config = $types[$type] ?? $types['info'];
@endphp
<div x-data="{show:true}" x-show="show" x-transition.opacity class="fixed top-5 right-5 z-50 flex items-center px-4 py-3 rounded shadow-lg text-white {{ $config[0] }}" @toast.window="if($event.detail.type==='{{ $type }}'){show=true;setTimeout(()=>show=false,3500)}">
    <i class="fas {{ $config[1] }} mr-2"></i>
    <span>{{ $message }}</span>
    <button @click="show=false" class="ml-4 focus:outline-none"><i class="fas fa-times"></i></button>
</div>
