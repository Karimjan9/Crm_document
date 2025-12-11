<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FilialController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\ServiceAddonController;
use App\Http\Controllers\Admin\DirectionTypeController;
use App\Http\Controllers\Admin\SMSMessageTextController;
use App\Http\Controllers\Admin\ConsulationTypeController;

Route::name('superadmin.')->prefix('superadmin')->group(function(){

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
});

