# ⚡ Guide de Démarrage Rapide - LaraSgmefQR v2.1.0

## 🎯 Installation en 5 Minutes

### Étape 1 : Installation
```bash
composer require banelsems/lara-sgmef-qr
```

### Étape 2 : Publication
```bash
php artisan vendor:publish --tag=lara-sgmef-qr-config
php artisan vendor:publish --tag=lara-sgmef-qr-migrations
php artisan migrate
```

### Étape 3 : Configuration
```env
# .env
SGMEF_API_URL=https://developper.impots.bj/sygmef-emcf
SGMEF_TOKEN=your_jwt_token_here
SGMEF_DEFAULT_IFU=your_company_ifu
SGMEF_DEFAULT_OPERATOR_NAME="Votre Nom"
```

### Étape 4 : Test
```bash
php artisan serve
# Visitez http://localhost:8000/sgmef
```

## 🚀 Première Facture en Code

```php
<?php

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;

// Dans un contrôleur ou une commande Artisan
class QuickInvoiceExample
{
    public function createSampleInvoice()
    {
        // 1. Récupérer le gestionnaire de factures
        $invoiceManager = app(InvoiceManagerInterface::class);

        // 2. Créer les données de la facture
        $invoiceData = InvoiceRequestDto::fromArray([
            'ifu' => config('lara_sgmef_qr.default_ifu'),
            'type' => 'FV', // Facture de Vente
            
            // Client
            'client' => [
                'name' => 'ACME Corporation',
                'ifu' => '1234567890123', // Optionnel
            ],
            
            // Opérateur (optionnel - sera rempli automatiquement)
            'operator' => [
                'name' => config('lara_sgmef_qr.default_operator.name'),
                'id' => config('lara_sgmef_qr.default_operator.id'),
            ],
            
            // Articles
            'items' => [
                [
                    'name' => 'Consultation Médicale',
                    'price' => 15000,
                    'quantity' => 1,
                    'taxGroup' => 'B', // TVA 18%
                ]
            ],
            
            // Paiements
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 15000,
                ]
            ],
        ]);

        // 3. Créer la facture
        try {
            $invoice = $invoiceManager->createInvoice($invoiceData);
            
            echo "✅ Facture créée avec succès !\n";
            echo "UID: {$invoice->uid}\n";
            echo "Statut: {$invoice->status->value}\n";
            
            return $invoice;
            
        } catch (\Exception $e) {
            echo "❌ Erreur: {$e->getMessage()}\n";
            throw $e;
        }
    }
}
```

## 🖥️ Interface Web Immédiate

### Accès Direct
```
http://your-app.com/sgmef
```

### Pages Disponibles
- **Dashboard** : Vue d'ensemble et statistiques
- **Créer une facture** : Formulaire interactif
- **Liste des factures** : Gestion complète
- **Configuration** : Paramètres du package

### Fonctionnalités
- ✅ **Aucune authentification requise**
- ✅ **Interface responsive**
- ✅ **Formulaires intuitifs**
- ✅ **Validation en temps réel**

## 🔧 Configuration Avancée

### Opérateur par Défaut
```php
// config/lara_sgmef_qr.php
'default_operator' => [
    'name' => 'Caissier Principal',
    'id' => '1',
],
```

### Sécurisation (Production)
```php
// config/lara_sgmef_qr.php
'web_interface' => [
    'enabled' => true,
    'middleware' => ['web', 'auth'], // Ajouter 'auth'
    'route_prefix' => 'sgmef',
],
```

### Variables d'Environnement Complètes
```env
# API Configuration
SGMEF_API_URL=https://developper.impots.bj/sygmef-emcf
SGMEF_TOKEN=your_jwt_token
SGMEF_DEFAULT_IFU=your_ifu

# Opérateur par Défaut
SGMEF_DEFAULT_OPERATOR_NAME="Nom Opérateur"
SGMEF_DEFAULT_OPERATOR_ID=1

# Interface Web
SGMEF_WEB_INTERFACE_ENABLED=true
SGMEF_ROUTE_PREFIX=sgmef

# HTTP Configuration
SGMEF_HTTP_TIMEOUT=30
SGMEF_VERIFY_SSL=true

# Logging
SGMEF_LOGGING_ENABLED=true
SGMEF_LOG_LEVEL=info
```

## 🧪 Test Rapide

### Commande Artisan de Test
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;

class TestSgmefInvoice extends Command
{
    protected $signature = 'sgmef:test-invoice';
    protected $description = 'Test the creation of a SGMEF invoice';

    public function handle()
    {
        $this->info('🧪 Test de création de facture SGMEF...');

        try {
            $invoiceManager = app(InvoiceManagerInterface::class);
            
            $invoiceData = InvoiceRequestDto::fromArray([
                'ifu' => config('lara_sgmef_qr.default_ifu'),
                'type' => 'FV',
                'client' => ['name' => 'Client Test'],
                'items' => [[
                    'name' => 'Article Test',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'B',
                ]],
                'payment' => [[
                    'name' => 'ESPECES',
                    'amount' => 1000,
                ]],
            ]);

            $invoice = $invoiceManager->createInvoice($invoiceData);
            
            $this->info("✅ Facture créée avec succès !");
            $this->line("UID: {$invoice->uid}");
            $this->line("Opérateur: {$invoice->operator_name}");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
```

Enregistrez cette commande et testez :
```bash
php artisan sgmef:test-invoice
```

## 🔍 Dépannage Rapide

### Erreur "Route [login] not defined"
```bash
# Solution
php artisan vendor:publish --tag=lara-sgmef-qr-config --force
php artisan config:clear
```

### Interface inaccessible
```bash
# Vérification
php artisan route:list | grep sgmef
php artisan config:show lara_sgmef_qr.web_interface
```

### Erreur d'opérateur
```bash
# Vérification de la configuration
php artisan tinker
>>> config('lara_sgmef_qr.default_operator')
```

## 📚 Ressources

- **Documentation complète** : `README.md`
- **Guide de migration** : `MIGRATION_GUIDE_v2.1.0.md`
- **Notes de version** : `RELEASE_NOTES_v2.1.0.md`
- **Tests** : `tests/Feature/AuthIndependenceTest.php`

## 🎉 Félicitations !

Votre package LaraSgmefQR est maintenant opérationnel et totalement indépendant de l'authentification. Vous pouvez créer des factures électroniques conformes aux exigences béninoises en quelques minutes !

### Prochaines Étapes

1. **Testez** l'interface web
2. **Créez** votre première facture
3. **Configurez** la sécurisation si nécessaire
4. **Explorez** les fonctionnalités avancées

**🚀 Bonne facturation électronique !**
