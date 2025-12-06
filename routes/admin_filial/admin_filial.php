<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminClientDocumentController;
use App\Http\Controllers\Admin\AdminFilialController;
use App\Http\Controllers\Admin\AdminFilialDocumentController;
use App\Http\Controllers\Admin\ExpenseAdminController;

Route::name('admin_filial.')
    ->prefix('admin_filial')
    ->group(function () {

        // Filial index
        Route::get('/index', [AdminFilialController::class, 'index'])->name('index');

        // Filial documents resource
        Route::resource('document', AdminFilialDocumentController::class);

        // Clients
        Route::get('/clients/search', [AdminClientDocumentController::class, 'search'])->name('clients.search');
        Route::get('/clients/map', [AdminClientDocumentController::class, 'mapData'])->name('clients.map');

        // Service addons
        Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons'])->name('get_service_addons');

        // Doc summary
        Route::get('/doc_summary', [AdminFilialDocumentController::class, 'doc_summary'])->name('doc_summary');

        // Payments
        Route::post('/payment/add', [AdminFilialDocumentController::class, 'add_payment'])->name('add_payment');
        Route::get('/payments/{document}', [AdminFilialDocumentController::class, 'paymentHistory']);

        // Expense
        Route::resource('expense_admin', ExpenseAdminController::class);
        Route::get('/expense/statistika', [ExpenseAdminController::class, 'statistika'])->name('expense.statistika');

        // Complete document
        Route::get('/document/complete/{document}', [AdminFilialDocumentController::class, 'completeDocument'])->name('document.complete');
    });
