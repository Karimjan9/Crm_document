<?php

namespace App\Providers;

use App\Models\Criteria;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //

        try {
            //code...
            view()->share('criterias', Criteria::all());
        } catch (\Throwable $th) {
            //throw $th;
        }


        Paginator::defaultView('vendor.pagination.bootstrap-5');
 
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');
    }
}
