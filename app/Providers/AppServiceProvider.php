<?php

namespace App\Providers;

use App\Support\DeadlineBellData;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('header', function ($view) {
            $view->with('deadlineBell', DeadlineBellData::buildFor(auth()->user()));
        });

        Paginator::defaultView('vendor.pagination.bootstrap-5');
 
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');
    }
}
