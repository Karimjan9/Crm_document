<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Courier\CourierController;
use App\Http\Controllers\Courier\DocumentController as CourierDocumentController;
use App\Http\Controllers\Courier\OrderDeliveryController;


Route::name('courier.')->prefix('courier')->group(function(){
    
    Route::get('/courier_part', [CourierController::class, 'index'])->name('index');

    Route::get('/documents', [CourierDocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/history', [CourierDocumentController::class, 'history'])->name('documents.history');
    Route::post('/documents/{documentCourier}/accept', [CourierDocumentController::class, 'accept'])->name('documents.accept');
    Route::post('/documents/{documentCourier}/reject', [CourierDocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/documents/{documentCourier}/return', [CourierDocumentController::class, 'returnDocument'])->name('documents.return');

    Route::get('/orders', [OrderDeliveryController::class, 'index'])->name('orders.index');
    Route::post('/orders/{delivery}/accept', [OrderDeliveryController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{delivery}/deliver', [OrderDeliveryController::class, 'deliver'])->name('orders.deliver');
    Route::post('/orders/{delivery}/return', [OrderDeliveryController::class, 'returnDelivery'])->name('orders.return');
});
