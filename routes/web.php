<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Univ IAM
|--------------------------------------------------------------------------
*/

// Guest Routes (Authentication)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// Authenticated Routes (SSO Portal & Session Management)
Route::middleware('auth')->group(function (): void {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', fn () => redirect()->route('portal'))->name('home');

    // Admin Master Data Management (RBAC: Super Admin & Admin SDM)
    Route::middleware('role:super_admin,admin_sdm')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update_status');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

