<?php

use App\Http\Controllers\CheckController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api')->group(function () {
    Route::get('/', [CheckController::class, 'index']);
    Route::get('/checkin', [CheckController::class, 'in']);
    Route::get('/checkout', [CheckController::class, 'out']);
    Route::put('/checkin/{uuid}', [CheckController::class, 'update']);
    Route::put('/password', [PasswordController::class, 'update']);
    Route::get('/logout', [AuthController::class, 'logout']);
});
