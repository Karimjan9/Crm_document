<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
        $t0 = microtime(true);
        $request->authenticate();
        $t1 = microtime(true);

        $request->session()->regenerate();
        $t2 = microtime(true);
        $user = $request->user()->loadMissing('roles');
        $t3 = microtime(true);

        if (config('app.debug')) {
            \Log::info('auth_login_timing', [
                'authenticate_ms' => (int) (($t1 - $t0) * 1000),
                'session_regen_ms' => (int) (($t2 - $t1) * 1000),
                'load_roles_ms' => (int) (($t3 - $t2) * 1000),
                'total_ms' => (int) (($t3 - $t0) * 1000),
            ]);
        }

        $roles = $user->getRoleNames();
        if ($roles->contains('admin_manager') || $roles->contains('super_admin')) {
            // dd('here');
            return redirect()->route('superadmin.index');

        } elseif ($roles->contains('admin_filial')) {
            // dd(1);
            return redirect()->route('admin_filial.index');

        } elseif ($roles->contains('employee')) {
            // dd(1);
            return redirect()->route('employee.document.index');

        } elseif ($roles->contains('courier')) {

            return redirect()->route('courier.documents.index');
        } elseif ($roles->contains('partner_admin') || $roles->contains('partner_operator')) {

            return redirect()->route('partner.dashboard');
        } elseif ($roles->contains('user')) {

            return redirect()->route('account.home');
        } else {
            // An account without an application role must not retain a session.
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Bu hisob uchun tizimga kirish ruxsati yo\'q.',
            ]);
        }

    }

    public function changePassword(): RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Logoutdan so'ng sahifa darhol ochilsin. Oddiy /login refresh'da
        // welcome intro avvalgidek ko'rsatiladi.
        return to_route('login', ['logged_out' => 1]);
    }
}
