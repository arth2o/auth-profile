<?php

use Arth2o\AuthProfile\Http\Controllers\AuthController;
use Arth2o\AuthProfile\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/custom-auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::get('/profile', [ProfileController::class, 'show'])
        ->middleware('custom-token-auth');
});
