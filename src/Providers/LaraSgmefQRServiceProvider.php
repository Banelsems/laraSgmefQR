<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Providers;

use Banelsems\LaraSgmefQr\Commands\EmecefGenerateDeclaration;
use Banelsems\LaraSgmefQr\Console\Commands\CheckConfigCommand;
use Banelsems\LaraSgmefQr\Console\Commands\GenerateStatExportCommand;
use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Services\DeclarationGeneratorService;
use Banelsems\LaraSgmefQr\Services\DefaultIfuResolver;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Banelsems\LaraSgmefQr\Services\QrCodeService;
use Banelsems\LaraSgmefQr\Services\SgmefApiClient;
use Banelsems\LaraSgmefQr\Services\StatExportService;
use Banelsems\LaraSgmefQr\Services\TaxCalculatorService;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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

        // Register API Client as singleton
        $this->app->singleton(DeclarationGeneratorService::class, function ($app) {
            return new DeclarationGeneratorService(
                $app->make(InvoiceManagerInterface::class),
                $app->make(SgmefApiClientInterface::class)
            );
        });

        $this->app->scoped(IfuResolverInterface::class, DefaultIfuResolver::class);
        $this->app->singleton(TaxCalculatorService::class);
        $this->app->singleton(QrCodeService::class);
        $this->app->singleton(StatExportService::class);

        $this->app->singleton(SgmefApiClientInterface::class, function ($app) {
            return new SgmefApiClient(
                $app->make(HttpClient::class),
                config('lara_sgmef_qr.api_url'),
                config('lara_sgmef_qr.token'),
                config('lara_sgmef_qr.http_options', [])
            );
        });

        // Register Invoice Manager as singleton
        $this->app->singleton(InvoiceManagerInterface::class, function ($app) {
            return new InvoiceManager(
                $app->make(SgmefApiClientInterface::class)
            );
        });

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
            ]);
        }

        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../config/lara_sgmef_qr.php' => config_path('lara_sgmef_qr.php'),
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
            IfuResolverInterface::class,
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
