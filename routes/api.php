<?php

use App\Http\Controllers\V1\Auth\LoginController;
use App\Http\Controllers\V1\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', RegisterController::class);
    Route::post('auth/login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', fn(Request $request) => $request->user());
    });
});
