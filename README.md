# 🧾 LaraSgmefQR - Package Laravel pour la Facturation Électronique Béninoise

[![Latest Version](https://img.shields.io/github/v/release/banelsems/laraSgmefQR)](https://github.com/banelsems/laraSgmefQR/releases)
[![License](https://img.shields.io/github/license/banelsems/laraSgmefQR)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E10.0-red)](https://laravel.com)

**LaraSgmefQR** est un package Laravel moderne et robuste qui simplifie l'intégration avec l'API **SyGM-eMCF** (Système de Gestion Modernisé des Finances Publiques - electronic Mechanism for Centralized Invoicing) pour la génération de factures électroniques conformes aux exigences fiscales du Bénin.

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

### 🧪 **Tests Automatisés**
- Couverture de tests > 95%
- Tests unitaires et fonctionnels
- Mocking des API externes
- CI/CD ready

## 📋 Prérequis

- **PHP** >= 8.1
- **Laravel** >= 10.0
- **Extensions PHP** : `ext-json`, `ext-curl`, `ext-mbstring`
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

$operator = new OperatorDto(
    id: 1,
    name: 'Caissier Principal'
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