<?php

namespace App\Providers;

use App\Support\DeadlineBellData;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
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
            $user = auth()->user();

            if (! $user) {
                $view->with('deadlineBell', DeadlineBellData::buildFor(null));

                return;
            }

            $user->loadMissing('roles');

            $cacheKey = sprintf(
                'deadline_bell:%s:%s:%s',
                $user->getKey(),
                md5($user->roles->pluck('name')->sort()->implode('|')),
                $user->filial_id ?? 'none'
            );

            $deadlineBell = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($user) {
                return DeadlineBellData::buildFor($user);
            });

            $view->with('deadlineBell', $deadlineBell);
        });

        Paginator::defaultView('vendor.pagination.bootstrap-5');
 
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');
    }
}
