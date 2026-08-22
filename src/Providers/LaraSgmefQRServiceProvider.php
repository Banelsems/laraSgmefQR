<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Providers;

use Banelsems\LaraSgmefQr\Console\Commands\CheckConfigCommand;
use Banelsems\LaraSgmefQr\Console\Commands\CleanupExpiredInvoicesCommand;
use Banelsems\LaraSgmefQr\Console\Commands\EmecefGenerateDeclaration;
use Banelsems\LaraSgmefQr\Console\Commands\GenerateStatExportCommand;
use Banelsems\LaraSgmefQr\Contracts\ConfigWriterInterface;
use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProviderInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Http\Filters\InvoiceFilters;
use Banelsems\LaraSgmefQr\Infrastructure\FileConfigWriter;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefApiClientFactory;
use Banelsems\LaraSgmefQr\Repositories\EloquentInvoiceRepository;
use Banelsems\LaraSgmefQr\Services\DeclarationGeneratorService;
use Banelsems\LaraSgmefQr\Services\DefaultIfuResolver;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Banelsems\LaraSgmefQr\Services\InvoiceQueueDispatcher;
use Banelsems\LaraSgmefQr\Services\PackageConfigurationService;
use Banelsems\LaraSgmefQr\Services\QrCodeService;
use Banelsems\LaraSgmefQr\Services\ReferenceDataProvider;
use Banelsems\LaraSgmefQr\Services\SgmefApiClient;
use Banelsems\LaraSgmefQr\Services\SgmefConnectionTester;
use Banelsems\LaraSgmefQr\Services\StatExportService;
use Banelsems\LaraSgmefQr\Services\TaxCalculatorService;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;

/**
 * Service Provider pour le package LaraSgmefQR
 */
class LaraSgmefQRServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (class_exists(\Barryvdh\DomPDF\ServiceProvider::class)) {
            $this->app->register(\Barryvdh\DomPDF\ServiceProvider::class);
        }

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/lara_sgmef_qr.php',
            'lara_sgmef_qr'
        );

        $spatieDataConfig = __DIR__ . '/../../vendor/spatie/laravel-data/config/data.php';
        if (file_exists($spatieDataConfig)) {
            $this->mergeConfigFrom($spatieDataConfig, 'data');
        }

        // --- Infrastructure ---

        // SgmefApiClientFactory : singleton, stateless, construit des clients isolés
        $this->app->singleton(SgmefApiClientFactory::class, function ($app) {
            return new SgmefApiClientFactory($app->make(HttpClient::class));
        });

        // SgmefSecurityGuard : singleton, pré-validation token + rate limiting outgoing
        $this->app->singleton(\Banelsems\LaraSgmefQr\Infrastructure\SgmefSecurityGuard::class, function () {
            return new \Banelsems\LaraSgmefQr\Infrastructure\SgmefSecurityGuard(
                (int) config('lara_sgmef_qr.rate_limit_per_minute', 60)
            );
        });

        // SgmefApiClientInterface : singleton construit depuis la config fixe.
        // Le client est immutable après instanciation (setters dépréciés).
        // Safe en singleton car la config ne change pas pendant le cycle de vie.
        $this->app->singleton(SgmefApiClientInterface::class, function ($app) {
            return $app->make(SgmefApiClientFactory::class)->makeFromConfig();
        });

        // ConfigWriter : déprécié, conservé pour compatibilité. Singleton stateless.
        $this->app->singleton(ConfigWriterInterface::class, FileConfigWriter::class);

        // PackageConfigurationService : singleton, utilise ConfigWriter
        $this->app->singleton(PackageConfigurationService::class);

        // SgmefConnectionTester : singleton, utilise la factory pour des clients isolés
        $this->app->singleton(SgmefConnectionTester::class);

        // --- Domain services ---

        // IfuResolver : scoped (peut dépendre du contexte request)
        $this->app->scoped(IfuResolverInterface::class, DefaultIfuResolver::class);

        // TaxCalculatorService : singleton, stateless
        $this->app->singleton(TaxCalculatorService::class);

        // QrCodeService : singleton, stateless
        $this->app->singleton(QrCodeService::class);

        // VerificationService : singleton, dépend du QrCodeService
        $this->app->singleton(\Banelsems\LaraSgmefQr\Services\VerificationService::class);

        // StatExportService : singleton, stateless
        $this->app->singleton(StatExportService::class);

        // DeclarationGeneratorService : singleton, dépend du InvoiceManager
        $this->app->singleton(DeclarationGeneratorService::class, function ($app) {
            return new DeclarationGeneratorService(
                $app->make(InvoiceManagerInterface::class)
            );
        });

        // ReferenceDataProvider : singleton, cache + fallback
        $this->app->singleton(ReferenceDataProviderInterface::class, function ($app) {
            return new ReferenceDataProvider(
                $app->make(SgmefApiClientInterface::class),
                (int) config('lara_sgmef_qr.cache.ttl', 3600),
                (string) config('lara_sgmef_qr.cache.prefix', 'lara_sgmef_qr'),
                config('lara_sgmef_qr.reference_data.fallback', [
                    'tax_groups' => [
                        'A' => 0, 'B' => 18, 'C' => 19, 'D' => 23, 'E' => 0, 'F' => 0,
                    ],
                    'payment_types' => [
                        'ESPECES', 'VIREMENT', 'CARTEBANCAIRE',
                        'MOBILEMONEY', 'CHEQUES', 'CREDIT', 'AUTRE',
                    ],
                    'invoice_types' => ['FV', 'FA', 'EV', 'EA'],
                ])
            );
        });

        // --- Invoice domain ---

        // InvoiceRepository : singleton, stateless (Eloquent gère ses propres connexions)
        $this->app->singleton(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);

        // InvoiceManager : singleton, orchestrateur stateless
        $this->app->singleton(InvoiceManagerInterface::class, function ($app) {
            return new InvoiceManager(
                $app->make(SgmefApiClientInterface::class),
                $app->make(InvoiceRepositoryInterface::class)
            );
        });

        // InvoiceFilters : singleton, stateless
        $this->app->singleton(InvoiceFilters::class);

        // InvoiceQueueDispatcher : singleton, stateless wrapper pour les jobs async
        $this->app->singleton(InvoiceQueueDispatcher::class);

        // ExpirePendingInvoices : singleton, service de cleanup des factures expirées
        $this->app->singleton(\Banelsems\LaraSgmefQr\Services\ExpirePendingInvoices::class);

        // Alias for easier access
        $this->app->alias(SgmefApiClientInterface::class, 'sgmef.api');
        $this->app->alias(InvoiceManagerInterface::class, 'sgmef.invoices');

        // Register helper methods
        $this->registerHelpers();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EmecefGenerateDeclaration::class,
                CheckConfigCommand::class,
                GenerateStatExportCommand::class,
                CleanupExpiredInvoicesCommand::class,
            ]);
        }

        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../config/lara_sgmef_qr.php' => config_path('lara_sgmef_qr.php'),
            __DIR__ . '/../../config/emecef_test_cases.php' => config_path('emecef_test_cases.php'),
        ], 'lara-sgmef-qr-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'lara-sgmef-qr-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views/' => resource_path('views/vendor/lara-sgmef-qr'),
        ], 'lara-sgmef-qr-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'lara-sgmef-qr');

        // Register routes if web interface is enabled
        if (config('lara_sgmef_qr.web_interface.enabled', true)) {
            $this->registerRoutes();
        }

        // Schedule cleanup of expired pending invoices
        $this->registerSchedule();
    }

    /**
     * Register scheduled tasks (cleanup expired invoices).
     *
     * Cross-version Laravel 10-13 : Schedule facade est stable depuis
     * Laravel 11. Pour Laravel 10, l'utilisateur peut ajouter la commande
     * manuellement dans app/Console/Kernel.php.
     */
    protected function registerSchedule(): void
    {
        if (! config('lara_sgmef_qr.queue.cleanup.enabled', true)) {
            return;
        }

        // Laravel 11+ : Schedule facade disponible dans le boot du provider.
        // Laravel 10 : Schedule facade existe aussi (depuis 10.x).
        if (class_exists(Schedule::class)) {
            $frequency = (string) config('lara_sgmef_qr.queue.cleanup.frequency', 'everyMinute');

            $command = Schedule::command('sgmef:cleanup-expired')
                ->withoutOverlapping()
                ->onOneServer();

            // Appliquer la fréquence configurée
            match ($frequency) {
                'everyMinute' => $command->everyMinute(),
                'everyFiveMinutes' => $command->everyFiveMinutes(),
                'everyTenMinutes' => $command->everyTenMinutes(),
                'everyFifteenMinutes' => $command->everyFifteenMinutes(),
                'everyThirtyMinutes' => $command->everyThirtyMinutes(),
                'hourly' => $command->hourly(),
                default => $command->everyMinute(),
            };
        }
    }

    /**
     * Register package routes
     */
    protected function registerRoutes(): void
    {
        $routePrefix = config('lara_sgmef_qr.web_interface.route_prefix', 'sgmef');
        $middleware = config('lara_sgmef_qr.web_interface.middleware', ['web']);

        Route::group([
            'prefix' => $routePrefix,
            'middleware' => $middleware,
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register helper methods
     */
    protected function registerHelpers(): void
    {
        // Register a helper to get default operator
        $this->app->bind('sgmef.default_operator', function () {
            return [
                'name' => config('lara_sgmef_qr.default_operator.name', 'Opérateur Principal'),
                'id' => config('lara_sgmef_qr.default_operator.id', '1'),
            ];
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SgmefApiClientInterface::class,
            InvoiceManagerInterface::class,
            InvoiceRepositoryInterface::class,
            IfuResolverInterface::class,
            ReferenceDataProviderInterface::class,
            ConfigWriterInterface::class,
            SgmefApiClientFactory::class,
            PackageConfigurationService::class,
            SgmefConnectionTester::class,
            InvoiceFilters::class,
            'sgmef.api',
            'sgmef.invoices',
            'sgmef.default_operator',
        ];
    }

    /**
     * Get default operator configuration
     */
    public static function getDefaultOperator(): array
    {
        return [
            'name' => config('lara_sgmef_qr.default_operator.name', 'Opérateur Principal'),
            'id' => config('lara_sgmef_qr.default_operator.id', '1'),
        ];
    }
}
