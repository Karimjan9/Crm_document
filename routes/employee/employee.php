<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Employee\EmployeeController;



Route::name('employee.')->prefix('employee')->group(function(){
    Route::get('/employee', [EmployeeController::class, 'index'])->name('index');
    // Add more courier routes here
});