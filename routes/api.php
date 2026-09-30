<?php

declare(strict_types=1);

use App\Http\Controllers\Api\SsoUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::middleware('auth:api')->group(function (): void {
    Route::get('/user', [SsoUserController::class, 'show'])->name('api.user');
});
