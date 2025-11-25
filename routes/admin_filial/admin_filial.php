    <?php

    use App\Http\Controllers\Admin\AdminClientDocumentController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Admin\AdminController;
    use App\Http\Controllers\Admin\FilialController;
    use App\Http\Controllers\Admin\ExpenseController;
    use App\Http\Controllers\Admin\CalendarController;
    use App\Http\Controllers\Admin\DocumentController;
    use App\Http\Controllers\Admin\AdminFilialController;
    use App\Http\Controllers\Admin\AdminFilialDocumentController;
    use App\Http\Controllers\Admin\EmployeeDocumentController;

    Route::name('admin_filial.')->prefix('admin_filial')->group(function(){

        Route::get('/index', [AdminFilialController::class, 'index'])->name('index');

        Route::resource('document', AdminFilialDocumentController::class);

        Route::get('/clients/search', [AdminClientDocumentController::class, 'search'])->name('clients.search');

        Route::get('/clients/map', [AdminClientDocumentController::class, 'mapData'])->name('clients.map');
    
        Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons']);
        
        Route::name('admin_filial.')->prefix('admin_filial')->group(function(){

    // Dashboard
    Route::get('/index', [AdminFilialController::class, 'index'])
        ->name('index');

    // Employees
    Route::get('/employees', [AdminFilialController::class, 'employees'])
        ->name('employees.index');

    Route::get('/employees/create', [AdminFilialController::class, 'employeeCreate'])
        ->name('employees.create');

    // Stats
    Route::get('/stats', [AdminFilialController::class, 'stats'])
        ->name('stats.index');

    // Documents
    Route::get('/documents', [AdminFilialController::class, 'documents'])
        ->name('documents.index');

    Route::get('/documents/create', [AdminFilialController::class, 'documentsCreate'])
        ->name('documents.create');

    // Deadlines
    Route::get('/deadlines', [AdminFilialController::class, 'deadlines'])
        ->name('deadlines.index');

});





    });

