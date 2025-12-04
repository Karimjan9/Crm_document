<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminClientDocumentController;
use App\Http\Controllers\Admin\AdminFilialController;
use App\Http\Controllers\Admin\AdminFilialDocumentController;
use App\Http\Controllers\Admin\ExpenseAdminController;

Route::name('admin_filial.')
    ->prefix('admin_filial')
    ->group(function () {

        Route::get('/index', [AdminFilialController::class, 'index'])->name('index');
        Route::resource('document', AdminFilialDocumentController::class);

        Route::get('/clients/search', [AdminClientDocumentController::class, 'search'])->name('clients.search');
        Route::get('/clients/map', [AdminClientDocumentController::class, 'mapData'])->name('clients.map');

        Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons'])->name('get_service_addons');

        Route::get('/doc_summary', [AdminFilialDocumentController::class, 'doc_summary'])->name('doc_summary');
        Route::post('/payment/add', [AdminFilialDocumentController::class, 'add_payment'])->name('add_payment');
        Route::get('/payments/{document}', [AdminFilialDocumentController::class, 'paymentHistory']);

        Route::resource('expense_admin', ExpenseAdminController::class);
        Route::get('/expense/statistika', [ExpenseAdminController::class, 'statistika'])->name('expense.statistika');
    });
