@extends('lara-sgmef-qr::layouts.app')

@section('title', 'Configuration')
@section('page-title', 'Configuration du Package')

@section('breadcrumb')
    <a href="{{ route('sgmef.dashboard') }}" class="text-blue-600 hover:text-blue-800">Tableau de bord</a>
    <span class="mx-2">/</span>
    <span class="text-gray-500">Configuration</span>
@endsection

@section('content')
<div x-data="configForm()" class="max-w-4xl mx-auto space-y-6">
    
    <!-- Informations importantes -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Configuration du Package LaraSgmefQR
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Cette page vous permet de configurer les paramètres du package pour l'intégration avec l'API SyGM-eMCF du Bénin.</p>
                    <p class="mt-1"><strong>Note importante :</strong> Ce package fonctionne sans système d'authentification. L'opérateur par défaut sera utilisé pour toutes les factures.</p>
                </div>
            </div>
        </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
        @csrf
        
        <!-- Configuration API -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-cloud text-blue-600 mr-2"></i>
                Configuration API SyGM-eMCF
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- URL de l'API -->
                <div class="md:col-span-2">
                    <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL de l'API <span class="text-red-500">*</span>
                    </label>
                    <input type="url" 
                           id="api_url" 
                           name="api_url"
                           x-model="form.api_url"
                           placeholder="https://developper.impots.bj/sygmef-emcf"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ $config['api_url'] ?? '' }}"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        URL de base de l'API SyGM-eMCF (test ou production)
                    </p>
                </div>

                <!-- Token -->
                <div class="md:col-span-2">
                    <label for="token" class="block text-sm font-medium text-gray-700 mb-2">
                        Token JWT <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="token" 
                           name="token"
                           x-model="form.token"
                           placeholder="{{ $config['token'] ? 'Token configuré (masqué)' : 'Votre token JWT' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           {{ $config['token'] ? '' : 'required' }}>
                    <p class="mt-1 text-xs text-gray-500">
                        Token d'authentification fourni par l'administration fiscale béninoise
                    </p>
                </div>

                <!-- IFU par défaut -->
                <div>
                    <label for="default_ifu" class="block text-sm font-medium text-gray-700 mb-2">
                        IFU par défaut <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="default_ifu" 
                           name="default_ifu"
                           x-model="form.default_ifu"
                           placeholder="1234567890123"
                           maxlength="13"
                           pattern="[0-9]{13}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ $config['default_ifu'] ?? '' }}"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        IFU de votre entreprise (13 chiffres)
                    </p>
                </div>

                <!-- Test de connexion -->
                <div class="flex items-end">
                    <button type="button" 
                            @click="testConnection()"
                            :disabled="isTestingConnection"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50">
                        <i class="fas fa-plug mr-2"></i>
                        <span x-text="isTestingConnection ? 'Test en cours...' : 'Tester la connexion'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Configuration de l'opérateur par défaut -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-cog text-blue-600 mr-2"></i>
                Opérateur par Défaut
            </h3>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-yellow-600"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">
                            Concept d'Opérateur
                        </h4>
                        <div class="mt-1 text-sm text-yellow-700">
                            <p>L'opérateur est la personne ou le système qui émet les factures. Comme ce package fonctionne sans authentification, vous devez définir un opérateur par défaut qui sera utilisé pour toutes les factures.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom de l'opérateur -->
                <div>
                    <label for="default_operator_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'opérateur <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="default_operator_name" 
                           name="default_operator_name"
                           x-model="form.default_operator_name"
                           placeholder="Nom de la personne ou du système"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ $config['default_operator_name'] ?? '' }}"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        Nom qui apparaîtra sur toutes les factures
                    </p>
                </div>

                <!-- ID de l'opérateur -->
                <div>
                    <label for="default_operator_id" class="block text-sm font-medium text-gray-700 mb-2">
                        ID de l'opérateur
                    </label>
                    <input type="text" 
                           id="default_operator_id" 
                           name="default_operator_id"
                           x-model="form.default_operator_id"
                           placeholder="Identifiant unique"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="1">
                    <p class="mt-1 text-xs text-gray-500">
                        Identifiant unique de l'opérateur (par défaut: 1)
                    </p>
                </div>
            </div>
        </div>

        <!-- Configuration HTTP -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-network-wired text-blue-600 mr-2"></i>
                Configuration HTTP
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Timeout -->
                <div>
                    <label for="http_timeout" class="block text-sm font-medium text-gray-700 mb-2">
                        Timeout (secondes)
                    </label>
                    <input type="number" 
                           id="http_timeout" 
                           name="http_timeout"
                           x-model="form.http_timeout"
                           min="5"
                           max="120"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ $config['http_timeout'] ?? 30 }}">
                    <p class="mt-1 text-xs text-gray-500">
                        Temps d'attente maximum pour les requêtes API
                    </p>
                </div>

                <!-- Vérification SSL -->
                <div>
                    <label for="verify_ssl" class="block text-sm font-medium text-gray-700 mb-2">
                        Vérification SSL
                    </label>
                    <select id="verify_ssl" 
                            name="verify_ssl"
                            x-model="form.verify_ssl"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" {{ ($config['verify_ssl'] ?? true) ? 'selected' : '' }}>Activée (recommandé)</option>
                        <option value="0" {{ !($config['verify_ssl'] ?? true) ? 'selected' : '' }}>Désactivée</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Vérification des certificats SSL (recommandé en production)
                    </p>
                </div>
            </div>
        </div>

        <!-- Configuration des logs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                Configuration des Logs
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Logs activés -->
                <div>
                    <label for="logging_enabled" class="block text-sm font-medium text-gray-700 mb-2">
                        Logs activés
                    </label>
                    <select id="logging_enabled" 
                            name="logging_enabled"
                            x-model="form.logging_enabled"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" {{ ($config['logging_enabled'] ?? true) ? 'selected' : '' }}>Activés</option>
                        <option value="0" {{ !($config['logging_enabled'] ?? true) ? 'selected' : '' }}>Désactivés</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Enregistrement des activités du package
                    </p>
                </div>

                <!-- Interface web -->
                <div>
                    <label for="web_interface_enabled" class="block text-sm font-medium text-gray-700 mb-2">
                        Interface web
                    </label>
                    <select id="web_interface_enabled" 
                            name="web_interface_enabled"
                            x-model="form.web_interface_enabled"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" {{ ($config['web_interface_enabled'] ?? true) ? 'selected' : '' }}>Activée</option>
                        <option value="0" {{ !($config['web_interface_enabled'] ?? true) ? 'selected' : '' }}>Désactivée</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Activer/désactiver cette interface web
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Les modifications seront sauvegardées dans le fichier config/lara_sgmef_qr.php
            </div>
            
            <div class="flex space-x-4">
                <button type="button" 
                        @click="resetForm()"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-undo mr-2"></i>
                    Réinitialiser
                </button>
                
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                    <i class="fas fa-save mr-2"></i>
                    <span x-text="isSubmitting ? 'Sauvegarde...' : 'Sauvegarder'"></span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function configForm() {
    return {
        isSubmitting: false,
        isTestingConnection: false,
        form: {
            api_url: '{{ $config['api_url'] ?? '' }}',
            token: '',
            default_ifu: '{{ $config['default_ifu'] ?? '' }}',
            default_operator_name: '{{ $config['default_operator_name'] ?? '' }}',
            default_operator_id: '1',
            http_timeout: {{ $config['http_timeout'] ?? 30 }},
            verify_ssl: '{{ ($config['verify_ssl'] ?? true) ? '1' : '0' }}',
            logging_enabled: '{{ ($config['logging_enabled'] ?? true) ? '1' : '0' }}',
            web_interface_enabled: '{{ ($config['web_interface_enabled'] ?? true) ? '1' : '0' }}'
        },

        async testConnection() {
            this.isTestingConnection = true;
            
            try {
                const response = await fetch('{{ route('sgmef.config.test') }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Connexion réussie !\n\n' + data.message);
                } else {
                    alert('❌ Erreur de connexion :\n\n' + data.message);
                }
            } catch (error) {
                alert('❌ Erreur de connexion :\n\n' + error.message);
            } finally {
                this.isTestingConnection = false;
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            
            try {
                const formData = new FormData();
                
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('{{ route('sgmef.config.store') }}', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    alert('✅ Configuration sauvegardée avec succès !');
                    window.location.reload();
                } else {
                    const errorData = await response.text();
                    alert('❌ Erreur lors de la sauvegarde :\n\n' + errorData);
                }
            } catch (error) {
                alert('❌ Erreur :\n\n' + error.message);
            } finally {
                this.isSubmitting = false;
            }
        },

        resetForm() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
                window.location.reload();
            }
        }
    }
}
</script>
@endpush
