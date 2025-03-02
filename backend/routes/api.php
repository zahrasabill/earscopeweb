<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


Route::post('login', [AuthController::class, 'login']);

Route::middleware('jwt.auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // Route untuk admin
    Route::middleware('role:admin')->group(function () {
        Route::post('register', [UserController::class, 'register']);
        Route::get('users', [UserController::class, 'getAllUsers']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::delete('users/{id}', [UserController::class, 'delete']);
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete']);
        Route::post('users/{id}/restore', [UserController::class, 'restore']);
    });
    
    // Route untuk admin dan dokter
    Route::middleware('role:admin|dokter')->group(function () {
        Route::post('register', [UserController::class, 'register']);
        Route::get('users', [UserController::class, 'getAllUsers']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::get('users/{id}', [UserController::class, 'show']);
    });

    // Route tanpa pembatasan role
    Route::get('me', [UserController::class, 'me']);

});


