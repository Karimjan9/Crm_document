<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Courier\CourierController;


    Route::get('/courier', [CourierController::class, 'index'])->name('courier.index');
    // Add more courier routes here
