<?php

use App\Http\Controllers\Barn\Kadr\RankNameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Barn\Kadr\KadrController;
use App\Http\Controllers\Barn\Kadr\CareerController;
use App\Http\Controllers\MissingEquipmentController;
use App\Http\Controllers\Barn\GetJob\GetJobContoller;
use App\Http\Controllers\Barn\Rektor\RektorController;
use App\Http\Controllers\Barn\Command\CommandController;
use App\Http\Controllers\Barn\Kadr\DepartmentController;
use App\Http\Controllers\Barn\Kadr\Search\SearchController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
// use dompdf;
use App\Http\Controllers\Barn\Admin_Barn\AdminBarnController;
use App\Http\Controllers\Barn\Accountant\AccountantController;
use App\Http\Controllers\Barn\User\UserController as barn_user;
use App\Http\Controllers\Barn\Storekeeper\Cargo\CargoController;
use App\Http\Controllers\Barn\Storekeeper\Items\ItemsController;
use App\Http\Controllers\Barn\Storekeeper\StorekeeperController;
use App\Http\Controllers\Barn\Storekeeper\Prixod\PrixodController;
use App\Http\Controllers\Barn\Admin_Barn\AcceptedEquipmentController;
use App\Http\Controllers\Barn\Kadr\CareerUpdateController;
use App\Http\Controllers\Barn\Kadr\DepartamentUpdateController;
use App\Http\Controllers\Barn\OtherCheker\OtherChekerController;
use App\Http\Controllers\Barn\Rektor\RektorApplicationController;
use App\Http\Controllers\Barn\Rektor\RektorCurrencyController;
use App\Http\Controllers\Barn\Rektor\RektorPrixodController;
use App\Http\Controllers\Barn\Storekeeper\Items\ItemsUnityController;
use App\Http\Controllers\Barn\Storekeeper\Provider\ProviderController;
use App\Http\Controllers\Barn\Storekeeper\TypeOfItems\TypeOFItemsController;
use App\Http\Controllers\Barn\Storekeeper\TypeOfItems\SecondTypeOfItemsController;
use App\Http\Controllers\Barn\TechCheker\User\TechChekerUserController;
use App\Http\Controllers\Barn\User\ApplicationController;
use App\Http\Controllers\SuperAdmin\EmployeeController as SuperAdminEmployeeController;
use App\Models\GiveItemModel;
use App\Models\ItemsModel;
use App\Models\PrixodModel;
use App\Models\SecondTypeOfItem;

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


Route::get('/', [UserController::class, 'index'])->name('test');

Route::middleware(['auth'])->group(function () {


        Route::post('/file', function (Request $req) {
            
            return Storage::response($req->input('id'));
        })->name('file');





    Route::group(['middleware' => 'role:rektor'], function() {
        Route::prefix('rektor_role')->group(function () {
            Route::name('rektor_role.')->group(function () {

                Route::resource('/rektor_actions', RektorController::class);

                Route::resource('/prixod_show',RektorPrixodController::class)->only('index','show');

                Route::resource('/rektor_currency',RektorCurrencyController::class)->except('destroy','show');

                Route::get('/{app_id}/deny_application',[RektorController::class,'deny_application'])->name('deny.application');

                Route::get('/{app_id}/accept_application',[RektorController::class,'accept_application'])->name('accept.application');

                Route::get('/ajax_get_fillter_application',[RektorController::class,'ajax_get_fill_app'])->name('ajax_get_filter_app');

                Route::get('/show_all_items',[RektorController::class,'show_items'])->name('show_all_items');

                Route::get('/show_stat_item/{item_id}',[RektorController::class,'show_stat_item'])->name('show_stat_item');

                Route::get('/show_all_statistic',[RektorController::class,'show_statistic'])->name('show_all_statistic');

                Route::get('/ajax_get_fillter_without_b',[RektorController::class,'ajax_get_without_bodily'])->name('ajax_get_without_bodily');

                Route::get('/ajax_get_fillter_with_b',[RektorController::class,'ajax_get_with_bodily'])->name('ajax_get_with_bodily');

                Route::get('/ajax_get_second_types',[RektorController::class,'ajax_get_second_types'])->name('ajax_get_second_types');

                Route::get('/ajax_get_rektor_filter',[RektorController::class,'rektor_filter'])->name('ajax_rektor_statistic');

                Route::get('/ajax_building_filter',[RektorController::class,'rektor_statistic'])->name('ajax_building_filter');

                Route::get('/application_show',[RektorApplicationController::class,'index'])->name('application_index');

                Route::get('/application_accept/{application_id}',[RektorApplicationController::class,'accept_application'])->name('application_accept');

                Route::delete('/application_denide/{application_id}',[RektorApplicationController::class,'denide_application'])->name('application_denide');

            });
        });
    });

    // Route::group(['middleware' => 'role:bugalter'], function() {
    //     Route::prefix('bugalter_role')->group(function () {
    //         Route::name('bugalter_role.')->group(function () {

    //             Route::resource('/accountant', AccountantController::class);

    //             Route::get('/order_to_manager/{ask_id}',[AccountantController::class,'to_manager'])->name('order.to_manager');

    //             Route::get('/show_to_accountant',[AccountantController::class,'show_accountant'])->name('show_accountant');

    //             Route::get('/ajax_get_accountant_without_b',[AccountantController::class,'ajax_get_without_bodily_accountant'])->name('accountant_without_bodily');

    //             Route::get('/ajax_get_accountant_with_b',[AccountantController::class,'ajax_get_with_bodily_accountant'])->name('accountant_with_bodily');

    //             Route::get('/ajax_acco_second_types',[AccountantController::class,'ajax_acco_second_types'])->name('ajax_acco_second_types');

    //             Route::get('/show_orders',[AccountantController::class,'show_orders'])->name('show_orders');


    //         });
    //     });
    // });

    // Route::group(['middleware' => 'role:admin_barn'], function() {
    //     Route::prefix('admin_barn_role')->group(function () {
    //         Route::name('admin_barn_role.')->group(function () {

    //             Route::resource('/admin_actions', AdminBarnController::class)->only([
    //                 'index','update',
    //             ]);
                
    //             Route::resource('/admin_accepts', AcceptedEquipmentController::class)->only([
    //                 'index','update',
    //             ]);

    //             Route::get('/items_for_order/{order_id}',[AdminBarnController::class,'item_for_order'])->name('item_for_order');

    //             Route::resource('missing_equipment',MissingEquipmentController::class)->only(['index']);
                
    //         });
    //     });
    // });

    Route::group(['middleware' => 'role:tech_checker'], function() {
        Route::prefix('tech_repair')->group(function () {
            Route::name('tech_checker.')->group(function () {

                Route::resource('/tech_checker',TechChekerUserController::class)->only('index');
                
                Route::put('tech_swap/{swap_id}',[TechChekerUserController::class,'swap_to_other'])->name('swap_to_other');

                Route::post('tech_accept/{accept_id}',[TechChekerUserController::class,'accept_to_repair'])->name('accept_to_repair');
            });
        });
    });

    Route::group(['middleware' => 'role:other_checker'], function() {
        Route::prefix('other_cheker')->group(function () {
            Route::name('other_cheker.')->group(function () {
                
                Route::resource('/other_cheker',OtherChekerController::class)->only('index');
             
            });
        });
    });  

    Route::group(['middleware' => 'role:kadr'], function() {
        Route::prefix('storekeeper_role')->group(function () {
            Route::name('storekeeper_role.')->group(function () {

                Route::get('/command_input',[ItemsController::class,'command_input'])->name('command_input');

                Route::post('/command_create',[ItemsController::class,'command_create'])->name('command_create');

                Route::resource('/actions', StorekeeperController::class);

                Route::resource('/type_item_take',TypeOFItemsController::class);


                Route::resource('/second_type_item',SecondTypeOfItemsController::class);

                Route::resource('/action/items',ItemsController::class);

                Route::resource('/item_unity',ItemsUnityController::class);


                Route::resource('/cargo',CargoController::class);

                Route::resource('/provider',ProviderController::class);

                Route::resource('/prixod',PrixodController::class);
            

                Route::get('/prixod_list',[PrixodController::class,'list_prixod'])->name('prixod_list');

                Route::get('/ajax_get_second_type',[PrixodController::class,'ajax_get_second_type'])->name('ajax_get_second_type');
                
                Route::get('/ajax_get_item',[PrixodController::class,'ajax_get_item'])->name('ajax_get_item');

                Route::post('/store_all',[PrixodController::class,'store_all'])->name('store_all');

                Route::post('/store_all_items',[CareerUpdateController::class,'store_all_items'])->name('store_all_items');

                Route::delete('/items_give_to_barn/{dep_kaf}',[CareerUpdateController::class,'items_give_to_barn'])->name('items_give_to_barn');

                Route::get('/prixod_search_items',[SearchController::class,'prixod_search_items'])->name('prixod_search_items');

                Route::get('/departament_search_items',[SearchController::class,'departament_search_items'])->name('departament_search_items');

                Route::get('/selected_item_prixod/{selected_id}',[SearchController::class,'selected_item_prixod'])->name('selected_item_prixod');

                Route::get('/switch_cargo',[CargoController::class , 'switch_cargo'])->name('cargo_switch');

                Route::get('/switch_departament',[DepartamentUpdateController::class , 'switch_departament'])->name('dep_kaf_switch');

                Route::get('/selected_item_departament/{selected_id}',[SearchController::class,'selected_item_departament'])->name('selected_item_departament');

                Route::resource('/rank',RankNameController::class);


                
            }); 
        });
        Route::prefix('storekeeper/ajax')->group(function () {
            Route::name('storekeeper.ajax.')->group(function () {
                Route::get('ajax_item_types', [ItemsController::class, 'get_second_types'])->name('get_second_types');
            }); 
        });
    });

    Route::group(['middleware' => 'role:kadr'], function() {
        Route::prefix('kadr_role')->group(function () {
            Route::name('kadr_role.')->group(function () {
                

                Route::resource('change_career/command', CommandController::class);

                Route::resource('/actions', KadrController::class);

                // Route::resource('/department_employee', DepartmentController::class);

                Route::resource('/department_update', DepartamentUpdateController::class);


                // Route::resource('/career_employee', CareerController::class);

                Route::resource('/career_update',CareerUpdateController::class);

                Route::get('/reset_func/{dep_id}',[CareerUpdateController::class,'reset_for_dep'])->name('reset_func_for_dep');

                Route::get('/change_department/{user}', [KadrController::class, 'change_departament'])->name('change.departament');

                Route::post('/change_department/store/{user}', [KadrController::class, 'change_store'])->name('change.departament.store');

                Route::get('/change_career/{user}/{get_job}', [KadrController::class, 'change_career'])->name('change.career');

                Route::post('/change_career/store/{user}', [KadrController::class, 'change_store_career'])->name('change.career.store');

                // Route::post('/search', [SearchController::class, 'search'])->name('search');

                Route::get('/search_item', [SearchController::class, 'search_item'])->name('search.item');

                Route::get('/see_items_give/{item_id}', [SearchController::class, 'see_items_give'])->name('search.see_items_give');


                // Route::get('/item_update/{item_id}', [SearchController::class, 'item_page'])->name('search.item_update_page');


                Route::get('/second_data/{user}', [KadrController::class, 'second_data'])->name(  'change.second_data');

                Route::get('/user_functions/{user}', [KadrController::class, 'data_give'])->name(  'user.data_give');


                Route::post('/second_data/store/{id}', [KadrController::class, 'second_data_store'])->name( 'second_data.store');

                
                Route::get('/get_register/filter_departament', [KadrController::class, 'filter_departament'])->name('get_register.filter_departament');
            
                Route::get('/get_job/index/{user_id}', [GetJobContoller::class, 'index'])->name('get_job.index');

                Route::post('/get_job/store/{user_id}', [GetJobContoller::class, 'store'])->name('get_job.store');

                Route::get('/get_job/filter_departament', [GetJobContoller::class, 'filter_departament'])->name('get_job.filter_departament');

                Route::get('/type_job/filter_rate', [GetJobContoller::class, 'filter_rate'])->name('get_job_rate.filter_rate');

                Route::get('/type_job/filter_rate/change_career', [GetJobContoller::class, 'filter_rate_for_change'])->name('get_job_rate.filter_rate.change_career');
                
                Route::get('/departament_list',[CareerUpdateController::class,'departament_list'])->name('departament_list');
        

            
            });
        });
    });

    Route::group(['middleware' => "role:user,tech_checker,other_checker"], function() {
        Route::prefix('user_role')->group(function () {
            Route::name('user_role.')->group(function () {

               

                Route::resource('/users_invertar', barn_user::class)->only('index','show');

                Route::post('/invertar_store/{dep_id}/{item_id}',[barn_user::class,'store_invertar'])->name('invertar_store');

                Route::get('/show_invertar_deps',[barn_user::class,'show_invertar_deps'])->name('show_invertar_deps');

                Route::get('/show_invertar_room/{room_id}',[barn_user::class,'show_invertar_room'])->name('show_invertar_room');

                Route::get('/get_item/to_repair/{item_id}/{room_id}',[barn_user::class,'get_to_repair'])->name('get_to_repair');

                Route::get('/show_repair_items',[barn_user::class,'show_repair_items'])->name('show_repair_items');


                Route::resource('/application', ApplicationController::class);

                // Route::get('/ajax_get_second_type',[barn_user::class,'get_second_types'])->name('ajax_get_second_type_for_user');

                // Route::get('/petitions_list',[barn_user::class,'list_of_petition'])->name('list_petition');
                
                // Route::post('/petitions_store_all',[barn_user::class,'store_all'])->name('petition_store_all');
                

                // Route::get('/devices_show',[barn_user::class,'show_devices'])->name('show.devices');

                // Route::get('/devices_show_users',[barn_user::class,'show_items_user'])->name('show.devices_users');


                // Route::put('/device_update/{id}',[barn_user::class,'device_accept'])->name('show.devices_accept');

                // Route::put('/device_update_deny/{id}',[barn_user::class,'device_deny'])->name('show.devices_deny');
            });
        });
    });








    Route::get('/change-password', [AuthenticatedSessionController::class, 'change-password'])->name('change-password');

        Route::any('/destroy', [AuthenticatedSessionController::class, 'destroy'])->name('destroy');

        Route::get('/change_session', [PrixodController::class, 'clear_session'])->name('clear_session');

        Route::get('/clear_cache', function () {

            Artisan::call('migrate');

            Artisan::call('db:seed --class=BuildingSeeder');

            // Artisan::call('migrate:rollback --step=1');


            // Artisan::call('migrate');
            
            // Artisan::call('db:seed --class=CurrencySeeder');

            
            return redirect()->route('login');
            

        });
});



Route::middleware(['guest'])->group(function () {
    
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login_post');
});

Route::get("test", [SuperAdminEmployeeController::class, 'index_vue']);

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













