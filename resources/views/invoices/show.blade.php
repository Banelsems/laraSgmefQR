@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Facture ' . ($invoice->uid ?? 'N/A'))
@section('page-title', 'Détails de la Facture')

@section('breadcrumb')
    <a href="{{ route('sgmef.dashboard') }}" class="text-blue-600 hover:text-blue-800">Tableau de bord</a>
    <span class="mx-2">/</span>
    <a href="{{ route('sgmef.invoices.index') }}" class="text-blue-600 hover:text-blue-800">Factures</a>
    <span class="mx-2">/</span>
    <span class="text-gray-500">{{ $invoice->uid ?? 'N/A' }}</span>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-3">
        @if($invoice->status->value === 'pending')
            <!-- Confirmer -->
            <form method="POST" 
                  action="{{ route('sgmef.invoices.confirm', $invoice->uid) }}" 
                  class="inline"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir confirmer cette facture ?')">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-check mr-2"></i>
                    Confirmer
                </button>
            </form>

            <!-- Annuler -->
            <form method="POST" 
                  action="{{ route('sgmef.invoices.cancel', $invoice->uid) }}" 
                  class="inline"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette facture ?')">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </button>
            </form>
        @endif

        @if($invoice->status->value === 'confirmed')
            <!-- Télécharger PDF -->
            <a href="{{ route('sgmef.invoices.pdf', $invoice->uid) }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                <i class="fas fa-download mr-2"></i>
                Télécharger PDF
            </a>

            <!-- Imprimer -->
            <a href="{{ route('sgmef.invoices.print', $invoice->uid) }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-print mr-2"></i>
                Imprimer
            </a>
        @endif

        <!-- Synchroniser -->
        <a href="{{ route('sgmef.invoices.sync', $invoice->uid) }}" 
           class="inline-flex items-center px-4 py-2 border border-orange-300 text-orange-700 text-sm font-medium rounded-md hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
            <i class="fas fa-sync mr-2"></i>
            Synchroniser
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Statut et informations principales -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Facture {{ $invoice->uid ?? 'N/A' }}
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Créée le {{ $invoice->created_at?->format('d/m/Y à H:i') ?? 'N/A' }}
                </p>
            </div>
            
            <div class="text-right">
                @php
                    $statusConfig = [
                        'pending' => ['bg-yellow-100', 'text-yellow-800', 'En attente', 'fas fa-clock'],
                        'confirmed' => ['bg-green-100', 'text-green-800', 'Confirmée', 'fas fa-check-circle'],
                        'cancelled' => ['bg-red-100', 'text-red-800', 'Annulée', 'fas fa-times-circle'],
                        'error' => ['bg-red-100', 'text-red-800', 'Erreur', 'fas fa-exclamation-circle'],
                    ];
                    $status = $invoice->status->value ?? 'pending';
                    $config = $statusConfig[$status] ?? $statusConfig['pending'];
                @endphp
                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $config[0] }} {{ $config[1] }}">
                    <i class="{{ $config[3] }} mr-2"></i>
                    {{ $config[2] }}
                </span>
                
                @if($invoice->mecf_code)
                    <p class="text-xs text-gray-500 mt-2">
                        Code MECeF: {{ $invoice->mecf_code }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Informations principales en grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Montants -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Montants</h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Sous-total:</span>
                        <span class="font-medium">{{ number_format(($invoice->total_amount ?? 0) - ($invoice->total_tax_amount ?? 0), 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($invoice->total_tax_amount)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Taxes:</span>
                            <span class="font-medium">{{ number_format($invoice->total_tax_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($invoice->total_aib_amount)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">AIB:</span>
                            <span class="font-medium">{{ number_format($invoice->total_aib_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="border-t border-gray-200 pt-2">
                        <div class="flex justify-between text-base font-semibold">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-gray-900">{{ number_format($invoice->total_amount ?? 0, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Client</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-600">Nom:</span>
                        <p class="text-sm font-medium text-gray-900">{{ $invoice->client_name ?? 'N/A' }}</p>
                    </div>
                    @if($invoice->customer_ifu)
                        <div>
                            <span class="text-sm text-gray-600">IFU:</span>
                            <p class="text-sm font-medium text-gray-900">{{ $invoice->customer_ifu }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Opérateur -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Opérateur</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-600">Nom:</span>
                        <p class="text-sm font-medium text-gray-900">{{ $invoice->operator_name ?? 'N/A' }}</p>
                    </div>
                    @if($invoice->operator_id)
                        <div>
                            <span class="text-sm text-gray-600">ID:</span>
                            <p class="text-sm font-medium text-gray-900">{{ $invoice->operator_id }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Articles -->
    @if($invoice->items && count($invoice->items) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Articles ({{ count($invoice->items) }})
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Article
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prix unitaire
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantité
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Taxe
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $item['name'] ?? 'N/A' }}
                                    </div>
                                    @if(isset($item['description']) && $item['description'])
                                        <div class="text-sm text-gray-500">
                                            {{ $item['description'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    {{ number_format($item['price'] ?? 0, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    {{ $item['quantity'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                    {{ $item['taxGroup'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                    {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Paiements -->
    @if($invoice->payment && count($invoice->payment) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                    Modes de paiement
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($invoice->payment as $payment)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $payment['type'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Mode de paiement
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ number_format($payment['amount'] ?? 0, 0, ',', ' ') }} FCFA
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- QR Code et éléments de sécurité -->
    @if($invoice->status->value === 'confirmed' && ($invoice->qr_code_data || $invoice->mecf_code))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                Éléments de sécurité
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($invoice->qr_code_data)
                    <div class="text-center">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">QR Code</h4>
                        <div class="inline-block p-4 bg-white border border-gray-200 rounded-lg">
                            <!-- Ici, vous pourriez générer le QR code avec une librairie comme SimpleSoftwareIO/simple-qrcode -->
                            <div class="w-32 h-32 bg-gray-100 flex items-center justify-center text-gray-400">
                                <i class="fas fa-qrcode text-4xl"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Données: {{ Str::limit($invoice->qr_code_data, 50) }}
                        </p>
                    </div>
                @endif

                @if($invoice->mecf_code)
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Code MECeF</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-lg font-mono text-center text-gray-900">
                                {{ $invoice->mecf_code }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">
                            Code de vérification officiel
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Historique et logs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-history text-blue-600 mr-2"></i>
            Historique
        </h3>
        
        <div class="space-y-3">
            <div class="flex items-center text-sm">
                <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                <div class="flex-1">
                    <span class="text-gray-900">Facture créée</span>
                    <span class="text-gray-500 ml-2">{{ $invoice->created_at?->format('d/m/Y à H:i') ?? 'N/A' }}</span>
                </div>
            </div>
            
            @if($invoice->confirmed_at)
                <div class="flex items-center text-sm">
                    <div class="flex-shrink-0 w-2 h-2 bg-green-600 rounded-full mr-3"></div>
                    <div class="flex-1">
                        <span class="text-gray-900">Facture confirmée</span>
                        <span class="text-gray-500 ml-2">{{ $invoice->confirmed_at?->format('d/m/Y à H:i') ?? 'N/A' }}</span>
                    </div>
                </div>
            @endif
            
            @if($invoice->cancelled_at)
                <div class="flex items-center text-sm">
                    <div class="flex-shrink-0 w-2 h-2 bg-red-600 rounded-full mr-3"></div>
                    <div class="flex-1">
                        <span class="text-gray-900">Facture annulée</span>
                        <span class="text-gray-500 ml-2">{{ $invoice->cancelled_at?->format('d/m/Y à H:i') ?? 'N/A' }}</span>
                    </div>
                </div>
            @endif
            
            <div class="flex items-center text-sm">
                <div class="flex-shrink-0 w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                <div class="flex-1">
                    <span class="text-gray-900">Dernière mise à jour</span>
                    <span class="text-gray-500 ml-2">{{ $invoice->updated_at?->format('d/m/Y à H:i') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de retour -->
    <div class="flex justify-between pt-6 border-t border-gray-200">
        <a href="{{ route('sgmef.invoices.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-arrow-left mr-2"></i>
            Retour à la liste
        </a>
        
        <a href="{{ route('sgmef.invoices.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-plus mr-2"></i>
            Nouvelle facture
        </a>
    </div>
</div>
@endsection
