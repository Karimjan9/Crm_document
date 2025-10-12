<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FilialController;





Route::get('/admin', [AdminController::class, 'index'])->name('index');
Route::resource('/filial',FilialController::class)->except(['show']);
