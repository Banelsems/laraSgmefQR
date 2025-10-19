@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Créer une Facture')
@section('page-title', 'Nouvelle Facture')

@section('breadcrumb')
    <a href="{{ route('sgmef.dashboard') }}" class="text-blue-600 hover:text-blue-800">Tableau de bord</a>
    <span class="mx-2">/</span>
    <a href="{{ route('sgmef.invoices.index') }}" class="text-blue-600 hover:text-blue-800">Factures</a>
    <span class="mx-2">/</span>
    <span class="text-gray-500">Nouvelle</span>
@endsection

@section('content')
<div x-data="invoiceForm()" class="max-w-6xl mx-auto">
    <form @submit.prevent="submitForm" class="space-y-8">
        @csrf
        
        <!-- Configuration de base -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-cog text-blue-600 mr-2"></i>
                Configuration de base
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- IFU -->
                <div>
                    <label for="ifu" class="block text-sm font-medium text-gray-700 mb-2">
    IFU de l'entreprise <span class="text-red-500">*</span>
    @include('partials.tooltip', ['text' => 'Identifiant Fiscal Unique. 13 chiffres.'])
</label>
                    <input type="text" 
                           id="ifu" 
                           name="ifu"
                           x-model="form.ifu"
                           placeholder="1234567890123"
                           maxlength="13"
                           pattern="[0-9]{13}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ config('lara_sgmef_qr.default_ifu') }}"
                           required>
                    <p class="mt-1 text-xs text-gray-500">13 chiffres exactement</p>
                </div>

                <!-- Type de facture -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type de facture <span class="text-red-500">*</span>
                    </label>
                    <select id="type" 
                            name="type"
                            x-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Sélectionner un type</option>
                        @foreach($invoiceTypes ?? [] as $type)
                            <option value="{{ $type['code'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- AIB -->
                <div>
                    <label for="aib" class="block text-sm font-medium text-gray-700 mb-2">
                        AIB (Optionnel)
                    </label>
                    <select id="aib" 
                            name="aib"
                            x-model="form.aib"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Aucun</option>
                        <option value="A">A (1%)</option>
                        <option value="B">B (5%)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Informations de l'opérateur -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-cog text-blue-600 mr-2"></i>
                Opérateur
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom de l'opérateur -->
                <div>
                    <label for="operator_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'opérateur <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="operator_name" 
                           name="operator[name]"
                           x-model="form.operator.name"
                           placeholder="Nom de la personne qui émet la facture"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ config('lara_sgmef_qr.default_operator.name') }}"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Personne ou système qui émet cette facture</p>
                </div>

                <!-- ID de l'opérateur -->
                <div>
                    <label for="operator_id" class="block text-sm font-medium text-gray-700 mb-2">
                        ID de l'opérateur <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="operator_id" 
                           name="operator[id]"
                           x-model="form.operator.id"
                           placeholder="Identifiant unique"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ config('lara_sgmef_qr.default_operator.id') }}"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Identifiant unique de l'opérateur</p>
                </div>
            </div>
        </div>

        <!-- Informations du client -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user text-blue-600 mr-2"></i>
                Client
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom du client -->
                <div>
                    <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du client <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="client_name" 
                           name="client[name]"
                           x-model="form.client.name"
                           placeholder="Nom du client"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <!-- IFU du client -->
                <div>
                    <label for="client_ifu" class="block text-sm font-medium text-gray-700 mb-2">
                        IFU du client (Optionnel)
                    </label>
                    <input type="text" 
                           id="client_ifu" 
                           name="client[ifu]"
                           x-model="form.client.ifu"
                           placeholder="1234567890123"
                           maxlength="13"
                           pattern="[0-9]{13}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">13 chiffres exactement (optionnel)</p>
                </div>
            </div>
        </div>

        <!-- Articles -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Articles
                </h3>
                <button type="button" 
                        @click="addItem()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i>
                    Ajouter un article
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(item, index) in form.items" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900" x-text="`Article ${index + 1}`"></h4>
                            <button type="button" 
                                    @click="removeItem(index)"
                                    x-show="form.items.length > 1"
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Nom de l'article -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       :name="`items[${index}][name]`"
                                       x-model="item.name"
                                       placeholder="Nom de l'article"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Prix unitaire -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Prix unitaire <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       :name="`items[${index}][price]`"
                                       x-model="item.price"
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Quantité -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantité <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       :name="`items[${index}][quantity]`"
                                       x-model="item.quantity"
                                       placeholder="1"
                                       min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Groupe de taxe -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Taxe <span class="text-red-500">*</span>
                                </label>
                                <select :name="`items[${index}][taxGroup]`"
                                        x-model="item.taxGroup"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <option value="">Sélectionner</option>
                                    @foreach($taxGroups ?? [] as $taxGroup)
                                        <option value="{{ $taxGroup['code'] }}">{{ $taxGroup['name'] }} ({{ $taxGroup['rate'] }}%)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Paiements -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                    Modes de paiement
                </h3>
                <button type="button" 
                        @click="addPayment()"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i>
                    Ajouter un paiement
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(payment, index) in form.payment" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900" x-text="`Paiement ${index + 1}`"></h4>
                            <button type="button" 
                                    @click="removePayment(index)"
                                    x-show="form.payment.length > 1"
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Type de paiement -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Type <span class="text-red-500">*</span>
                                </label>
                                <select :name="`payment[${index}][type]`"
                                        x-model="payment.type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <option value="">Sélectionner</option>
                                    @foreach($paymentTypes ?? [] as $paymentType)
                                        <option value="{{ $paymentType['code'] }}">{{ $paymentType['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Montant -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Montant <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       :name="`payment[${index}][amount]`"
                                       x-model="payment.amount"
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <a href="{{ route('sgmef.invoices.index') }}" 
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </a>
            
            <div class="flex space-x-4">
                <button type="button" 
                        @click="previewInvoice()"
                        class="px-6 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-eye mr-2"></i>
                    Prévisualiser
                </button>
                
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                    <i class="fas fa-save mr-2"></i>
                    <span x-text="isSubmitting ? 'Création...' : 'Créer la facture'"></span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function invoiceForm() {
    return {
        isSubmitting: false,
        form: {
            ifu: '{{ config('lara_sgmef_qr.default_ifu') }}',
            type: '',
            aib: '',
            operator: {
                name: '{{ config('lara_sgmef_qr.default_operator.name') }}',
                id: '{{ config('lara_sgmef_qr.default_operator.id') }}'
            },
            client: {
                name: '',
                ifu: ''
            },
            items: [
                {
                    name: '',
                    price: '',
                    quantity: 1,
                    taxGroup: ''
                }
            ],
            payment: [
                {
                    type: '',
                    amount: ''
                }
            ]
        },

        addItem() {
            this.form.items.push({
                name: '',
                price: '',
                quantity: 1,
                taxGroup: ''
            });
        },

        removeItem(index) {
            if (this.form.items.length > 1) {
                this.form.items.splice(index, 1);
            }
        },

        addPayment() {
            this.form.payment.push({
                type: '',
                amount: ''
            });
        },

        removePayment(index) {
            if (this.form.payment.length > 1) {
                this.form.payment.splice(index, 1);
            }
        },

        async previewInvoice() {
            try {
                const response = await fetch('{{ route('sgmef.invoices.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(`Prévisualisation:\nMontant total: ${data.data.total_amount} FCFA\nTaxes: ${data.data.total_tax_amount} FCFA`);
                } else {
                    alert('Erreur: ' + (data.message || 'Erreur inconnue'));
                }
            } catch (error) {
                alert('Erreur de connexion: ' + error.message);
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            
            try {
                const formData = new FormData();
                
                // Aplatir les données pour Laravel
                Object.keys(this.form).forEach(key => {
                    if (key === 'operator') {
                        formData.append('operator[name]', this.form.operator.name);
                        formData.append('operator[id]', this.form.operator.id);
                    } else if (key === 'client') {
                        formData.append('client[name]', this.form.client.name);
                        if (this.form.client.ifu) {
                            formData.append('client[ifu]', this.form.client.ifu);
                        }
                    } else if (key === 'items') {
                        this.form.items.forEach((item, index) => {
                            Object.keys(item).forEach(itemKey => {
                                formData.append(`items[${index}][${itemKey}]`, item[itemKey]);
                            });
                        });
                    } else if (key === 'payment') {
                        this.form.payment.forEach((payment, index) => {
                            Object.keys(payment).forEach(paymentKey => {
                                formData.append(`payment[${index}][${paymentKey}]`, payment[paymentKey]);
                            });
                        });
                    } else {
                        if (this.form[key]) {
                            formData.append(key, this.form[key]);
                        }
                    }
                });

                // Ajouter le token CSRF
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('{{ route('sgmef.invoices.store') }}', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    window.location.href = '{{ route('sgmef.invoices.index') }}';
                } else {
                    const errorData = await response.text();
                    alert('Erreur lors de la création: ' + errorData);
                }
            } catch (error) {
                alert('Erreur: ' + error.message);
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
