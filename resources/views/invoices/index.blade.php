@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Factures')
@section('page-title', 'Gestion des Factures')

@section('breadcrumb')
    <a href="{{ route('sgmef.dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Tableau de bord</a>
    <span class="mx-2">/</span>
    <span class="text-gray-500 dark:text-gray-400">Factures</span>
@endsection

@section('header-actions')
    <a href="{{ route('sgmef.invoices.create') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <i class="fas fa-plus mr-2"></i>
        <span class="hidden sm:inline">Nouvelle Facture</span>
        <span class="sm:hidden">Nouvelle</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <form method="GET" action="{{ route('sgmef.invoices.index') }}" class="space-y-4">
            <!-- Preserve sort/direction across filter submissions -->
            <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
            <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- Recherche -->
                <div class="sm:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rechercher
                    </label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="UID, IFU client, Code MECeF, référence..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Statut
                    </label>
                    <select id="status"
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Erreur</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirée</option>
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                    <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous</option>
                        @foreach(['FV', 'FA', 'EV', 'EA'] as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label for="ifu" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">IFU émetteur</label>
                    <input type="text" id="ifu" name="ifu" value="{{ request('ifu') }}" maxlength="13" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Depuis</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jusqu'au</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="amount_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min</label>
                        <input type="number" id="amount_min" name="amount_min" value="{{ request('amount_min') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="amount_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max</label>
                        <input type="number" id="amount_max" name="amount_max" value="{{ request('amount_max') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-search mr-2"></i>
                    Filtrer
                </button>

                @if(request()->hasAny(['search', 'status', 'type', 'ifu', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                    <a href="{{ route('sgmef.invoices.index') }}"
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-times mr-2"></i>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste des factures -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($invoices->count() > 0)
            <div class="overflow-x-auto">
                @php
                    $currentSort = request('sort', 'created_at');
                    $currentDir = request('direction', 'desc');
                    $queryParams = request()->only(['search', 'status', 'type', 'ifu', 'date_from', 'date_to', 'amount_min', 'amount_max']);
                @endphp
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            @php
                                $sortableHeaders = [
                                    'uid' => ['Facture', 'created_at'],
                                    'client' => ['Client', null],
                                    'operator' => ['Opérateur', null],
                                    'total_amount' => ['Montant', 'total_amount'],
                                    'status' => ['Statut', 'status'],
                                    'created_at' => ['Date', 'created_at'],
                                ];
                            @endphp
                            @foreach($sortableHeaders as $key => [$label, $sortCol])
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                                    @if($sortCol !== null)
                                        @php
                                            $isActive = $currentSort === $sortCol;
                                            $nextDir = $isActive && $currentDir === 'desc' ? 'asc' : 'desc';
                                            $sortParams = array_merge($queryParams, ['sort' => $sortCol, 'direction' => $nextDir]);
                                        @endphp
                                        <a href="{{ route('sgmef.invoices.index', $sortParams) }}"
                                           class="inline-flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>{{ $label }}</span>
                                            @if($isActive)
                                                <i class="fas fa-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300 dark:text-gray-600"></i>
                                            @endif
                                        </a>
                                    @else
                                        {{ $label }}
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $invoice->uid ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $invoice->type }} @if($invoice->reference) · Réf: {{ $invoice->reference }} @endif
                                        </div>
                                        @if($invoice->mecf_code)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                MECeF: {{ $invoice->mecf_code }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ data_get($invoice->raw_request, 'client.name', 'N/A') }}
                                        </div>
                                        @if($invoice->customer_ifu)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                IFU: {{ $invoice->customer_ifu }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ data_get($invoice->raw_request, 'operator.name', 'N/A') }}
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ number_format($invoice->total_amount ?? 0, 0, ',', ' ') }} FCFA
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">IFU: {{ $invoice->ifu }}</div>
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    @include('lara-sgmef-qr::partials.status-badge', ['status' => $invoice->status])
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $invoice->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if($invoice->uid)
                                            <a href="{{ route('sgmef.invoices.show', $invoice->uid) }}"
                                               class="text-blue-600 dark:text-blue-400 hover:text-blue-900"
                                               title="Voir la facture">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif

                                        @if($invoice->status->value === 'pending' && $invoice->uid)
                                            <form method="POST"
                                                  action="{{ route('sgmef.invoices.confirm', $invoice->uid) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir confirmer cette facture ?')">
                                                @csrf
                                                <button type="submit"
                                                        class="text-green-600 dark:text-green-400 hover:text-green-900"
                                                        title="Confirmer la facture">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('sgmef.invoices.cancel', $invoice->uid) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette facture ?')">
                                                @csrf
                                                <button type="submit"
                                                        class="text-red-600 dark:text-red-400 hover:text-red-900"
                                                        title="Annuler la facture">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($invoice->status->value === 'confirmed' && $invoice->uid)
                                            <a href="{{ route('sgmef.invoices.pdf', $invoice->uid) }}"
                                               class="text-purple-600 dark:text-purple-400 hover:text-purple-900"
                                               title="Télécharger PDF">
                                                <i class="fas fa-download"></i>
                                            </a>

                                            <a href="{{ route('sgmef.invoices.print', $invoice->uid) }}"
                                               target="_blank"
                                               class="text-gray-600 dark:text-gray-400 hover:text-gray-900"
                                               title="Imprimer">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif

                                        @if($invoice->uid)
                                            <a href="{{ route('sgmef.invoices.sync', $invoice->uid) }}"
                                               class="text-orange-600 dark:text-orange-400 hover:text-orange-900"
                                               title="Synchroniser avec l'API">
                                                <i class="fas fa-sync"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- État vide -->
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600">
                    <i class="fas fa-file-invoice text-4xl"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucune facture</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if(request()->hasAny(['search', 'status', 'type', 'ifu', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                        Aucune facture ne correspond à vos critères de recherche.
                    @else
                        Commencez par créer votre première facture.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status', 'type', 'ifu', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                        <a href="{{ route('sgmef.invoices.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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
