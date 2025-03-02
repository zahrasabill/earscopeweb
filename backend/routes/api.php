<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;


Route::post('login', [AuthController::class, 'login']);

Route::post('videos', [VideoController::class, 'store']); // Upload video

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

        Route::post('videos/{videoId}/assign/{userId}', [VideoController::class, 'assignToUser']); // Assign video ke user
        Route::get('videos', [VideoController::class, 'index']); // Lihat semua video
        Route::get('videos/{id}', [VideoController::class, 'show']); // Lihat detail video
    });
    
    // Route untuk admin dan dokter
    Route::middleware('role:admin|dokter')->group(function () {
        Route::post('register', [UserController::class, 'register']);
        Route::get('users', [UserController::class, 'getAllUsers']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::get('users/{id}', [UserController::class, 'show']);

        Route::post('videos/{videoId}/assign/{userId}', [VideoController::class, 'assignToUser']); // Assign video ke user
        Route::get('videos', [VideoController::class, 'index']); // Lihat semua video
        Route::get('videos/{id}', [VideoController::class, 'show']); // Lihat detail video
    });

    // Route tanpa pembatasan role
    Route::get('me', [UserController::class, 'me']);

});


