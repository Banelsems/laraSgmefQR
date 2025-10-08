<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LaraSgmefQR Web Routes
|--------------------------------------------------------------------------
|
| Routes pour l'interface web du package LaraSgmefQR
|
*/

// Dashboard principal
Route::get('/', 'DashboardController@index')->name('sgmef.dashboard');

// Configuration
Route::prefix('config')->name('sgmef.config.')->group(function () {
    Route::get('/', 'ConfigController@index')->name('index');
    Route::post('/', 'ConfigController@store')->name('store');
    Route::get('/test-connection', 'ConfigController@testConnection')->name('test');
});

// Gestion des factures
Route::prefix('invoices')->name('sgmef.invoices.')->group(function () {
    // Liste et création
    Route::get('/', 'InvoiceController@index')->name('index');
    Route::get('/create', 'InvoiceController@create')->name('create');
    Route::post('/', 'InvoiceController@store')->name('store');
    
    // Actions sur une facture spécifique
    Route::get('/{uid}', 'InvoiceController@show')->name('show');
    Route::post('/{uid}/confirm', 'InvoiceController@confirm')->name('confirm');
    Route::post('/{uid}/cancel', 'InvoiceController@cancel')->name('cancel');
    Route::get('/{uid}/sync', 'InvoiceController@sync')->name('sync');
    
    // Export et impression
    Route::get('/{uid}/pdf', 'InvoiceController@downloadPdf')->name('pdf');
    Route::get('/{uid}/print/{template?}', 'InvoiceController@print')->name('print');
    
    // Preview
    Route::post('/preview', 'InvoiceController@preview')->name('preview');
});

// API endpoints pour AJAX
Route::prefix('api')->name('sgmef.api.')->group(function () {
    Route::get('/tax-groups', 'ApiController@getTaxGroups')->name('tax-groups');
    Route::get('/payment-types', 'ApiController@getPaymentTypes')->name('payment-types');
    Route::get('/invoice-types', 'ApiController@getInvoiceTypes')->name('invoice-types');
    Route::post('/validate-ifu', 'ApiController@validateIfu')->name('validate-ifu');
});
