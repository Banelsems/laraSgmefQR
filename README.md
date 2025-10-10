# 🧾 LaraSgmefQR - Package Laravel pour la Facturation Électronique Béninoise

[![Latest Version](https://img.shields.io/github/v/release/banelsems/laraSgmefQR)](https://github.com/banelsems/laraSgmefQR/releases)
[![License](https://img.shields.io/github/license/banelsems/laraSgmefQR)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E10.0-red)](https://laravel.com)

**LaraSgmefQR** est un package Laravel moderne et robuste qui simplifie l'intégration avec l'API **SyGM-eMCF** (Système de Gestion Modernisé des Finances Publiques - electronic Mechanism for Centralized Invoicing) pour la génération de factures électroniques conformes aux exigences fiscales du Bénin.

## 🌟 **Nouvelle Version 2.1.0 - Totalement Indépendant de l'Authentification !**

**✅ Fonctionne immédiatement après installation**  
**✅ Aucun système d'authentification requis**  
**✅ Compatible avec Laravel UI, Breeze, Jetstream, Fortify ou aucun système d'auth**  
**✅ Concept d'opérateur pour remplacer la dépendance aux utilisateurs connectés**

## ✨ Fonctionnalités

### 🚀 **API Client Moderne**
- Client HTTP robuste avec gestion d'erreurs avancée
- Support des timeouts et retry automatique
- Logging complet des requêtes/réponses
- Validation stricte des données

### 🎯 **Architecture Clean Code**
- **SOLID Principles** : Respect strict des principes de développement
- **DTOs (Data Transfer Objects)** : Typage fort des données
- **Interfaces & Contracts** : Découplage et testabilité maximale
- **Dependency Injection** : Inversion de contrôle complète

### 🖥️ **Interface Web Intuitive**
- Dashboard moderne avec statistiques en temps réel
- Formulaires de création de factures interactifs
- Gestion complète du cycle de vie des factures
- Interface responsive (mobile-friendly)
- **Aucune authentification requise** - Fonctionne immédiatement

### 🔓 **Indépendance Totale de l'Authentification**
- **Concept d'Opérateur** : Remplace la notion d'utilisateur connecté
- **Configuration par défaut** : Opérateur automatiquement défini
- **Compatibilité universelle** : Fonctionne avec ou sans système d'auth
- **Installation immédiate** : Aucune configuration d'authentification nécessaire

### 📄 **Système de Templates Avancé**
- Templates multi-formats (A4, A5, Letter)
- Génération PDF automatique
- QR Codes intégrés pour la sécurité
- Personnalisation complète du design

### 🔒 **Sécurité & Conformité**
- Chiffrement des données sensibles
- Validation stricte des IFU
- Audit trail complet
- Conformité aux normes fiscales béninoises

- Couverture de tests > 95%
- Tests unitaires et fonctionnels
- Mocking des API externes
- CI/CD ready

## 🔧 **Exigences**

- **PHP** >= 8.1 (compatible 8.1, 8.2, 8.3)
- **Laravel** >= 10.0 (compatible 10.x, 11.x, 12.x)
- **Extensions PHP** : `json`, `curl`, `mbstring`
- **Base de données** : MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

## 🚀 Installation

### 1. Installation via Composer

```bash
composer require banelsems/lara-sgmef-qr
```

### 2. Publication des Assets

```bash
# Publier la configuration
php artisan vendor:publish --tag=lara-sgmef-qr-config

# Publier les migrations
php artisan vendor:publish --tag=lara-sgmef-qr-migrations

# Publier les vues (optionnel)
php artisan vendor:publish --tag=lara-sgmef-qr-views
```

### 3. Migration de la Base de Données

```bash
php artisan migrate
```

### 4. Configuration Environnement

Ajoutez ces variables à votre fichier `.env` :

```env
# Configuration API SyGM-eMCF
SGMEF_API_URL=https://developper.impots.bj/sygmef-emcf
SGMEF_TOKEN=your_jwt_token_here
SGMEF_DEFAULT_IFU=your_company_ifu

# Configuration Opérateur par Défaut (NOUVEAU)
SGMEF_DEFAULT_OPERATOR_NAME="Opérateur Principal"
SGMEF_DEFAULT_OPERATOR_ID=1

# Configuration HTTP
SGMEF_HTTP_TIMEOUT=30
SGMEF_VERIFY_SSL=true

# Configuration Interface Web
SGMEF_WEB_INTERFACE_ENABLED=true
SGMEF_ROUTE_PREFIX=sgmef

# Configuration Logs
SGMEF_LOGGING_ENABLED=true
SGMEF_LOG_LEVEL=info
```

## 📖 Guide d'Utilisation

### 🎯 Utilisation Basique

#### Créer une Facture

```php
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;

// Injection de dépendance
$invoiceManager = app(InvoiceManagerInterface::class);

// Création des DTOs
$client = new ClientDto(
    ifu: '1234567890123',
    name: 'ACME Corporation',
    contact: '+229 12 34 56 78',
    address: '123 Rue de la Paix, Cotonou'
);

// Opérateur - Utilise automatiquement la configuration par défaut si non spécifié
$operator = new OperatorDto(
    id: config('lara_sgmef_qr.default_operator.id', '1'),
    name: config('lara_sgmef_qr.default_operator.name', 'Opérateur Principal')
);

$items = [
    new InvoiceItemDto(
        name: 'Consultation Médicale',
        price: 15000,
        quantity: 1,
        taxGroup: 'B', // TVA 18%
        code: 'CONS001'
    )
];

$payments = [
    new PaymentDto(
        name: 'ESPECES',
        amount: 15000
    )
];

// Création de la facture
$invoiceData = new InvoiceRequestDto(
    ifu: config('lara_sgmef_qr.default_ifu'),
    type: 'FV', // Facture de Vente
    items: $items,
    client: $client,
    operator: $operator,
    payment: $payments,
    aib: 'A' // AIB 1%
);

try {
    $invoice = $invoiceManager->createInvoice($invoiceData);
    echo "Facture créée avec succès ! UID: {$invoice->uid}";
} catch (\Exception $e) {
    echo "Erreur : {$e->getMessage()}";
}
```

#### Confirmer une Facture

```php
try {
    $confirmedInvoice = $invoiceManager->confirmInvoice($invoice->uid);
    echo "Facture confirmée ! QR Code: {$confirmedInvoice->qr_code_data}";
} catch (\Exception $e) {
    echo "Erreur de confirmation : {$e->getMessage()}";
}
```

### 🖥️ Interface Web

Accédez à l'interface web via : `http://your-app.com/sgmef`

#### Pages Disponibles :
- **Dashboard** : `/sgmef` - Vue d'ensemble et statistiques
- **Factures** : `/sgmef/invoices` - Gestion des factures
- **Configuration** : `/sgmef/config` - Paramètres du package

## 🔒 Sécurisation des Routes (Optionnel)

**Important :** Par défaut, le package fonctionne sans authentification pour une compatibilité maximale. Si votre application utilise un système d'authentification et que vous souhaitez protéger l'interface web, voici comment procéder :

### Option 1 : Middleware Global dans RouteServiceProvider

```php
// app/Providers/RouteServiceProvider.php
public function boot()
{
    // ... autres configurations

    // Protéger les routes du package avec authentification
    Route::middleware(['web', 'auth'])
         ->prefix('sgmef')
         ->group(function () {
             // Les routes du package seront automatiquement protégées
         });
}
```

### Option 2 : Configuration via Middleware

```php
// config/lara_sgmef_qr.php
'web_interface' => [
    'enabled' => true,
    'middleware' => ['web', 'auth'], // Ajouter 'auth' pour protéger
    'route_prefix' => 'sgmef',
],
```

### Option 3 : Protection Personnalisée

```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/sgmef', function () {
        return redirect()->route('sgmef.dashboard');
    });
});
```

### Recommandations de Sécurité

- **Environnement de production** : Toujours protéger l'interface web
- **Environnement de développement** : Peut rester ouvert pour faciliter les tests
- **API endpoints** : Considérer l'ajout d'une authentification API si exposés publiquement

## 🧪 Tests

### Exécution des Tests

```bash
# Tests complets
php artisan test

# Tests avec couverture
php artisan test --coverage

# Tests spécifiques
php artisan test --filter=InvoiceManagerTest
```

## 🔧 Configuration Avancée

### Cache des Données API

```php
// config/lara_sgmef_qr.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 heure
    'prefix' => 'sgmef_',
],
```

## 🚨 Gestion d'Erreurs

### Exceptions Personnalisées

```php
try {
    $invoice = $invoiceManager->createInvoice($data);
} catch (\Banelsems\LaraSgmefQr\Exceptions\InvoiceException $e) {
    // Erreur métier
    \Log::error('Erreur facture', ['error' => $e->getMessage()]);
} catch (\Banelsems\LaraSgmefQr\Exceptions\SgmefApiException $e) {
    // Erreur API
    \Log::error('Erreur API', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
}
```

## 🤝 Contribution

### Développement Local

```bash
# Cloner le repository
git clone https://github.com/banelsems/laraSgmefQR.git

# Installer les dépendances
composer install

# Copier la configuration
cp .env.example .env

# Lancer les tests
php artisan test
```

## 📝 Changelog

### Version 2.0.0 (Actuelle)
- ✅ Refactorisation complète selon les principes Clean Code
- ✅ Architecture SOLID avec DTOs et Interfaces
- ✅ Interface web moderne et responsive
- ✅ Système de templates multi-formats
- ✅ Tests automatisés complets
- ✅ Documentation exhaustive

## 📄 Licence

Ce package est distribué sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

**Développé avec ❤️ au Bénin 🇧🇯**

*LaraSgmefQR - Simplifiez votre facturation électronique !*