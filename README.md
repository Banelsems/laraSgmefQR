# LaraSgmefQR

Package Laravel pour intégrer l'API e-MECeF / SyGM-eMCF de la Direction Générale des Impôts du Bénin.

[![Latest Version](https://img.shields.io/github/v/release/banelsems/laraSgmefQR)](https://github.com/Banelsems/laraSgmefQR/releases)
[![License](https://img.shields.io/github/license/banelsems/laraSgmefQR)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1%20%7C%20%5E8.2%20%7C%20%5E8.3-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10%20%7C%2011%20%7C%2012-red)](https://laravel.com)

LaraSgmefQR v3.0.3 fournit un client API, des DTOs, une interface web, des jobs Laravel et des outils de conformité pour créer, confirmer, annuler, exporter et documenter des factures e-MECeF.

## Fonctionnalites v3

- Client API aligne sur les endpoints officiels DGI : groupes de taxe, types de facture, types de paiement, creation, lecture, confirmation et annulation de facture.
- Types de facture `FV`, `FA`, `EV`, `EA`, avec `reference` obligatoire pour les avoirs `FA/EA`.
- Paiements officiels : `ESPECES`, `VIREMENT`, `CARTEBANCAIRE`, `MOBILEMONEY`, `CHEQUES`, `CREDIT`, `AUTRE`.
- Articles avec groupes de taxe `A-F`, taxe specifique `taxSpecific`, prix original `originalPrice` et modification `priceModification`.
- Validation locale : IFU a 13 chiffres, paiements egaux au total, coherence des avoirs et erreurs API `errorCode/errorDesc`.
- Support multi-IFU via `IfuResolverInterface`.
- Jobs Laravel pour creation, confirmation et expiration locale des factures en attente apres 2 minutes.
- Export STAT CSV et generation du dossier d'auto-declaration e-MECeF.
- Dashboard avec KPI, filtres, recherche, mode sombre et polling configurable.

## Installation

```bash
composer require banelsems/lara-sgmef-qr

php artisan vendor:publish --tag=lara-sgmef-qr-config
php artisan vendor:publish --tag=lara-sgmef-qr-migrations
php artisan migrate
```

Publier les vues est optionnel :

```bash
php artisan vendor:publish --tag=lara-sgmef-qr-views
```

## Configuration

Ajoutez les variables utiles dans `.env` :

```env
SGMEF_API_URL=https://developper.impots.bj/sygmef-emcf/api
SGMEF_TOKEN=your_jwt_token_here
SGMEF_DEFAULT_IFU=1234567890123

SGMEF_DEFAULT_OPERATOR_NAME="Operateur Principal"
SGMEF_DEFAULT_OPERATOR_ID=1

SGMEF_HTTP_TIMEOUT=30
SGMEF_CONNECT_TIMEOUT=10
SGMEF_VERIFY_SSL=true

SGMEF_WEB_INTERFACE_ENABLED=true
SGMEF_ROUTE_PREFIX=sgmef
SGMEF_POLLING_SECONDS=30

SGMEF_QUEUE_ENABLED=false
SGMEF_QUEUE_CONNECTION=sync

SGMEF_COMPANY_NAME="Votre entreprise"
SGMEF_RCCM="RCCM/XXXX/XX/XXXX"
SGMEF_PHONE="XX XX XX XX"
SGMEF_EMAIL="contact@example.com"
```

En production, protegez toujours l'interface web :

```php
// config/lara_sgmef_qr.php
'web_interface' => [
    'enabled' => env('SGMEF_WEB_INTERFACE_ENABLED', true),
    'middleware' => ['web', 'auth'],
    'route_prefix' => env('SGMEF_ROUTE_PREFIX', 'sgmef'),
    'polling_seconds' => (int) env('SGMEF_POLLING_SECONDS', 30),
],
```

L'interface est disponible sur `/sgmef` par defaut :

- `/sgmef` : dashboard
- `/sgmef/invoices` : liste et filtres
- `/sgmef/invoices/create` : creation de facture
- `/sgmef/config` : configuration

## Utilisation PHP

### Creer et confirmer une facture de vente

```php
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;

$manager = app(InvoiceManagerInterface::class);

$data = InvoiceRequestDto::fromArray([
    'ifu' => config('lara_sgmef_qr.default_ifu'),
    'type' => 'FV',
    'client' => [
        'ifu' => '9876543210123',
        'name' => 'Client Exemple',
        'contact' => '+229 01 00 00 00',
        'address' => 'Cotonou',
    ],
    'operator' => [
        'id' => '1',
        'name' => 'Operateur Principal',
    ],
    'items' => [
        [
            'name' => 'Prestation de service',
            'price' => 10000,
            'quantity' => 1,
            'taxGroup' => 'B',
            'code' => 'SERV-001',
        ],
    ],
    'payment' => [
        ['name' => 'ESPECES', 'amount' => 10000],
    ],
]);

$invoice = $manager->createInvoice($data);
$confirmedInvoice = $manager->confirmInvoice($invoice->uid);

echo $confirmedInvoice->mecf_code;
echo $confirmedInvoice->qr_code_data;
```

### Multi-paiement

Le total des paiements doit etre egal au total des lignes.

```php
$data = InvoiceRequestDto::fromArray([
    'ifu' => '1234567890123',
    'type' => 'FV',
    'client' => ['name' => 'Client multi-paiement'],
    'operator' => ['id' => '1', 'name' => 'Caisse 1'],
    'items' => [
        ['name' => 'Article A', 'price' => 7000, 'quantity' => 1, 'taxGroup' => 'B'],
        ['name' => 'Article B', 'price' => 3000, 'quantity' => 1, 'taxGroup' => 'A'],
    ],
    'payment' => [
        ['name' => 'ESPECES', 'amount' => 4000],
        ['name' => 'MOBILEMONEY', 'amount' => 6000],
    ],
]);
```

Types acceptes : `ESPECES`, `VIREMENT`, `CARTEBANCAIRE`, `MOBILEMONEY`, `CHEQUES`, `CREDIT`, `AUTRE`.

### Avoir FA/EA avec reference obligatoire

Pour une facture d'avoir, `reference` doit contenir l'UID de la facture originale.

```php
$refundData = InvoiceRequestDto::fromArray([
    'ifu' => '1234567890123',
    'type' => 'FA',
    'reference' => $originalInvoice->uid,
    'client' => ['name' => 'Client Exemple'],
    'operator' => ['id' => '1', 'name' => 'Operateur Principal'],
    'items' => [
        ['name' => 'Avoir partiel', 'price' => 2500, 'quantity' => 1, 'taxGroup' => 'B'],
    ],
    'payment' => [
        ['name' => 'ESPECES', 'amount' => 2500],
    ],
]);

$refund = $manager->createInvoice($refundData);
$manager->confirmInvoice($refund->uid);
```

### Taxe specifique et modification de prix

```php
$data = InvoiceRequestDto::fromArray([
    'ifu' => '1234567890123',
    'type' => 'FV',
    'client' => ['name' => 'Client Exemple'],
    'operator' => ['id' => '1', 'name' => 'Operateur Principal'],
    'items' => [
        [
            'name' => 'Produit avec remise',
            'price' => 9000,
            'quantity' => 1,
            'taxGroup' => 'B',
            'taxSpecific' => 150,
            'originalPrice' => 10000,
            'priceModification' => 'REMISE_COMMERCIALE',
        ],
    ],
    'payment' => [
        ['name' => 'CARTEBANCAIRE', 'amount' => 9000],
    ],
]);
```

### Multi-IFU

Par defaut, le package lit `SGMEF_DEFAULT_IFU`. Pour une application multi-etablissements, fournissez votre propre resolver :

```php
namespace App\Sgmef;

use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;

class TenantIfuResolver implements IfuResolverInterface
{
    public function resolve(?string $context = null): string
    {
        return tenant($context)->ifu;
    }
}
```

Puis liez-le dans un service provider de l'application :

```php
use App\Sgmef\TenantIfuResolver;
use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;

$this->app->bind(IfuResolverInterface::class, TenantIfuResolver::class);
```

Vous pouvez ensuite injecter le resolver pour construire vos DTOs :

```php
$ifu = app(IfuResolverInterface::class)->resolve('boutique-cotonou');
```

### Jobs Laravel

```php
use Banelsems\LaraSgmefQr\Jobs\CreateInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\ConfirmInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CleanupExpiredPendingInvoicesJob;

CreateInvoiceJob::dispatch($data)->onQueue('sgmef');
ConfirmInvoiceJob::dispatch($invoice->uid)->onQueue('sgmef');
CleanupExpiredPendingInvoicesJob::dispatch()->onQueue('sgmef');
```

Planification recommandee du nettoyage :

```php
// app/Console/Kernel.php
use Banelsems\LaraSgmefQr\Jobs\CleanupExpiredPendingInvoicesJob;

$schedule->job(new CleanupExpiredPendingInvoicesJob())->everyMinute();
```

## Commandes

Generer un export STAT CSV :

```bash
php artisan emecef:export-stat --from=2026-07-01 --to=2026-07-31
php artisan emecef:export-stat --from=2026-07-01 --to=2026-07-31 --path=storage/app/stat_juillet.csv
```

Generer le dossier d'auto-declaration e-MECeF :

```bash
php artisan emecef:generate-declaration
```

La configuration `emecef_test_cases` contient 20 cas de test DGI automatisables. Les appels reseau restent mockables dans les tests; les appels reels exigent un token et un IFU valides fournis par l'application hote.

## Services utiles

```php
use Banelsems\LaraSgmefQr\Services\TaxCalculatorService;
use Banelsems\LaraSgmefQr\Services\QrCodeService;
use Banelsems\LaraSgmefQr\Services\StatExportService;

$taxes = app(TaxCalculatorService::class)->calculateInvoice($data->items);
$verificationUrl = app(QrCodeService::class)->verificationUrl($confirmedInvoice);
$csvPath = app(StatExportService::class)->exportCsv(now()->startOfMonth(), now()->endOfMonth());
```

## Cycle de vie et statuts

Le modele `Banelsems\LaraSgmefQr\Models\Invoice` stocke les factures locales et l'audit API.

Statuts disponibles :

```php
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;

InvoiceStatusEnum::PENDING;
InvoiceStatusEnum::CONFIRMED;
InvoiceStatusEnum::CANCELLED;
InvoiceStatusEnum::ERROR;
```

Champs importants :

- `uid` : UID retourne par l'API e-MECeF.
- `ifu` et `customer_ifu` : IFU emetteur/client.
- `type` : `FV`, `FA`, `EV`, `EA`.
- `reference` : facture originale pour `FA/EA`.
- `security_elements`, `qr_code_data`, `mecf_code` : elements de confirmation.
- `error_code`, `error_description` : erreurs locales ou API.
- `expires_at` : date limite locale de confirmation.

## Gestion des erreurs

```php
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;

try {
    $invoice = $manager->createInvoice($data);
} catch (InvoiceException $e) {
    report($e);
} catch (SgmefApiException $e) {
    report($e);
}
```

Les logs du client API evitent de persister les tokens et payloads complets. En production, gardez les logs applicatifs dans un canal protege.

## Tests et qualite

```bash
composer validate --no-check-publish
composer run-script cs-check
php vendor/bin/phpunit
composer run-script analyse
```

Remarques :

- PHPUnit peut signaler `No code coverage driver available` si Xdebug/PCOV n'est pas installe.
- PHPStan est conserve dans le workflow, mais certaines bases Laravel/Eloquent peuvent necessiter des annotations ou une configuration dediee.

## Compatibilite

- PHP `^8.1|^8.2|^8.3`
- Laravel `^10.0|^11.0|^12.0`
- Extensions PHP : `curl`, `json`
- Base de donnees compatible Laravel migrations

## Ressources

- Repository : <https://github.com/Banelsems/laraSgmefQR>
- Issues : <https://github.com/Banelsems/laraSgmefQR/issues>
- API developpeur DGI : <https://developper.impots.bj/sygmef-emcf/swagger/v1/swagger.json>
- Portail e-MECeF : <https://e-mecef.impots.bj/>

## Licence

MIT. Voir [LICENSE](LICENSE).
