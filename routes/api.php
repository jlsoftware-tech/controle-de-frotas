<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretariatController;
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
        Route::get('/logout', [AuthController::class, 'logout'])->middleware('jwt');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('jwt')->group(function () use ($notFound) {
        Route::prefix('/users')->group(function () {
            Route::get('/sidebar', [UserController::class, 'sidebar']);
            Route::get('/profile', [UserController::class, 'profile']);
            Route::get('/permissions', [UserController::class, 'permissions']);
        });
        Route::apiResource('/users', UserController::class)
            ->whereNumber('user')
            ->missing($notFound);

        Route::apiResource('/secretariats', SecretariatController::class)
            ->whereNumber('secretariat')
            ->missing($notFound);
        Route::apiResource('/profiles', ProfileController::class)->missing($notFound);
        Route::apiResource('/permissions', PermissionController::class)->only(['index', 'show']);
    });
});
