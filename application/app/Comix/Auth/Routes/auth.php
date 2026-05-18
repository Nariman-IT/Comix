<?php

declare(strict_types=1);

use App\Comix\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/auth')
    ->middleware('api')
    ->group(callback: static function (): void {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,5');
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgot']);
        Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:3,1');
});
