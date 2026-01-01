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
use App\Http\Controllers\Admin\FCalendar\CalendarController as FCalendarController;

Route::name('superadmin.')->prefix('superadmin')->group(function(){

    Route::prefix('fl')->group(function () {

        Route::get('calendar',  function () {
            return view('calendar/fl/index');
        });

        // Календарь
        Route::prefix('calendar')->group(function () {
            Route::get('/data', [FCalendarController::class, 'getCalendarData']);
            Route::post('/check-date', [FCalendarController::class, 'checkDateAvailability']);
        });

        // Праздники (CRUD)
        Route::prefix('holidays')->group(function () {
            Route::get('/', [HolidayController::class, 'index']);
            Route::get('/upcoming', [HolidayController::class, 'upcoming']);
            Route::get('/stats', [HolidayController::class, 'stats']);
            Route::get('/{id}', [HolidayController::class, 'show']);
            Route::get('/{id}/edit', [HolidayController::class, 'edit']);
            Route::post('/', [HolidayController::class, 'store']);
            Route::put('/{id}', [HolidayController::class, 'update']);
            Route::delete('/{id}', [HolidayController::class, 'destroy']);
            Route::post('/{id}/copy', [HolidayController::class, 'copy']);
            Route::post('/bulk-import', [HolidayController::class, 'bulkImport']);
            Route::get('/export/csv', [HolidayController::class, 'exportCsv']);
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

    Route::get('/calendar/index', [CalendarController::class, 'index'])->name('calendar.index');

    Route::resource('/service',ServiceController::class)->except(['show']);

    Route::resource('service/{service}/addon',ServiceAddonController::class)->except(['show', 'index']);

    Route::resource('/document_type',DocumentTypeController::class)->except(['show']);

    Route::resource('/direction_type',DirectionTypeController::class)->except(['show']);

    Route::resource('/consulation',ConsulationTypeController::class)->except(['show']);

    Route::resource('/sms_message_text', SMSMessageTextController::class)->except(['show']);

    Route::resource('document_type/{document_type}/type_addition',TypeAdditionController::class)->except(['show']);

    Route::post('/store_document_direction', [DirectionTypeController::class, 'store_type_direction'])->name('store_direction_addition');

    Route::delete('/delete_document_additional/{addition}', [DocumentTypeController::class, 'delete_type_additional'])->name('addition.destroy');

    Route::delete('/delete_document_direction/{addition}', [DirectionTypeController::class, 'delete_type_direction'])->name('delete_direction_addition');

    // Direction Type Comment Routes
    // Route::post('/superadmin/store-comment', [DocumentTypeController::class, 'store_direction_type_comment'])->name('superadmin.store_comment');

    // Route::put('/superadmin/comment/{id}', [DocumentTypeController::class, 'update_direction_type_comment'])->name('superadmin.update_comment');

    // Route::delete('/superadmin/direction_type_comment/{comment}', [DocumentTypeController::class, 'destroy_direction_type_comment'])->name('superadmin.direction_type_comment.destroy');

    Route::resource('direction_type/{direction_type}/direction_addition',DirectionAdditionController::class)->except(['show']);

});


