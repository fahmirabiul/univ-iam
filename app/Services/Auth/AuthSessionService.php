<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthSessionService
{
    /**
     * Attempt to authenticate a user using email and password.
     *
     * @throws ValidationException
     */
    public function attemptLogin(string $email, string $password, bool $remember = false): void
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Kombinasi email dan kata sandi tidak cocok dengan data kami.'],
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator SDM.'],
            ]);
        }

        request()->session()->regenerate();
    }

    /**
     * Log the user out of the application and invalidate the session.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
