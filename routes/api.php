<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\TokenController;
use App\Http\Controllers\Api\V1\PartnerOrderController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'abilities:profile:read'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/payments/{provider}', [PaymentWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('payment.webhook');

Route::prefix('v1')->group(function () {
    Route::post('/auth/token', [TokenController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::middleware([
        'auth:sanctum',
        'role:admin_manager|super_admin|employee|admin_filial',
    ])->group(function () {
        Route::get('/user', [TokenController::class, 'user'])
            ->middleware('abilities:profile:read');
        Route::delete('/auth/token', [TokenController::class, 'destroy'])
            ->middleware('abilities:token:revoke');

        Route::get('/clients/search', [ClientController::class, 'search'])
            ->middleware('abilities:clients:read');
        Route::get('/clients', [ClientController::class, 'index'])
            ->middleware('abilities:clients:read')
            ->name('clients.index');
        Route::get('/clients/{client}', [ClientController::class, 'show'])
            ->middleware('abilities:clients:read')
            ->name('clients.show');
        Route::post('/clients', [ClientController::class, 'store'])
            ->middleware('abilities:clients:write')
            ->name('clients.store');
        Route::put('/clients/{client}', [ClientController::class, 'update'])
            ->middleware('abilities:clients:write')
            ->name('clients.update');
        Route::patch('/clients/{client}', [ClientController::class, 'update'])
            ->middleware('abilities:clients:write')
            ->name('clients.patch');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
            ->middleware('abilities:clients:write')
            ->name('clients.destroy');

        Route::get('/document-addons/{type}/{id}', [DocumentController::class, 'getAddons'])
            ->middleware('abilities:documents:read');
        Route::get('/document-files/{documentFile}', [DocumentFileController::class, 'show'])
            ->middleware('abilities:files:read')
            ->name('api.v1.document-files.show');
        Route::post('/documents/batch', [DocumentController::class, 'storeAll'])
            ->middleware('abilities:documents:write');
        Route::apiResource('/documents', DocumentController::class)
            ->only(['show'])
            ->middleware('abilities:documents:read');
        Route::apiResource('/documents', DocumentController::class)
            ->only(['store', 'update'])
            ->middleware('abilities:documents:write');
    });

    Route::prefix('partner')->middleware(['partner.api', 'throttle:api'])->group(function (): void {
        Route::get('/catalog', [PartnerOrderController::class, 'catalog'])->name('api.v1.partner.catalog');
        Route::get('/orders', [PartnerOrderController::class, 'index'])->name('api.v1.partner.orders.index');
        Route::post('/orders', [PartnerOrderController::class, 'store'])->name('api.v1.partner.orders.store');
        Route::get('/orders/{order}', [PartnerOrderController::class, 'show'])->name('api.v1.partner.orders.show');
        Route::get('/stats', [PartnerOrderController::class, 'stats'])->name('api.v1.partner.stats');
        Route::get('/invoices', [PartnerOrderController::class, 'invoices'])->name('api.v1.partner.invoices');
    });
});
