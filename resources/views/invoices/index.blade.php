@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Factures')
@section('page-title', 'Gestion des Factures')

@section('breadcrumb')
    <a href="{{ route('sgmef.dashboard') }}" class="text-blue-600 hover:text-blue-800">Tableau de bord</a>
    <span class="mx-2">/</span>
    <span class="text-gray-500">Factures</span>
@endsection

@section('header-actions')
    <a href="{{ route('sgmef.invoices.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <i class="fas fa-plus mr-2"></i>
        Nouvelle Facture
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('sgmef.invoices.index') }}" class="flex flex-wrap items-end gap-4">
            <!-- Recherche -->
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher
                </label>
                <input type="text" 
                       id="search" 
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="UID, IFU client, Code MECeF..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Statut -->
            <div class="min-w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Statut
                </label>
                <select id="status" 
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Erreur</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-search mr-2"></i>
                    Filtrer
                </button>
                
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('sgmef.invoices.index') }}" 
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-times mr-2"></i>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste des factures -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Facture
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Opérateur
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Montant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $invoice->uid ?? 'N/A' }}
                                        </div>
                                        @if($invoice->mecf_code)
                                            <div class="text-xs text-gray-500">
                                                MECeF: {{ $invoice->mecf_code }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-900">
                                            {{ $invoice->client_name ?? 'N/A' }}
                                        </div>
                                        @if($invoice->customer_ifu)
                                            <div class="text-xs text-gray-500">
                                                IFU: {{ $invoice->customer_ifu }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $invoice->operator_name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ number_format($invoice->total_amount ?? 0, 0, ',', ' ') }} FCFA
                                    </div>
                                    @if($invoice->total_tax_amount)
                                        <div class="text-xs text-gray-500">
                                            Taxes: {{ number_format($invoice->total_tax_amount, 0, ',', ' ') }} FCFA
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg-yellow-100', 'text-yellow-800', 'En attente'],
                                            'confirmed' => ['bg-green-100', 'text-green-800', 'Confirmée'],
                                            'cancelled' => ['bg-red-100', 'text-red-800', 'Annulée'],
                                            'error' => ['bg-red-100', 'text-red-800', 'Erreur'],
                                        ];
                                        $status = $invoice->status->value ?? 'pending';
                                        $config = $statusConfig[$status] ?? $statusConfig['pending'];
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $config[0] }} {{ $config[1] }}">
                                        {{ $config[2] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $invoice->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Voir -->
                                        <a href="{{ route('sgmef.invoices.show', $invoice->uid) }}" 
                                           class="text-blue-600 hover:text-blue-900"
                                           title="Voir la facture">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($invoice->status->value === 'pending')
                                            <!-- Confirmer -->
                                            <form method="POST" 
                                                  action="{{ route('sgmef.invoices.confirm', $invoice->uid) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir confirmer cette facture ?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-900"
                                                        title="Confirmer la facture">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>

                                            <!-- Annuler -->
                                            <form method="POST" 
                                                  action="{{ route('sgmef.invoices.cancel', $invoice->uid) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette facture ?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        title="Annuler la facture">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($invoice->status->value === 'confirmed')
                                            <!-- Télécharger PDF -->
                                            <a href="{{ route('sgmef.invoices.pdf', $invoice->uid) }}" 
                                               class="text-purple-600 hover:text-purple-900"
                                               title="Télécharger PDF">
                                                <i class="fas fa-download"></i>
                                            </a>

                                            <!-- Imprimer -->
                                            <a href="{{ route('sgmef.invoices.print', $invoice->uid) }}" 
                                               target="_blank"
                                               class="text-gray-600 hover:text-gray-900"
                                               title="Imprimer">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif

                                        <!-- Synchroniser -->
                                        <a href="{{ route('sgmef.invoices.sync', $invoice->uid) }}" 
                                           class="text-orange-600 hover:text-orange-900"
                                           title="Synchroniser avec l'API">
                                            <i class="fas fa-sync"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $invoices->links() }}
                </div>
            @endif
        @else
            <!-- État vide -->
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <i class="fas fa-file-invoice text-4xl"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune facture</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status']))
                        Aucune facture ne correspond à vos critères de recherche.
                    @else
                        Commencez par créer votre première facture.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('sgmef.invoices.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-times mr-2"></i>
                            Réinitialiser les filtres
                        </a>
                    @else
                        <a href="{{ route('sgmef.invoices.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-plus mr-2"></i>
                            Créer une facture
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
