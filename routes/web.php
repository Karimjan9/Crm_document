<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\KalendarController;

// use dompdf;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/




Route::middleware(['auth'])->group(function () {


       



   
Route::get('/', function () {
    return view('welcome');
});



   

   
 





   
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('calendar', function () {
        return view('admin.calendar.index');
    })->name('calendar.index');

    Route::get('calendar/create', function () {
        return view('admin.calendar.create');
    })->name('calendar.create');
});



    Route::get('/change-password', [AuthenticatedSessionController::class, 'change-password'])->name('change-password');

        Route::any('/destroy', [AuthenticatedSessionController::class, 'destroy'])->name('destroy');
    


       


        // Route::get('/change_session', [PrixodController::class, 'clear_session'])->name('clear_session');

        Route::get('/clear_cache', function () {

            Artisan::call('migrate');

            // Artisan::call('db:seed --class=BuildingSeeder');

            // Artisan::call('migrate:rollback --step=1');


            // Artisan::call('migrate');
            
            // Artisan::call('db:seed --class=CurrencySeeder');

            
            return redirect()->route('login');
            

        });




Route::middleware(['guest'])->group(function () {
    
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login_post');
});



Route::get("/load_script", function () {

    if (env('APP_DEBUG')) {
        
        Artisan::call('migrate:fresh');
    
        Artisan::call('db:seed');
    
        return view("reload_script");
    }

})->name("load_script");

Route::get('test', function(){
    return view('test.test');
});

