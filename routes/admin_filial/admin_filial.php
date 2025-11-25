<?php

use App\Http\Controllers\Admin\AdminClientDocumentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminFilialController;
use App\Http\Controllers\Admin\AdminFilialDocumentController;

Route::name('admin_filial.')->prefix('admin_filial')->group(function () {

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

});

//     // Dashboard
//     Route::get('/index', [AdminFilialController::class, 'index'])
//         ->name('index');

//     // Employees
//     Route::get('/employees', [AdminFilialController::class, 'employees'])
//         ->name('employees.index');

//     Route::get('/employees/create', [AdminFilialController::class, 'employeeCreate'])
//         ->name('employees.create');

//     // Stats
//     Route::get('/stats', [AdminFilialController::class, 'stats'])
//         ->name('stats.index');

//     // Documents
//     Route::get('/documents', [AdminFilialController::class, 'documents'])
//         ->name('documents.index');

//     Route::get('/documents/create', [AdminFilialController::class, 'documentsCreate'])
//         ->name('documents.create');

//     // Deadlines
//     Route::get('/deadlines', [AdminFilialController::class, 'deadlines'])
//         ->name('deadlines.index');

// });





