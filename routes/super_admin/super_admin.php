<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FilialController;
use App\Http\Controllers\TypeAdditionController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\DirectionAdditionController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\ServiceAddonController;
use App\Http\Controllers\Admin\DirectionTypeController;
use App\Http\Controllers\Admin\SMSMessageTextController;
use App\Http\Controllers\Admin\ConsulationTypeController;
use App\Http\Controllers\Admin\FCalendar\HolidayController;
use App\Http\Controllers\Admin\Api\ClientController;
use App\Http\Controllers\Admin\Api\DocumentController as ApiDocumentController;
use App\Http\Controllers\SuperAdmin\ExcelExportController;
use App\Http\Controllers\SuperAdmin\StaticApostilController;
use App\Http\Controllers\SuperAdmin\PackageTemplateController;
use App\Http\Controllers\SuperAdmin\MonthlyNotificationController;
use App\Http\Controllers\Admin\FCalendar\CalendarController as FCalendarController;

Route::name('superadmin.')->prefix('superadmin')->group(function(){
    Route::middleware('role:super_admin')->prefix('monthly-notifications')->name('monthly_notifications.')->group(function () {
        Route::get('/', [MonthlyNotificationController::class, 'index'])->name('index');
        Route::get('/sql-backup', [MonthlyNotificationController::class, 'downloadSqlBackup'])->name('sql_backup');
        Route::post('/{notification}/read', [MonthlyNotificationController::class, 'markAsRead'])->name('read');
    });

    Route::prefix('api')->group(function () {
        Route::post('/document', [ApiDocumentController::class, 'store'])->name('api.document.store');
        Route::get('/clients/search', [ClientController::class, 'search'])->name('api.clients.search');
        Route::apiResource('/clients', ClientController::class);
        Route::post('/document/save-all', [ApiDocumentController::class, 'storeAll'])->name('api.document.save_all');
        Route::get('/get-addons/{type}/{id}', [ApiDocumentController::class, 'getAddons'])->name('api.addons.index');
    });

    Route::prefix('fl')->group(function () {

        Route::get('calendar',  function () {
            return view('calendar/fl/index');
        })->name('calendar.full.index');

        // Календарь
        Route::prefix('calendar')->group(function () {
            Route::get('/data', [FCalendarController::class, 'getCalendarData'])->name('calendar.data');
            Route::post('/check-date', [FCalendarController::class, 'checkDateAvailability'])->name('calendar.check_date');
        });

        // Праздники (CRUD)
        Route::prefix('holidays')->group(function () {
            Route::get('/', [HolidayController::class, 'index'])->name('holidays.index');
            Route::get('/upcoming', [HolidayController::class, 'upcoming'])->name('holidays.upcoming');
            Route::get('/stats', [HolidayController::class, 'stats'])->name('holidays.stats');
            Route::get('/{id}', [HolidayController::class, 'show'])->name('holidays.show');
            Route::get('/{id}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
            Route::post('/', [HolidayController::class, 'store'])->name('holidays.store');
            Route::put('/{id}', [HolidayController::class, 'update'])->name('holidays.update');
            Route::delete('/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
            Route::post('/{id}/copy', [HolidayController::class, 'copy'])->name('holidays.copy');
            Route::post('/bulk-import', [HolidayController::class, 'bulkImport'])->name('holidays.bulk_import');
            Route::get('/export/csv', [HolidayController::class, 'exportCsv'])->name('holidays.export_csv');
        });
    });


    Route::get('/index', [AdminController::class, 'index'])->name('index');

    Route::get('/create', [AdminController::class, 'create'])->name('create');

    Route::post('/store', [AdminController::class, 'store'])->name('store');

    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('edit');

    Route::put('/update/{id}', [AdminController::class, 'update'])->name('update');

    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('destroy');

    Route::resource('/filial',FilialController::class)->except(['show']);

    Route::resource('/expense',ExpenseController::class);

    Route::get('/expense_static', [ExpenseController::class, 'statistika'])->name('statistika');

    Route::resource('/document',DocumentController::class);

    Route::get('/document_static', [DocumentController::class, 'statistika'])->name('document.statistika');

    Route::post('/payment/add', [DocumentController::class, 'add_payment'])->name('add_payment');
    Route::get('/payments/{document}', [DocumentController::class, 'paymentHistory'])->name('payments');

    Route::get('/calendar/index', [CalendarController::class, 'index'])->name('calendar.index');

    Route::resource('/service',ServiceController::class)->except(['show']);

    Route::middleware('role:super_admin')->resource('/template-package', PackageTemplateController::class)
        ->except(['show'])
        ->parameter('template-package', 'templatePackage')
        ->names('template_package');

    Route::resource('service/{service}/addon',ServiceAddonController::class)->except(['show', 'index']);

    Route::resource('/document_type',DocumentTypeController::class)->except(['show']);

    Route::resource('/direction_type',DirectionTypeController::class)->except(['show']);

    Route::resource('/static/apostil',StaticApostilController::class)->except(['show']);

    Route::resource('/consulation',ConsulationTypeController::class)->except(['show']);

    Route::get('/consulation_static', [ConsulationTypeController::class, 'static'])->name('consulation.static_main');

    Route::put('/consulation_static_update/{id}', [ConsulationTypeController::class, 'update_static'])->name('consulation.update_static_main');

    Route::delete('/consulation_static_delete/{id}', [ConsulationTypeController::class, 'destroy_static'])->name('consulation.destroy_static_main');

    Route::get('/consulation_main_type', [ConsulationTypeController::class, 'getMainConsulationType'])->name('consulation.get_main_type');

    Route::put('/consulation_main_type_update', [ConsulationTypeController::class, 'update_main'])->name('consulation.update_main_type');

    Route::get('/sms_message_text/report', [SMSMessageTextController::class, 'report'])->name('sms_message_text.report');
    Route::resource('/sms_message_text', SMSMessageTextController::class)->except(['show']);

    Route::middleware('role:super_admin')->prefix('excel')->name('excel.')->group(function () {
        Route::get('/{dataset}', [ExcelExportController::class, 'download'])
            ->where('dataset', 'clients|documents|employees|all')
            ->name('download');
    });

    Route::resource('document_type/{document_type}/type_addition',TypeAdditionController::class)->except(['show']);

    // Direction Type Comment Routes
    // Route::post('/superadmin/store-comment', [DocumentTypeController::class, 'store_direction_type_comment'])->name('superadmin.store_comment');

    // Route::put('/superadmin/comment/{id}', [DocumentTypeController::class, 'update_direction_type_comment'])->name('superadmin.update_comment');

    // Route::delete('/superadmin/direction_type_comment/{comment}', [DocumentTypeController::class, 'destroy_direction_type_comment'])->name('superadmin.direction_type_comment.destroy');

    Route::resource('direction_type/{direction_type}/direction_addition',DirectionAdditionController::class)->except(['show']);

});


