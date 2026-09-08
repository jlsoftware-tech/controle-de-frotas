<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$notFound = function (Request $request) {
    return ($request->user('api') !== null)
        ? ApiResponder::error('Recurso não encontrado.', 404)
        : ApiResponder::error('Não autenticado.', 401);
};

Route::prefix('v1')->group(function () use ($notFound) {
    Route::prefix('auth')->middleware('api')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/refresh', [AuthController::class, 'refresh']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::get('/logout', [AuthController::class, 'logout'])->middleware('jwt');
    });

    Route::middleware('jwt')->group(function () use ($notFound) {
        Route::get('/me', [AuthController::class, 'me']);
        Route::apiResource('/users', UserController::class)->missing($notFound);
        Route::apiResource('/profiles', ProfileController::class)->missing($notFound);
        Route::apiResource('/permissions', PermissionController::class)->only(['index', 'show']);
    });
});
