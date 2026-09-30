<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthSessionService $authSessionService,
    ) {}

    /**
     * Show the application login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $this->authSessionService->attemptLogin(
            email: (string) $request->validated('email'),
            password: (string) $request->validated('password'),
            remember: (bool) $request->boolean('remember'),
        );

        return redirect()->intended(route('portal'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authSessionService->logout();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sesi.');
    }
}
