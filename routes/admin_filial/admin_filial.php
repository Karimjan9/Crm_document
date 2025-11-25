    <?php

    use App\Http\Controllers\Admin\AdminClientDocumentController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Admin\AdminFilialController;
    use App\Http\Controllers\Admin\AdminFilialDocumentController;


    Route::name('admin_filial.')->prefix('admin_filial')->group(function(){

        Route::get('/index', [AdminFilialController::class, 'index'])->name('index');

        Route::resource('document', AdminFilialDocumentController::class);

        Route::get('/clients/search', [AdminClientDocumentController::class, 'search'])->name('clients.search');

        Route::get('/clients/map', [AdminClientDocumentController::class, 'mapData'])->name('clients.map');
    

        Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons']);
        
        Route::name('admin_filial.')->prefix('admin_filial')->group(function(){

            
        Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons'])->name('get_service_addons');

        Route::get(  '/admin/filial/get-service-addons/{service}',  [AdminFilialDocumentController::class, 'getServiceAddons'])->name('admin_filial.get_service_addons');

        Route::get('/doc_summary', [AdminFilialDocumentController::class, 'doc_summary'])->name('doc_summary');

    });

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





