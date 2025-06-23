<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PenangananController;

Route::post('login', [AuthController::class, 'login']);

Route::post('videos', [VideoController::class, 'store']);

Route::middleware('jwt.auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::get('me', [UserController::class, 'me']);

    Route::get('videos/stream/{filename}', [VideoController::class, 'streamVideo']);

    // Route Admin dan Dokter
    Route::middleware('role:admin|dokter')->group(function () {
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'delete']);
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete']);
        Route::post('users/{id}/restore', [UserController::class, 'restore']);

    });

    // Route Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('dokter', [UserController::class, 'getAllDokter']);
        Route::post('register/dokter', [UserController::class, 'registerDokter']);
    });

    // Route yang dapat diakses oleh Dokter dan Pasien
    Route::middleware('role:dokter|pasien')->group(function () {
        Route::get('penanganan', [PenangananController::class, 'index']);
        Route::get('penanganan/{id}', [PenangananController::class, 'show']);
        Route::get('penanganan/pasien', [PenangananController::class, 'getPenangananByPasien']);
    });

    // Route khusus Dokter
    Route::middleware('role:dokter')->group(function () {
        Route::get('videos', [VideoController::class, 'showAllVideos']);
        Route::get('videos/pasien', [UserController::class, 'showVideosByPasienId']);
        Route::post('videos/{videoId}/assign/{userId}', [VideoController::class, 'assignToUser']);
        Route::patch('videos/{videoId}', [VideoController::class, 'updateStatusVideo']);
        Route::put('videos/{videoId}/keterangan', [VideoController::class, 'updateKeterangan']);

        Route::get('pasien', [UserController::class, 'getAllPasien']);
        Route::post('register/pasien', [UserController::class, 'registerPasien']);

        Route::post('penanganan', [PenangananController::class, 'store']);
        Route::put('penanganan/{id}', [PenangananController::class, 'update']);
        Route::delete('penanganan/{id}', [PenangananController::class, 'delete']);
        Route::delete('penanganan/{id}/force-delete', [PenangananController::class, 'forceDelete']);
        Route::post('penanganan/{id}/assign/{userId}', [PenangananController::class, 'assignToUser']);
        Route::patch('penanganan/{id}', [PenangananController::class, 'updateStatusPenanganan']);
    });

});

