@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 animate-pulse">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Factures</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_invoices']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 animate-pulse">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En Attente</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['pending_invoices']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 animate-pulse">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Confirmées</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['confirmed_invoices']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 animate-pulse">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Montant Total</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Factures récentes -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Factures Récentes</h3>
            </div>
            <div class="p-6">
                @if($recentInvoices->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentInvoices as $invoice)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $invoice->status->getColor() }}-100 text-{{ $invoice->status->getColor() }}-800 tooltip" data-tooltip="{{ $invoice->status->getLabel() }}">
                                            {{ $invoice->status->getLabel() }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $invoice->uid ?? 'En cours...' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $invoice->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA
                                    </p>
                                    @if($invoice->uid)
                                        <a href="{{ route('sgmef.invoices.show', $invoice->uid) }}" 
                                           class="text-xs text-blue-600 hover:text-blue-800">
                                            Voir détails
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Aucune facture trouvée</p>
                @endif
            </div>
        </div>

        <!-- Répartition par statut -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Répartition par Statut</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($statusStats as $status => $count)
                        @php
                            $statusEnum = \Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum::from($status);
                            $percentage = $stats['total_invoices'] > 0 ? ($count / $stats['total_invoices']) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ $statusEnum->getLabel() }}</span>
                                <span class="text-sm text-gray-500">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-{{ $statusEnum->getColor() }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions Rapides</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('sgmef.invoices.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Nouvelle Facture
            </a>
            
            <a href="{{ route('sgmef.invoices.index', ['status' => 'pending']) }}" 
               class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <i class="fas fa-clock mr-2"></i>
                Factures en Attente
            </a>
            
            <a href="{{ route('sgmef.config.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-cog mr-2"></i>
                Configuration
            </a>
        </div>
    </div>
</div>
@endsection
