<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Courier\CourierController;
use App\Http\Controllers\KalendarController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\Admin\AdminFilialDocumentController;

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
    return redirect('https://sites.google.com/view/tarjimalarmarkazi'); 
});



   

   
 





   
});

// holidays

Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
Route::delete('/holidays/{date}', [HolidayController::class, 'destroy'])->name('holidays.destroy');




Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/calendar', function () {
        return view('admin.calendar.index');
    })->name('calendar.index');

    Route::get('/calendar/create', function () {
        return view('admin.calendar.create');
    })->name('calendar.create');
});



// Route::prefix('admin')->group(function () {
//     Route::get('/filial/{filial}/employees-stat', [AdminFilialDocumentController::class, 'employeesStat'])
//         ->name('admin.filial.employees.stat');

//     Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons'])
//         ->name('admin.service.addons');
// });





// Route::get('/courier', [CourierController::class, 'index'])->name('courier.index');




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

