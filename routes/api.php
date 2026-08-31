<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use \App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

$notFound = fn(Request $request) => response()->json([
    'success' => false,
    'message' => 'Recurso não encontrado.',
    'data' => null,
], 404);

Route::prefix('v1')->group(function () use ($notFound) {
    Route::prefix('auth')->middleware('api')->group(function() use ($notFound) {
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/refresh', [AuthController::class, 'refresh']);

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('jwt')->group(function() use ($notFound) {
            Route::get('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);

            Route::apiResource('/users', UserController::class)
                ->missing($notFound);
        });
    });

    Route::middleware('auth:api')->group(function () {
        Route::apiResource('profiles', ProfileController::class);
    });
});
