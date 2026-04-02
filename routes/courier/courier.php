<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Courier\CourierController;
use App\Http\Controllers\Courier\DocumentController as CourierDocumentController;


Route::name('courier.')->prefix('courier')->group(function(){
    
    Route::get('/courier_part', [CourierController::class, 'index'])->name('index');

    Route::get('/documents', [CourierDocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/history', [CourierDocumentController::class, 'history'])->name('documents.history');
    Route::post('/documents/{documentCourier}/accept', [CourierDocumentController::class, 'accept'])->name('documents.accept');
    Route::post('/documents/{documentCourier}/reject', [CourierDocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/documents/{documentCourier}/return', [CourierDocumentController::class, 'returnDocument'])->name('documents.return');
});
