<?php

use Illuminate\Support\Facades\Route;
use Banelsems\LaraSgmefQr\Http\Controllers\DashboardController;
use Banelsems\LaraSgmefQr\Http\Controllers\ConfigController;
use Banelsems\LaraSgmefQr\Http\Controllers\InvoiceController;
use Banelsems\LaraSgmefQr\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| LaraSgmefQR Web Routes
|--------------------------------------------------------------------------
|
| Routes pour l'interface web du package LaraSgmefQR
|
*/

// Dashboard principal
Route::get('/', [DashboardController::class, 'index'])->name('sgmef.dashboard');

// Configuration
Route::prefix('config')->name('sgmef.config.')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/', [ConfigController::class, 'store'])->name('store');
        Route::get('/test-connection', [ConfigController::class, 'testConnection'])->name('test');
});

// Gestion des factures
Route::prefix('invoices')->name('sgmef.invoices.')->group(function () {
    // Liste et création
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
    
    // Actions sur une facture spécifique
        Route::get('/{uid}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{uid}/confirm', [InvoiceController::class, 'confirm'])->name('confirm');
        Route::post('/{uid}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
        Route::get('/{uid}/sync', [InvoiceController::class, 'sync'])->name('sync');
    
    // Export et impression
        Route::get('/{uid}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
        Route::get('/{uid}/print/{template?}', [InvoiceController::class, 'print'])->name('print');
    
    // Preview
        Route::post('/preview', [InvoiceController::class, 'preview'])->name('preview');
});

// API endpoints pour AJAX
Route::prefix('api')->name('sgmef.api.')->group(function () {
        Route::get('/tax-groups', [ApiController::class, 'getTaxGroups'])->name('tax-groups');
        Route::get('/payment-types', [ApiController::class, 'getPaymentTypes'])->name('payment-types');
        Route::get('/invoice-types', [ApiController::class, 'getInvoiceTypes'])->name('invoice-types');
        Route::post('/validate-ifu', [ApiController::class, 'validateIfu'])->name('validate-ifu');
});
