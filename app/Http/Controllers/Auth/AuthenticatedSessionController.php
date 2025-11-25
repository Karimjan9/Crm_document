<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('login');
    }
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();
      
        if($request->user()->hasRole("admin_manager") || $request->user()->hasRole("super_admin")){
            // dd('here');
            return redirect()->route('superadmin.index');

        }
        else if($request->user()->hasRole("admin_filial")){
            // dd(1);
                    return redirect()->route('admin_filial.index');

        }else if($request->user()->hasRole("employee")){
            // dd(1);
                return redirect()->route('employee.index');

        }else if($request->user()->hasRole("courier")){

                 return redirect()->route('courier.index'); 
        }
        else{
                 return redirect()->route('login'); 

        }
      

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('login');
    }
}
