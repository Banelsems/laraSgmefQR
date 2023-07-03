<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class LaraSgmefQRServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('laraSgmefQR', function () {
            return new LaraSgmefQR();
        });

        // Register the view composer
        View::composer('invoiceNormalize', function ($view) {
            $view->with('invoiceNormalize', $this->app->make('laraSgmefQR')->getInvoiceNormalize());
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laraSgmefQR.php' => config_path('laraSgmefQR.php'),
            __DIR__ . '/../resources/views/laraSgmefQR/' => resource_path('views/invoiceNormalize.blade.php'),
        ]);
    }

}
