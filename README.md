# LaraSgmefQR

Package Laravel pour intégrer l'API e-MECeF / SyGM-eMCF de la Direction Générale des Impôts du Bénin.

[![Latest Version](https://img.shields.io/github/v/release/banelsems/laraSgmefQR)](https://github.com/Banelsems/laraSgmefQR/releases)
[![License](https://img.shields.io/github/license/banelsems/laraSgmefQR)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1%20%7C%20%5E8.2%20%7C%20%5E8.3%20%7C%20%5E8.4-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-red)](https://laravel.com)
[![Tests](https://img.shields.io/badge/tests-368%20passing-brightgreen)](https://github.com/Banelsems/laraSgmefQR)
[![PHPStan](https://img.shields.io/badge/phpstan-level%208-0%20errors-brightgreen)](https://phpstan.org)

LaraSgmefQR v4.0.0 fournit un client API, des DTOs, une interface web, des jobs Laravel et des outils de conformité pour créer, confirmer, annuler, vérifier, exporter et documenter des factures e-MECeF.

## Fonctionnalites v4

- Client API aligne sur les endpoints officiels DGI : groupes de taxe, types de facture, types de paiement, creation, lecture, confirmation et annulation de facture.
- **Annulation alternative** : `POST /invoice/cancel` (body-based) en plus du `PUT /cancel` existant.
- **VerificationService** : verification post-confirmation structuree (8 controles) avec URL de verification DGI.
- **SgmefSecurityGuard** : pre-validation JWT + rate limiting outgoing (configurable via `SGMEF_RATE_LIMIT_PER_MINUTE`).
- Types de facture `FV`, `FA`, `EV`, `EA`, avec `reference` obligatoire pour les avoirs `FA/EA`.
- Paiements officiels : `ESPECES`, `VIREMENT`, `CARTEBANCAIRE`, `MOBILEMONEY`, `CHEQUES`, `CREDIT`, `AUTRE`.
- Articles avec groupes de taxe `A-F`, taxe specifique `taxSpecific`, prix original `originalPrice` et modification `priceModification`.
- Validation locale : IFU a 13 chiffres, paiements egaux au total, coherence des avoirs et erreurs API `errorCode/errorDesc`.
- Support multi-IFU via `IfuResolverInterface`.
- Jobs Laravel pour creation, confirmation et expiration locale des factures en attente apres 2 minutes.
- Export STAT CSV et generation du dossier d'auto-declaration e-MECeF.
- **Dashboard v4** : KPIs cliquables, graphique 30 jours, Top 5 clients, alertes visuelles (factures expirees, taux d'erreur API).
- **Interface responsive** : dark mode complet (auto-detection + toggle), sidebar mobile, notifications temps reel.
- **Liste factures** : tri par colonnes cliquables, filtres persistants dans l'URL, pagination avec query string.
- **CI/CD** : GitHub Actions (PHP 8.2/8.3/8.4 x Laravel 10/11/12, PHPStan level 8, PHP-CS-Fixer).

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

SGMEF_RATE_LIMIT_PER_MINUTE=60

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
- `/sgmef/invoices/{uid}/verify` : verification post-confirmation
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

### Jobs Laravel et API async

Le package fournit une API async additive via `InvoiceQueueDispatcher` qui
dispatche des jobs sur la queue configuree. Les jobs appellent les memes
services metier (`InvoiceManager`) que l'API synchrone — aucune logique
metier n'est dupliquee.

#### Configuration

```env
SGMEF_QUEUE_ENABLED=true
SGMEF_QUEUE_CONNECTION=redis
SGMEF_QUEUE=sgmef
SGMEF_JOB_TIMEOUT=60
SGMEF_SYNC_TRIES=3
SGMEF_CLEANUP_ENABLED=true
SGMEF_CLEANUP_FREQUENCY=everyMinute
SGMEF_CLEANUP_LIMIT=500
```

#### Dispatcher async (recommande)

```php
use Banelsems\LaraSgmefQr\Services\InvoiceQueueDispatcher;

$dispatcher = app(InvoiceQueueDispatcher::class);

// Creation (payload array serialisable, IFU fige au dispatch)
$dispatcher->create($dto);

// Confirmation / annulation / synchronisation
$dispatcher->confirmByUid($invoice->uid);
$dispatcher->cancelByUid($invoice->uid);
$dispatcher->syncByUid($invoice->uid);
```

Tous les dispatchs utilisent `afterCommit()` pour ne s'envoyer qu'apres
commit de la transaction DB courante.

#### Jobs directs (bas niveau)

```php
use Banelsems\LaraSgmefQr\Jobs\CreateInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\ConfirmInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\SyncInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CancelInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CleanupExpiredPendingInvoicesJob;

// Payload = DTO->toArray() (array serialisable)
CreateInvoiceJob::dispatch($dto->toArray());
ConfirmInvoiceJob::dispatch($invoice->uid);
SyncInvoiceJob::dispatch($invoice->uid);
CancelInvoiceJob::dispatch($invoice->uid);
```

#### Securite des retries

| Job | tries | Raison |
|-----|-------|--------|
| CreateInvoiceJob | 1 | POST /invoice non-idempotent |
| ConfirmInvoiceJob | 1 | PUT /confirm non-idempotent |
| CancelInvoiceJob | 1 | PUT /cancel non-idempotent |
| SyncInvoiceJob | 3 | GET /invoice safe-to-retry |
| CleanupExpiredPendingInvoicesJob | 1 | Idempotent mais non-retryable |

Les jobs Create/Confirm/Cancel sont `ShouldBeUnique` pour eviter deux
operations concurrentes sur la meme facture.

#### Expiration des factures pending

La commande `sgmef:cleanup-expired` marque les factures pending dont
`expires_at` est depasse au statut `EXPIRED` (distinct d'`ERROR`).

```bash
php artisan sgmef:cleanup-expired
php artisan sgmef:cleanup-expired --dry-run
php artisan sgmef:cleanup-expired --limit=1000
```

Le scheduling est automatiquement enregistre par le ServiceProvider si
`SGMEF_CLEANUP_ENABLED=true` (defaut). L'event `InvoiceExpired` est
dispatche pour chaque facture expiree.

#### QR code PNG

`QrCodeService::png()` produit desormais de vrais PNG (signature `\x89PNG`).
Necessite `ext-gd`. Sans `ext-gd`, une `RuntimeException` explicite est levee
(png() ne retourne jamais silencieusement du SVG). Utilisez `svg()` pour un
format vectoriel sans dependance.

### Verification post-confirmation

Apres confirmation, verifiez l'integrite d'une facture via le `VerificationService` :

```php
use Banelsems\LaraSgmefQr\Services\VerificationService;

$verification = app(VerificationService::class)->verify($confirmedInvoice);

if ($verification->valid) {
    echo "Facture conforme. URL: " . $verification->verificationUrl;
} else {
    foreach ($verification->issues as $issue) {
        echo "Probleme: $issue\n";
    }
}
```

Le service verifie 8 controles : statut confirmed, UID present, QR code present
et valide, code MECeF present, date/heure, counters, NIM.

### Annulation alternative (POST)

En plus du `PUT /cancel` standard, une annulation alternative via `POST /invoice/cancel` est disponible :

```php
$manager->cancelInvoiceByUid($invoice->uid);  // PUT /cancel (standard)
$manager->cancelInvoiceByPost($invoice->uid); // POST /invoice/cancel (alternative)
```

### Securite : rate limiting et pre-validation JWT

Le `SgmefSecurityGuard` valide le token JWT avant chaque appel API et limite
le nombre de requetes sortantes par minute :

```env
SGMEF_RATE_LIMIT_PER_MINUTE=60  # 0 pour desactiver
```

L'event `SgmefAuthenticationAlert` est dispatche sur les reponses 401/403.
Ecoutez-le pour integrer un systeme d'alerte de securite :

```php
use Banelsems\LaraSgmefQr\Events\SgmefAuthenticationAlert;

Event::listen(SgmefAuthenticationAlert::class, function ($event) {
    // $event->httpStatus, $event->endpoint, $event->timestamp
    Log::warning("Authentification DGI echouee", (array) $event);
});
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
InvoiceStatusEnum::EXPIRED;
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

Resultats actuels :

- **368 tests, 665 assertions** — tous passants.
- **PHPStan level 8** — 0 erreurs.
- **composer validate** — valide.
- **CI/CD GitHub Actions** — PHP 8.2/8.3/8.4 x Laravel 10/11/12.

Remarques :

- PHPUnit peut signaler `No code coverage driver available` si Xdebug/PCOV n'est pas installe.
- Les annotations `@method` sur le modele `Invoice` documentent les methodes magiques Eloquent pour PHPStan.

## Compatibilite

- PHP `^8.1|^8.2|^8.3|^8.4`
- Laravel `^10.0|^11.0|^12.0|^13.0`
- Extensions PHP : `curl`, `json`, `gd` (optionnel, pour PNG QR codes)
- Base de donnees compatible Laravel migrations

## Ressources

- Repository : <https://github.com/Banelsems/laraSgmefQR>
- Issues : <https://github.com/Banelsems/laraSgmefQR/issues>
- API developpeur DGI : <https://developper.impots.bj/sygmef-emcf/swagger/v1/swagger.json>
- Portail e-MECeF : <https://e-mecef.impots.bj/>

## Licence

MIT. Voir [LICENSE](LICENSE).
