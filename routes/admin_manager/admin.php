<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FilialController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\Api\ClientController;
use App\Http\Controllers\Admin\Api\DocumentController as ApiDocumentController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PricingTariffController;




Route::name('admin.')->prefix('admin')->group(function(){
    Route::prefix('api')->group(function () {
        Route::post('/document', [ApiDocumentController::class, 'store'])->name('api.document.store');
        Route::get('/clients/search', [ClientController::class, 'search'])->name('api.clients.search');
        Route::apiResource('/clients', ClientController::class);
        Route::post('/document/save-all', [ApiDocumentController::class, 'storeAll'])->name('api.document.save_all');
        Route::get('/get-addons/{type}/{id}', [ApiDocumentController::class, 'getAddons'])->name('api.addons.index');
    });

    Route::get('/index', [AdminController::class, 'index'])->name('index');

    Route::get('/create', [AdminController::class, 'create'])->name('create');

    Route::post('/store', [AdminController::class, 'store'])->name('store');

    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('edit');

    Route::put('/update/{id}', [AdminController::class, 'update'])->name('update');

    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('destroy');

    Route::resource('/filial',FilialController::class)->except(['show']);

    Route::resource('/partners', PartnerController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->names('partners');

    Route::resource('/pricing', PricingTariffController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->parameters(['pricing' => 'pricing'])
        ->names('pricing');

    Route::resource('/expense',ExpenseController::class);
    Route::post('/expense/{expense}/approve', [ExpenseController::class, 'approve'])->name('expense.approve');

    Route::get('/expense_static', [ExpenseController::class, 'statistika'])->name('statistika');

    Route::resource('/document',DocumentController::class);

    Route::get('/document_static', [DocumentController::class, 'statistika'])->name('document.statistika');

    Route::post('/payment/add', [DocumentController::class, 'add_payment'])->name('add_payment');
    Route::get('/payments/{document}', [DocumentController::class, 'paymentHistory'])->name('payments');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

});

