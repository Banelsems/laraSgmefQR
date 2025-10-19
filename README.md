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

### 🧪 **Tests Automatisés**
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

---

## 🔗 **Intégration dans une Application Existante (Ex: Gestion de Vente)**

L'un des points forts de `LaraSgmefQR` est sa capacité à s'intégrer de manière transparente dans votre application existante, sans que vos utilisateurs aient à ressaisir les informations.

### Scénario : Automatiser la facturation après une vente

Imaginons que vous ayez une application de gestion de ventes avec les modèles `Order`, `OrderItem`, `Product` et `Customer`. Vous voulez générer la facture e-MECeF automatiquement lorsqu'une commande est validée.

#### Étape 1 : Mapper vos données vers les DTOs du package

Dans votre `OrderController`, après avoir créé la commande, vous pouvez utiliser ses données pour construire la facture.

```php
// app/Http/Controllers/OrderController.php

use App\Models\Order;
use App\Models\Customer;
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Illuminate\Support\Facades\Log;

public function store(Request $request)
{
    // ... (logique existante pour créer la vente, valider le panier, etc.)

    // 1. Créer la commande dans votre base de données
    $order = Order::create([
        'customer_id' => $request->customer_id,
        'total_amount' => $request->total_amount,
        'status' => 'completed',
        // ...
    ]);

    // 2. Préparer les données pour l'API e-MECeF en mappant vos modèles
    $customer = Customer::find($request->customer_id);

    // Mapper le client de votre app vers le DTO du package
    $clientDto = new ClientDto(
        ifu: $customer->ifu,
        name: $customer->name,
        contact: $customer->phone,
        address: $customer->address
    );

    // Mapper l'opérateur (l'utilisateur connecté qui a fait la vente)
    $operatorDto = new OperatorDto(
        id: (string) auth()->id(),
        name: auth()->user()->name
    );

    // Mapper les articles de la commande vers les DTOs du package
    $invoiceItems = $order->items->map(function ($item) {
        return new InvoiceItemDto(
            name: $item->product->name,
            price: $item->unit_price,
            quantity: $item->quantity,
            taxGroup: $item->product->tax_group, // Assurez-vous d'avoir ce champ dans votre modèle Product
            code: $item->product->sku
        );
    })->all();

    // Mapper le paiement
    $paymentDto = new PaymentDto(
        name: $request->payment_method, // Ex: 'ESPECES', 'CARTEBANCAIRE'
        amount: $order->total_amount
    );

    // 3. Créer le DTO principal pour la facture
    $invoiceData = InvoiceRequestDto::fromArray([
        'ifu' => config('lara_sgmef_qr.default_ifu'),
        'type' => 'FV',
        'client' => [
            'name' => $customer->name,
            'ifu' => $customer->ifu,
            'contact' => $customer->phone,
            'address' => $customer->address,
        ],
        'operator' => [
            'id' => (string) auth()->id(),
            'name' => auth()->user()->name,
        ],
        'items' => $order->items->map(function ($item) {
            return [
                'name' => $item->product->name,
                'price' => $item->unit_price,
                'quantity' => $item->quantity,
                'taxGroup' => $item->product->tax_group,
                'code' => $item->product->sku,
            ];
        })->toArray(),
        'payment' => [[
            'name' => $request->payment_method,
            'amount' => $order->total_amount,
        ]],
    ]);

    // 4. Appeler le service de LaraSgmefQR
    try {
        $invoiceManager = app(InvoiceManagerInterface::class);
        
        // Créer la facture sur l'API e-MECeF
        $invoice = $invoiceManager->createInvoice($invoiceData);
        
        // Confirmer la facture pour obtenir le QR Code et le code MECeF
        $confirmedInvoice = $invoiceManager->confirmInvoice($invoice->uid);

        // 5. Mettre à jour votre commande avec les informations de l'e-MECeF
        $order->update([
            'mECeF_uid' => $confirmedInvoice->uid,
            'mECeF_code' => $confirmedInvoice->mecf_code,
            'qr_code_data' => $confirmedInvoice->qr_code_data,
            'status' => 'invoiced'
        ]);

        return redirect()->route('orders.show', $order->id)
                         ->with('success', 'Vente et facture e-MECeF créées avec succès !');

    } catch (\Exception $e) {
        // GESTION D'ERREUR CRUCIALE
        Log::error("Échec de la création de la facture e-MECeF pour la commande {$order->id}", [
            'error' => $e->getMessage()
        ]);

        // On ne fait pas échouer la vente, mais on la marque comme en attente
        $order->update(['status' => 'pending_emecf_invoice']);

        return redirect()->route('orders.show', $order->id)
                         ->with('warning', 'Vente enregistrée, mais la facture e-MECeF a échoué. Veuillez réessayer depuis le détail de la commande.');
    }
}
```

### 📊 Récupérer et Utiliser les Données de LaraSgmefQR

Votre application peut aussi avoir besoin de lire les informations des factures stockées par le package. Le modèle `Invoice` est disponible pour cela.

#### Exemple : Afficher les détails d'une facture sur la page d'une commande

Dans votre vue `orders/show.blade.php`, vous pouvez afficher les détails de la facture associée.

```php
// Dans votre modèle Order.php
use Banelsems\LaraSgmefQr\Models\Invoice;

class Order extends Model
{
    // ...

    /**
     * Get the e-MECeF invoice associated with the order.
     */
    public function emecfInvoice()
    {
        return $this->belongsTo(Invoice::class, 'mECeF_uid', 'uid');
    }
}
```

```blade
{{-- resources/views/orders/show.blade.php --}}

<h1>Détails de la Commande #{{ $order->id }}</h1>

<p>Client : {{ $order->customer->name }}</p>
<p>Montant : {{ number_format($order->total_amount, 0, ',', ' ') }} XOF</p>

@if ($order->emecfInvoice)
    <div class="alert alert-success">
        <h3>Facture e-MECeF</h3>
        <p><strong>Code MECeF/DGI :</strong> {{ $order->emecfInvoice->mecf_code }}</p>
        <p><strong>Statut :</strong> {{ $order->emecfInvoice->status }}</p>
        
        {{-- Vous pouvez même afficher le QR Code --}}
        <img src="data:image/png;base64, {{ base64_encode(QRCode::format('png')->size(150)->generate($order->emecfInvoice->qr_code_data)) }}" alt="QR Code">

        {{-- Lien pour télécharger le PDF généré par le package --}}
        <a href="{{ route('sgmef.invoices.download', $order->emecfInvoice->id) }}" class="btn btn-primary" target="_blank">
            Télécharger la Facture PDF
        </a>
    </div>
@elseif($order->status === 'pending_emecf_invoice')
    <div class="alert alert-warning">
        La facture e-MECeF est en attente de génération. Veuillez réessayer plus tard.
    </div>
@endif
```

---

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

## 📚 Guide de Référence Complet

### 🗄️ **Modèle de Données (Invoice Model)**

Le modèle principal `Banelsems\LaraSgmefQr\Models\Invoice` représente une facture électronique dans votre base de données.

#### **Attributs du Modèle**

| Attribut | Type | Description |
|----------|------|-------------|
| `id` | `bigint` | Identifiant unique auto-incrémenté |
| `uid` | `string` | Identifiant unique fourni par l'API SyGM-eMCF |
| `ifu` | `string` | IFU de l'entreprise émettrice (13 chiffres) |
| `type` | `string` | Type de facture (FV, FA, EV, EA) |
| `status` | `enum` | Statut de la facture (voir InvoiceStatusEnum) |
| `client_name` | `string` | Nom du client |
| `customer_ifu` | `string` | IFU du client (optionnel) |
| `operator_name` | `string` | Nom de l'opérateur qui a créé la facture |
| `operator_id` | `string` | ID de l'opérateur |
| `total_amount` | `decimal` | Montant total TTC de la facture |
| `total_tax_amount` | `decimal` | Montant total des taxes |
| `total_aib_amount` | `decimal` | Montant total de l'AIB |
| `items` | `json` | Articles de la facture (format JSON) |
| `payment` | `json` | Modes de paiement (format JSON) |
| `raw_request` | `json` | Données brutes envoyées à l'API |
| `raw_response` | `json` | Réponse brute de l'API |
| `qr_code_data` | `text` | Données du QR Code (après confirmation) |
| `mecf_code` | `string` | Code MECeF officiel (après confirmation) |
| `confirmed_at` | `timestamp` | Date de confirmation |
| `cancelled_at` | `timestamp` | Date d'annulation |
| `created_at` | `timestamp` | Date de création |
| `updated_at` | `timestamp` | Date de dernière modification |

#### **Énumération InvoiceStatusEnum**

```php
enum InvoiceStatusEnum: string
{
    case PENDING = 'pending';      // En attente de confirmation
    case CONFIRMED = 'confirmed';  // Confirmée avec QR Code et MECeF
    case CANCELLED = 'cancelled';  // Annulée
    case ERROR = 'error';         // Erreur lors du traitement
}
```

**Méthodes utilitaires :**
- `isPending()` : Vérifie si la facture est en attente
- `isConfirmed()` : Vérifie si la facture est confirmée
- `isCancelled()` : Vérifie si la facture est annulée
- `hasError()` : Vérifie si la facture a une erreur

### ⚙️ **Configuration Complète (config/lara_sgmef_qr.php)**

#### **Section API**
```php
'api' => [
    'url' => env('SGMEF_API_URL', 'https://developper.impots.bj/sygmef-emcf'),
    'token' => env('SGMEF_TOKEN'),
    'timeout' => env('SGMEF_HTTP_TIMEOUT', 30),
    'verify_ssl' => env('SGMEF_VERIFY_SSL', true),
    'retry_attempts' => env('SGMEF_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SGMEF_RETRY_DELAY', 1000), // millisecondes
],
```

#### **Section Entreprise**
```php
'company' => [
    'default_ifu' => env('SGMEF_DEFAULT_IFU'),
    'name' => env('SGMEF_COMPANY_NAME'),
    'address' => env('SGMEF_COMPANY_ADDRESS'),
],
```

#### **Section Opérateur par Défaut**
```php
'default_operator' => [
    'name' => env('SGMEF_DEFAULT_OPERATOR_NAME', 'Opérateur Principal'),
    'id' => env('SGMEF_DEFAULT_OPERATOR_ID', '1'),
],
```

#### **Section Interface Web**
```php
'web_interface' => [
    'enabled' => env('SGMEF_WEB_INTERFACE_ENABLED', true),
    'middleware' => ['web'], // Ajouter 'auth' pour protéger
    'route_prefix' => env('SGMEF_ROUTE_PREFIX', 'sgmef'),
    'items_per_page' => env('SGMEF_ITEMS_PER_PAGE', 15),
],
```

#### **Section Cache**
```php
'cache' => [
    'enabled' => env('SGMEF_CACHE_ENABLED', true),
    'ttl' => env('SGMEF_CACHE_TTL', 3600), // 1 heure
    'prefix' => 'sgmef_',
    'store' => env('SGMEF_CACHE_STORE', 'default'),
],
```

#### **Section Logging**
```php
'logging' => [
    'enabled' => env('SGMEF_LOGGING_ENABLED', true),
    'level' => env('SGMEF_LOG_LEVEL', 'info'),
    'channel' => env('SGMEF_LOG_CHANNEL', 'daily'),
    'log_requests' => env('SGMEF_LOG_REQUESTS', true),
    'log_responses' => env('SGMEF_LOG_RESPONSES', true),
],
```

### 🔧 **Services et Contrats**

#### **InvoiceManagerInterface**

Le service principal pour la gestion des factures.

```php
interface InvoiceManagerInterface
{
    /**
     * Créer une nouvelle facture
     */
    public function createInvoice(InvoiceRequestDto $data): Invoice;
    
    /**
     * Confirmer une facture (génère QR Code et MECeF)
     */
    public function confirmInvoice(string $uid): Invoice;
    
    /**
     * Annuler une facture
     */
    public function cancelInvoice(string $uid): Invoice;
    
    /**
     * Récupérer le statut d'une facture
     */
    public function getInvoiceStatus(string $uid): InvoiceStatusEnum;
    
    /**
     * Synchroniser une facture avec l'API
     */
    public function syncInvoice(string $uid): Invoice;
    
    /**
     * Récupérer une facture par UID
     */
    public function getInvoice(string $uid): ?Invoice;
    
    /**
     * Lister les factures avec pagination
     */
    public function listInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
```

#### **SgmefApiClientInterface**

Client HTTP pour l'API SyGM-eMCF.

```php
interface SgmefApiClientInterface
{
    /**
     * Créer une facture via l'API
     */
    public function createInvoice(InvoiceRequestDto $data): InvoiceResponseDto;
    
    /**
     * Confirmer une facture via l'API
     */
    public function confirmInvoice(string $uid): InvoiceResponseDto;
    
    /**
     * Annuler une facture via l'API
     */
    public function cancelInvoice(string $uid): InvoiceResponseDto;
    
    /**
     * Récupérer le statut d'une facture
     */
    public function getInvoiceStatus(string $uid): InvoiceResponseDto;
    
    /**
     * Tester la connexion à l'API
     */
    public function testConnection(): bool;
}
```

### 📋 **Référence des DTOs (Data Transfer Objects)**

#### **InvoiceRequestDto**
```php
class InvoiceRequestDto
{
    public string $ifu;              // IFU entreprise (13 chiffres)
    public string $type;             // Type: FV, FA, EV, EA
    public ?string $aib;             // AIB: A (1%) ou B (5%)
    public ?string $reference;       // Référence interne
    public ClientDto $client;        // Informations client
    public OperatorDto $operator;    // Informations opérateur
    public array $items;             // Articles (InvoiceItemDto[])
    public array $payment;           // Paiements (PaymentDto[])
    
    public static function fromArray(array $data): self;
    public function toArray(): array;
    public function validate(): array; // Retourne les erreurs
}
```

#### **ClientDto**
```php
class ClientDto
{
    public string $name;             // Nom du client (requis)
    public ?string $ifu;             // IFU client (13 chiffres, optionnel)
    public ?string $contact;         // Téléphone/Email
    public ?string $address;         // Adresse complète
}
```

#### **OperatorDto**
```php
class OperatorDto
{
    public string $id;               // ID unique de l'opérateur
    public string $name;             // Nom de l'opérateur
}
```

#### **InvoiceItemDto**
```php
class InvoiceItemDto
{
    public string $name;             // Nom de l'article
    public float $price;             // Prix unitaire HT
    public int $quantity;            // Quantité
    public string $taxGroup;         // Groupe de taxe: A, B, C, D, E, F
    public ?string $code;            // Code article (SKU)
    public ?string $description;     // Description détaillée
}
```

#### **PaymentDto**
```php
class PaymentDto
{
    public string $name;             // Type: ESPECES, CARTEBANCAIRE, CHEQUE, VIREMENT
    public float $amount;            // Montant du paiement
}
```

#### **InvoiceResponseDto**
```php
class InvoiceResponseDto
{
    public string $uid;              // UID de la facture
    public string $status;           // Statut de la facture
    public float $totalAmount;       // Montant total TTC (mappé depuis 'total')
    public float $totalTaxAmount;    // Total des taxes (mappé depuis 'ts')
    public float $totalAibAmount;    // Total AIB (mappé depuis 'aib')
    public array $items;             // Articles avec calculs
    public ?string $qrCodeData;      // Données QR Code (si confirmée)
    public ?string $mecfCode;        // Code MECeF (si confirmée)
    public ?SecurityElementsDto $security; // Éléments de sécurité
}
```

### 🎯 **Événements (Events)**

Le package déclenche plusieurs événements Laravel pour permettre l'intégration avec votre application.

#### **InvoiceCreated**
```php
class InvoiceCreated
{
    public Invoice $invoice;
    public InvoiceRequestDto $requestData;
    
    // Déclenché après la création réussie d'une facture
}
```

#### **InvoiceConfirmed**
```php
class InvoiceConfirmed
{
    public Invoice $invoice;
    public string $qrCodeData;
    public string $mecfCode;
    
    // Déclenché après la confirmation d'une facture
}
```

#### **InvoiceCancelled**
```php
class InvoiceCancelled
{
    public Invoice $invoice;
    public string $reason;
    
    // Déclenché après l'annulation d'une facture
}
```

#### **InvoiceCreationFailed**
```php
class InvoiceCreationFailed
{
    public Exception $exception;
    public InvoiceRequestDto $requestData;
    public ?string $errorCode;
    
    // Déclenché en cas d'échec de création
}
```

#### **Écouter les Événements**
```php
// Dans EventServiceProvider.php
protected $listen = [
    InvoiceCreated::class => [
        SendInvoiceNotification::class,
        UpdateOrderStatus::class,
    ],
    InvoiceConfirmed::class => [
        SendConfirmationEmail::class,
        GeneratePdfInvoice::class,
    ],
];
```

### 🌐 **Interface Web - Référence des Routes**

| Méthode | URL | Nom de Route | Description |
|---------|-----|--------------|-------------|
| `GET` | `/sgmef` | `sgmef.dashboard` | Tableau de bord principal |
| `GET` | `/sgmef/invoices` | `sgmef.invoices.index` | Liste des factures |
| `GET` | `/sgmef/invoices/create` | `sgmef.invoices.create` | Formulaire de création |
| `POST` | `/sgmef/invoices` | `sgmef.invoices.store` | Créer une facture |
| `GET` | `/sgmef/invoices/{uid}` | `sgmef.invoices.show` | Détails d'une facture |
| `POST` | `/sgmef/invoices/{uid}/confirm` | `sgmef.invoices.confirm` | Confirmer une facture |
| `POST` | `/sgmef/invoices/{uid}/cancel` | `sgmef.invoices.cancel` | Annuler une facture |
| `GET` | `/sgmef/invoices/{uid}/sync` | `sgmef.invoices.sync` | Synchroniser avec l'API |
| `GET` | `/sgmef/invoices/{uid}/pdf` | `sgmef.invoices.pdf` | Télécharger PDF |
| `GET` | `/sgmef/invoices/{uid}/print` | `sgmef.invoices.print` | Version imprimable |
| `POST` | `/sgmef/invoices/preview` | `sgmef.invoices.preview` | Prévisualiser (AJAX) |
| `GET` | `/sgmef/config` | `sgmef.config.index` | Configuration du package |
| `POST` | `/sgmef/config` | `sgmef.config.store` | Sauvegarder la config |
| `GET` | `/sgmef/config/test` | `sgmef.config.test` | Tester la connexion API |

### 🔍 **Helpers et Utilitaires**

#### **Helpers Statiques**
```php
// Récupérer l'opérateur par défaut
$operator = LaraSgmefQRServiceProvider::getDefaultOperator();

// Accéder aux services via l'IoC
$apiClient = app('sgmef.api');
$invoiceManager = app('sgmef.invoices');
$defaultOperator = app('sgmef.default_operator');
```

#### **Validation IFU**
```php
// Valider un IFU béninois
if (preg_match('/^\d{13}$/', $ifu)) {
    // IFU valide
}
```

#### **Groupes de Taxes**
```php
$taxGroups = [
    'A' => 'Exonéré (0%)',
    'B' => 'TVA 18%',
    'C' => 'TVA 18% + AIB 1%',
    'D' => 'TVA 18% + AIB 5%',
    'E' => 'Régime spécial',
    'F' => 'Exportation (0%)',
];
```

### 🚨 **Gestion d'Erreurs et Exceptions**

#### **Exceptions Personnalisées**

```php
// Exception générale du package
SgmefException extends Exception

// Exception liée aux factures
InvoiceException extends SgmefException
- InvoiceNotFoundException
- InvoiceAlreadyConfirmedException
- InvoiceValidationException

// Exception liée à l'API
SgmefApiException extends SgmefException
- ApiConnectionException
- ApiAuthenticationException
- ApiRateLimitException
- ApiServerException
```

#### **Codes d'Erreur API**
```php
const ERROR_CODES = [
    'INVALID_IFU' => 'IFU invalide ou non reconnu',
    'INVALID_TOKEN' => 'Token d\'authentification invalide',
    'INVOICE_NOT_FOUND' => 'Facture introuvable',
    'ALREADY_CONFIRMED' => 'Facture déjà confirmée',
    'NETWORK_ERROR' => 'Erreur de connexion réseau',
    'SERVER_ERROR' => 'Erreur serveur API',
];
```

### 📊 **Monitoring et Métriques**

#### **Logs Disponibles**
- `sgmef.requests` : Requêtes vers l'API
- `sgmef.responses` : Réponses de l'API
- `sgmef.errors` : Erreurs et exceptions
- `sgmef.performance` : Métriques de performance

#### **Métriques Collectées**
- Nombre de factures créées/confirmées/annulées
- Temps de réponse de l'API
- Taux d'erreur par type
- Utilisation du cache

---

## 📝 Changelog

### Version 2.1.0 (Actuelle) - Indépendance Totale de l'Authentification
- 🚀 **BREAKING IMPROVEMENT** : Suppression de toute dépendance d'authentification
- ✅ Concept d'opérateur configurable remplaçant les utilisateurs connectés
- ✅ Interface web accessible sans authentification (sécurisation optionnelle)
- ✅ Configuration par défaut pour installation immédiate
- ✅ Tests d'indépendance d'authentification complets
- ✅ Documentation de migration et guides détaillés
- ✅ Compatibilité universelle avec tous projets Laravel

### Version 2.0.0 - Refactorisation Clean Code
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