<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Employee\DocumentController as EmployeeDocumentController;
use App\Http\Controllers\Employee\ExpenseController as EmployeeExpenseController;
use App\Http\Controllers\Admin\AdminClientDocumentController;
use App\Http\Controllers\Admin\Api\ClientController;
use App\Http\Controllers\Admin\Api\DocumentController as ApiDocumentController;



Route::name('employee.')->prefix('employee')->group(function(){
    Route::prefix('api')->group(function () {
        Route::post('/document', [ApiDocumentController::class, 'store']);
        Route::get('/clients/search', [ClientController::class, 'search']);
        Route::apiResource('/clients', ClientController::class);
        Route::post('/document/save-all', [ApiDocumentController::class, 'storeAll']);
        Route::get('/get-addons/{type}/{id}', [ApiDocumentController::class, 'getAddons']);
    });

    Route::get('/', [EmployeeController::class, 'index'])->name('index');

    // Documents
    Route::resource('document', EmployeeDocumentController::class)->except(['show', 'destroy']);
    Route::get('/doc_summary', [EmployeeDocumentController::class, 'doc_summary'])->name('doc_summary');
    Route::post('/payment/add', [EmployeeDocumentController::class, 'add_payment'])->name('add_payment');
    Route::get('/payments/{document}', [EmployeeDocumentController::class, 'paymentHistory'])->name('payments');
    Route::get('/document/complete/{document}', [EmployeeDocumentController::class, 'completeDocument'])->name('document.complete');
    Route::get('/service/{service}/addons', [EmployeeDocumentController::class, 'getServiceAddons'])->name('get_service_addons');
    Route::get('/clients/search', [AdminClientDocumentController::class, 'search'])->name('clients.search');

    // Expenses
    Route::get('/expense_admin', [EmployeeExpenseController::class, 'index'])->name('expense_admin.index');
    Route::post('/expense_admin', [EmployeeExpenseController::class, 'store'])->name('expense_admin.store');
    Route::get('/expense_admin/statistika', [EmployeeExpenseController::class, 'statistika'])->name('expense.statistika');
});
