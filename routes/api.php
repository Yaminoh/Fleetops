<?php

use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Authentication
|--------------------------------------------------------------------------
*/

Route::post('/mobile/login', [
    MobileAuthController::class,
    'login',
]);

Route::post('/mobile/verify-2fa', [
    MobileAuthController::class,
    'verifyTwoFactor',
]);

Route::post('/mobile/resend-2fa', [
    MobileAuthController::class,
    'resendTwoFactor',
]);

/*
|--------------------------------------------------------------------------
| Authenticated Mobile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/mobile/user', [
        MobileAuthController::class,
        'user',
    ]);

    Route::post('/mobile/logout', [
        MobileAuthController::class,
        'logout',
    ]);

});

Route::post('/location/update', [ApiController::class, 'updateLocation']);

/*
|--------------------------------------------------------------------------
| Existing Sanctum User Route
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');