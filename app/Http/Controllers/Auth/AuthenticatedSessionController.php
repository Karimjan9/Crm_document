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


    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        if($request->user()->hasRole("rektor")){

            return redirect()->route('rektor_role.show_all_statistic');

        }else if($request->user()->hasRole("bugalter")){

            return redirect()->route('bugalter_role.accountant.index');

        }else if($request->user()->hasRole("admin_barn")){

            return redirect()->route('admin_barn_role.missing_equipment.index' );

        }else if($request->user()->hasRole("storekeeper")){

            return redirect()->route('storekeeper_role.prixod.index');

        }else if($request->user()->hasRole("kadr")){

            return redirect()->route('kadr_role.actions.index');
        
        }else if($request->user()->hasRole("user")){

            return redirect()->route('user_role.users_invertar.index');
        }else if($request->user()->hasRole("tech_checker")){
          
            return redirect()->route('user_role.users_invertar.index');
        }else if($request->user()->hasRole("other_checker")){

            return redirect()->route('user_role.users_invertar.index');
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

        return redirect('/login');
    }
}
