<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Employee\EmployeeController;



Route::name('employee.')->prefix('employee')->group(function(){
    
    Route::get('/courier', [EmployeeController::class, 'index'])->name('courier.index');
    // Add more courier routes here
});