<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Courier\CourierController;


Route::name('courier.')->prefix('courier')->group(function(){
    
    Route::get('/courier', [CourierController::class, 'index'])->name('index');
    // Add more courier routes here
});