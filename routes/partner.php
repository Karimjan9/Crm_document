<?php

use App\Http\Controllers\Partner\PartnerCabinetController;
use Illuminate\Support\Facades\Route;

Route::name('partner.')->prefix('partner')->group(function (): void {
    Route::get('/', [PartnerCabinetController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [PartnerCabinetController::class, 'orders'])->name('orders.index');
    Route::get('/orders/create', [PartnerCabinetController::class, 'createOrder'])->name('orders.create');
    Route::post('/orders', [PartnerCabinetController::class, 'storeOrder'])->name('orders.store');
    Route::get('/orders/{order}', [PartnerCabinetController::class, 'showOrder'])->name('orders.show');
    Route::get('/invoices', [PartnerCabinetController::class, 'invoices'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [PartnerCabinetController::class, 'showInvoice'])->name('invoices.show');

    Route::post('/api-keys', [PartnerCabinetController::class, 'createApiKey'])->name('api-keys.store');
    Route::post('/api-keys/{apiKey}/revoke', [PartnerCabinetController::class, 'revokeApiKey'])->name('api-keys.revoke');
    Route::post('/branding', [PartnerCabinetController::class, 'updateBranding'])->name('branding.update');
});
