<?php

use \App\Http\Controllers\Api\AuthController;
use \App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->middleware('api')->group(function() {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:api')->group(function() {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/me', [AuthController::class, 'me']);
        });
    });
    Route::apiResource('profiles', ProfileController::class)->middleware('auth:api');
});
