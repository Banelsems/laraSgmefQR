@props(['status'])
@php
    $map = [
        'pending' => ['bg-yellow-100 text-yellow-800', '⏳', 'En attente', 'Facture en attente de confirmation.'],
        'confirmed' => ['bg-green-100 text-green-800', '✅', 'Confirmée', 'Facture validée et confirmée.'],
        'cancelled' => ['bg-red-100 text-red-800', '❌', 'Annulée', 'Facture annulée.'],
        'error' => ['bg-red-100 text-red-800', '⚠️', 'Erreur', 'Erreur lors du traitement.'],
    ];
    $config = $map[$status->value ?? 'pending'] ?? $map['pending'];
@endphp
<span x-data="{}" x-tooltip="'{{ $config[3] }}'" class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $config[0] }}">
    <span class="mr-1">{{ $config[1] }}</span>{{ $config[2] }}
</span>
