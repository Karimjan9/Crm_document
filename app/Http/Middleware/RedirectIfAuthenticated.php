<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if($request->user()->hasRole("super_admin")){

                    return redirect()->route('superadmin.employees.index');
                }else if($request->user()->hasRole("admin")){
        
                    return redirect()->route('admin.employees.index');
                }else if($request->user()->hasRole("user")){
        
                    // return redirect()->route('user_role.employee.index', ["profile" => $request->user()->id ]);
                }
            }
        }

        return $next($request);
    }
}
