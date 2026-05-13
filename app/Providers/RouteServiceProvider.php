<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/employee';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware(['web', 'auth', 'role:admin_manager|super_admin'])
                ->group(base_path('routes/admin_manager/admin.php'));

            Route::middleware(['web', 'auth', 'role:admin_manager|super_admin'])
                ->group(base_path('routes/super_admin/super_admin.php'));

            Route::middleware(['web', 'auth', 'role:employee'])
                ->group(base_path('routes/employee/employee.php'));

            Route::middleware(['web', 'auth', 'role:courier'])
                ->group(base_path('routes/courier/courier.php'));

            Route::middleware(['web', 'auth', 'role:admin_filial'])
                ->group(base_path('routes/admin_filial/admin_filial.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
