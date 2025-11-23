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

    Route::name('admin_filial.')->prefix('admin_filial')->group(function(){

        Route::get('/index', [AdminFilialController::class, 'index'])->name('index');

        Route::resource('document', AdminFilialDocumentController::class);

        Route::get('/clients/search', [AdminClientDocumentController::class, 'search'])->name('clients.search');

        Route::get('/clients/map', [AdminClientDocumentController::class, 'mapData'])->name('clients.map');
    
        Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons']);
    });

