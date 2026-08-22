# Upgrade Guide

## Lot 8-10 : API compliance, UI/UX, finalisation

### ClientDto constructor parameter order

`ClientDto` constructor parameters have been reordered to fix a PHP 8.1
deprecation (required parameter after optional). `$name` is now the first
parameter:

```php
// Before
new ClientDto(ifu: null, name: 'John Doe');

// After
new ClientDto('John Doe', ifu: null);
```

If you use named arguments (`ClientDto::from()` or `new ClientDto(name: '...')`),
no change is needed.

### New configuration option: rate_limit_per_minute

A new `rate_limit_per_minute` config key has been added (default: 60).
Add it to your `config/lara_sgmef_qr.php`:

```php
'security' => [
    'rate_limit_per_minute' => env('SGMEF_RATE_LIMIT_PER_MINUTE', 60),
],
```

Or set the environment variable:

```env
SGMEF_RATE_LIMIT_PER_MINUTE=60
```

Set to `0` to disable rate limiting.

### New route: GET /invoices/{uid}/verify

A new verification endpoint is available. No action required if you use
the default route file. If you publish routes manually, add:

```php
Route::get('/invoices/{uid}/verify', [InvoiceController::class, 'verify'])
    ->name('invoices.verify');
```

### New events

- `SgmefAuthenticationAlert`: Dispatched when the API returns 401 or 403.
  Listen to it for security monitoring or alerting.

## Lot 7 : Queue, Jobs, expiration et résilience asynchrone

### New InvoiceStatusEnum::EXPIRED

A new `expired` status has been added to `InvoiceStatusEnum`. Run the migration:

```bash
php artisan migrate
```

The migration is additive (expand-and-contract). Existing statuses are preserved.
If you have hardcoded status lists in your application, add `expired`:

```php
// Before
['pending', 'confirmed', 'cancelled', 'error']

// After
['pending', 'confirmed', 'cancelled', 'expired', 'error']
```

### QrCodeService::png() behavior change

`png()` now produces real PNG output (requires `ext-gd`). If `ext-gd` is not
available, a `RuntimeException` is thrown. Previously, `png()` silently
returned SVG content.

**Action required**: If you were calling `png()` without `ext-gd`, either:
1. Install `ext-gd`, or
2. Switch to `svg()` (vector format, no dependency).

### CreateInvoiceJob payload change

`CreateInvoiceJob` constructor now accepts `array` instead of `InvoiceRequestDto`:

```php
// Before
CreateInvoiceJob::dispatch($dto);

// After
CreateInvoiceJob::dispatch($dto->toArray());
```

Or use the new `InvoiceQueueDispatcher` which handles this automatically.

### New config keys

Add these to your `.env` if you want non-default values:

```env
SGMEF_QUEUE=sgmef
SGMEF_JOB_TIMEOUT=60
SGMEF_SYNC_TRIES=3
SGMEF_CLEANUP_ENABLED=true
SGMEF_CLEANUP_FREQUENCY=everyMinute
SGMEF_CLEANUP_LIMIT=500
```

### Automatic scheduling

The `sgmef:cleanup-expired` command is now automatically scheduled by the
ServiceProvider when `SGMEF_CLEANUP_ENABLED=true`. If you previously had
manual scheduling in `app/Console/Kernel.php`, you can remove it.

## From v3.x to v4.0.0

v4.0.0 introduces significant breaking changes to improve robustness,
architecture, and safety. This guide covers all changes that require
action when upgrading.

### Breaking Changes

#### TaxCalculationStrategyInterface (Lot 3)

The `calculate()` method now returns `int` (minor units) instead of `float`:

```php
// Before (v3)
/** @return array{base: float, tax: float, aib: float, total: float} */
public function calculate(InvoiceItemDto $item): array;

// After (v4)
/** @return array{base: int, tax: int, aib: int, total: int} */
public function calculate(InvoiceItemDto $item): array;
```

If you have custom tax strategies, update them to return `int` values
(use the `Money` value object for calculations).

#### ClientDto::$name (Lot 3)

`ClientDto::$name` is now `string` (non-nullable). Any code passing `null`
for the client name must be updated.

#### InvoiceManager constructor (Lot 4)

The constructor now requires an `InvoiceRepositoryInterface` as second
parameter:

```php
// Before (v3)
new InvoiceManager($apiClient);

// After (v4)
new InvoiceManager($apiClient, $repository);
```

If you resolve `InvoiceManagerInterface` from the container, this is
handled automatically by the ServiceProvider.

#### SgmefApiClientInterface::getStatus() (Lot 1)

Now returns `ApiStatusDto` instead of `array`. Update any code that
accesses the return value as an array.

#### SgmefApiClientInterface::confirmInvoice() (Lot 1)

The `$withQrCode` parameter is deprecated and has no effect. It will be
removed in v5.0.0.

### Deprecated APIs (Lot 5)

The following mutable APIs are deprecated since v4.0.0 and will be removed
in v5.0.0:

- `SgmefApiClientInterface::setBaseUrl()` — Use `SgmefApiClientFactory::make()`
  with an immutable `SgmefConnectionConfig` instead.
- `SgmefApiClientInterface::setCredentials()` — Same as above.
- `ConfigWriterInterface` — Runtime config file writing is discouraged.
  Prefer environment variables (`.env`).

### New Infrastructure (Lot 5)

#### SgmefConnectionConfig

Immutable configuration object for API connections:

```php
use Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig;

$config = new SgmefConnectionConfig(
    baseUrl: 'https://api.example.com',
    token: 'your-token',
    timeout: 30,
    connectTimeout: 10,
    verifySsl: true
);
```

#### SgmefApiClientFactory

Creates isolated API client instances without mutating shared singletons:

```php
$factory = app(SgmefApiClientFactory::class);
$client = $factory->make($config);
```

#### Exception Hierarchy

All API exceptions now extend `SgmefApiException` and are catchable via
the base class. Specific subclasses:

- `SgmefAuthenticationException` (401/403)
- `SgmefValidationException` (400/422)
- `SgmefNotFoundException` (404)
- `SgmefRateLimitException` (429, with `retryAfter()`)
- `SgmefTimeoutException`
- `SgmefNetworkException`
- `SgmefServerException` (5xx)

#### ReferenceDataProvider

Reference data (tax groups, payment types, invoice types) is now loaded
via `ReferenceDataProviderInterface` with caching and explicit fallbacks.
The `InvoiceController::create()` method no longer contains hardcoded
fallbacks.
