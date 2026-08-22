@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <!-- Alertes visuelles -->
    @if($stats['expired_pending'] > 0)
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-lg p-4 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3 text-lg"></i>
                <div>
                    <p class="font-medium text-red-800 dark:text-red-300">{{ number_format($stats['expired_pending']) }} facture(s) en attente expirée(s)</p>
                    <p class="text-sm text-red-600 dark:text-red-400">Ces factures n'ont pas été confirmées dans le délai de 2 minutes et ont expiré côté e-MECeF.</p>
                </div>
            </div>
            <a href="{{ route('sgmef.invoices.index', ['status' => 'pending']) }}"
               class="text-sm font-medium text-red-700 dark:text-red-300 hover:underline whitespace-nowrap">
                Voir →
            </a>
        </div>
    @endif

    @if($stats['api_error_rate'] > 10)
        <div class="bg-orange-50 dark:bg-orange-900/30 border-l-4 border-orange-500 rounded-r-lg p-4 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-bug text-orange-500 mr-3 text-lg"></i>
                <div>
                    <p class="font-medium text-orange-800 dark:text-orange-300">Taux d'erreur API élevé : {{ number_format($stats['api_error_rate'], 1) }}%</p>
                    <p class="text-sm text-orange-600 dark:text-orange-400">Sur les 30 derniers jours, plus de 10% des appels API ont échoué.</p>
                </div>
            </div>
            <a href="{{ route('sgmef.invoices.index', ['status' => 'error']) }}"
               class="text-sm font-medium text-orange-700 dark:text-orange-300 hover:underline whitespace-nowrap">
                Voir →
            </a>
        </div>
    @endif

    <!-- KPIs principales — cliquables -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <a href="{{ route('sgmef.invoices.index') }}"
           class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Factures</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_invoices']) }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('sgmef.invoices.index', ['status' => 'pending']) }}"
           class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En Attente</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['pending_invoices']) }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('sgmef.invoices.index', ['status' => 'confirmed']) }}"
           class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Confirmées</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['confirmed_invoices']) }}</p>
                </div>
            </div>
        </a>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Montant Total</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs secondaires -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Aujourd'hui</p>
            <p class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['today_invoices']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Cette semaine</p>
            <p class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['week_invoices']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Ce mois</p>
            <p class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['month_invoices']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Erreurs API 30j</p>
            <p class="text-lg sm:text-xl font-semibold {{ $stats['api_error_rate'] > 10 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">{{ number_format($stats['api_error_rate'], 2) }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Attentes expirées</p>
            <p class="text-lg sm:text-xl font-semibold {{ $stats['expired_pending'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">{{ number_format($stats['expired_pending']) }}</p>
        </div>
    </div>

    <!-- Graphique 30 jours + Répartition statut -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend chart 30 jours -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Évolution des 30 derniers jours</h3>
            </div>
            <div class="p-6">
                @php
                    $maxCount = $dailyStats->max('count') ?? 0;
                    $maxAmount = $dailyStats->max('amount') ?? 0;
                @endphp
                @if($dailyStats->count() > 0)
                    <div class="flex items-end space-x-1 h-48 overflow-x-auto">
                        @foreach($dailyStats as $day)
                            @php
                                $barHeight = $maxCount > 0 ? max(2, ($day->count / $maxCount) * 100) : 2;
                            @endphp
                            <div class="flex flex-col items-center flex-shrink-0 group relative" style="width: 20px;">
                                <div class="bg-blue-500 hover:bg-blue-600 rounded-t transition-all w-3"
                                     style="height: {{ $barHeight }}%"
                                     title="{{ $day->date }} — {{ $day->count }} facture(s) — {{ number_format($day->amount ?? 0, 0, ',', ' ') }} FCFA">
                                </div>
                                <span class="text-[8px] text-gray-400 dark:text-gray-500 mt-1 rotate-45 origin-left">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-12">Aucune donnée sur les 30 derniers jours</p>
                @endif
            </div>
        </div>

        <!-- Répartition par statut -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Répartition par Statut</h3>
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
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $statusEnum->getLabel() }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-{{ $statusEnum->getColor() }}-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Factures récentes + Top clients -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Factures récentes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Factures Récentes</h3>
            </div>
            <div class="p-6">
                @if($recentInvoices->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentInvoices as $invoice)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center min-w-0">
                                    <div class="flex-shrink-0">
                                        @include('lara-sgmef-qr::partials.status-badge', ['status' => $invoice->status])
                                    </div>
                                    <div class="ml-4 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $invoice->uid ?? 'En cours...' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $invoice->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right ml-4 flex-shrink-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA
                                    </p>
                                    @if($invoice->uid)
                                        <a href="{{ route('sgmef.invoices.show', $invoice->uid) }}"
                                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                            Voir détails
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Aucune facture trouvée</p>
                @endif
            </div>
        </div>

        <!-- Top 5 clients -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Top 5 Clients</h3>
            </div>
            <div class="p-6">
                @if($topClients->count() > 0)
                    <div class="space-y-4">
                        @foreach($topClients as $client)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center min-w-0">
                                    <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 mr-3">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $client->customer_ifu ?? 'N/A' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $client->invoice_count }} facture(s)</p>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 ml-4 flex-shrink-0">
                                    {{ number_format($client->total_amount, 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Aucun client confirmé</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Actions Rapides</h3>
        <div class="flex flex-wrap gap-3 sm:gap-4">
            <a href="{{ route('sgmef.invoices.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base">
                <i class="fas fa-plus mr-2"></i>
                Nouvelle Facture
            </a>

            <a href="{{ route('sgmef.invoices.index', ['status' => 'pending']) }}"
               class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm sm:text-base">
                <i class="fas fa-clock mr-2"></i>
                Factures en Attente
            </a>

            <a href="{{ route('sgmef.config.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm sm:text-base">
                <i class="fas fa-cog mr-2"></i>
                Configuration
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Polling des stats dashboard — dispatche des notifications toast
// quand des anomalies sont détectées (factures expirées, pic d'erreurs).
(function() {
    let lastExpiredCount = {{ (int) ($stats['expired_pending'] ?? 0) }};
    const pollingSeconds = {{ max(5, (int) config('lara_sgmef_qr.web_interface.polling_seconds', 30)) }};

    setInterval(async () => {
        try {
            const response = await fetch('{{ route('sgmef.dashboard.stats') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            // Alerte : nouvelles factures expirées depuis le dernier poll
            const expired = data.expired_pending || 0;
            if (expired > lastExpiredCount) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        type: 'error',
                        message: expired + ' facture(s) en attente expirée(s)'
                    }
                }));
            }
            lastExpiredCount = expired;
        } catch (error) {
            // Polling indisponible — pas d'alerte utilisateur pour éviter le bruit
        }
    }, pollingSeconds * 1000);
})();
</script>
@endpush
