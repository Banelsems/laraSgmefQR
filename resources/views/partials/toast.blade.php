@php
    $flashType = session('error') ? 'error' : (session('success') ? 'success' : null);
    $flashMessage = session('success') ?? session('error');
@endphp
<div x-data="{ toasts: [], push(type, message) { this.toasts.push({ type, message, id: Date.now() }); setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== this.toasts[0]?.id); }, 4000); } }"
     @notify.window="push($event.detail.type || 'info', $event.detail.message)"
     class="fixed top-5 right-5 z-50 space-y-2"
     x-init="@if($flashType && $flashMessage) push('{{ $flashType }}', '{{ addslashes($flashMessage) }}'); @endif">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast"
             x-transition.opacity.duration.300ms
             class="flex items-center px-4 py-3 rounded-lg shadow-lg text-white"
             :class="{
                'bg-green-600': toast.type === 'success',
                'bg-red-600': toast.type === 'error',
                'bg-blue-600': toast.type === 'info',
                'bg-yellow-500': toast.type === 'warning'
             }">
            <i class="fas mr-2" :class="{
                'fa-check-circle': toast.type === 'success',
                'fa-exclamation-circle': toast.type === 'error',
                'fa-info-circle': toast.type === 'info',
                'fa-exclamation-triangle': toast.type === 'warning'
            }"></i>
            <span x-text="toast.message" class="text-sm font-medium"></span>
            <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="ml-4 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </template>
</div>
