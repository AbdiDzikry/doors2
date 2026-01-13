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
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Redirect based on Role/Permission
        // Redirect based on Role/Permission
        
        // Super Admin always goes to main dashboard
        if ($user->hasRole('Super Admin')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($user->can('access tablet mode')) {
            // Priority: If user ONLY has tablet access or explicitly is 'Tablet' role
            // Since we don't know the exact role name, we proceed if they have permission.
            // But Admins also have this permission.
            // Assumption: Admins want Main Dashboard. Tablet Users (Kiosk) want Tablet Dashboard.
            // Check if user has "access panry dashboard" or other high-level perms?
            
            // Or better: Check if they DO NOT have access to main dashboard features?
            // Let's assume a "Tablet" role exists.
            if ($user->hasRole('Tablet') || $user->hasRole('tablet')) {
                 return redirect()->intended(route('tablet.index', absolute: false));
            }
        }
        
        if ($user->can('access pantry dashboard') && !$user->hasRole('Super Admin')) {
             return redirect()->intended(route('dashboard.receptionist', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
