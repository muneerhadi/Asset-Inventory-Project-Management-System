<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // #region agent log
        $logPath = 'c:\Users\hadim\project-asset-inventory-system\project-asset-inventory-system\Hadi project\.cursor\debug.log';
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run_check_version',
            'hypothesisId' => 'C',
            'location' => 'AuthenticatedSessionController.php:32',
            'message' => 'Controller store method reached',
            'timestamp' => time() * 1000
        ];
        @file_put_contents($logPath, json_encode($logData) . PHP_EOL, FILE_APPEND);
        // #endregion

        $request->authenticate();

        $request->session()->regenerate();

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
