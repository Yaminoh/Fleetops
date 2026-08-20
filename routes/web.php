<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->name('register.store');

    Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify');
    Route::post('/two-factor-challenge/resend', [AuthController::class, 'resendTwoFactor'])->name('two-factor.resend');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [PageController::class, 'show'])->defaults('page', 'dashboard')->name('dashboard');
    foreach (['vehicles', 'reservations', 'drivers', 'fuel-logs', 'cost-analytics', 'driver-analytics', 'routes', 'reports', 'settings', 'usermanagement', 'notifications'] as $page) {
        Route::get('/'.$page, [PageController::class, 'show'])->defaults('page', $page)->name($page);
    }

    Route::post('/usermanagement', [UserManagementController::class, 'store'])->name('usermanagement.store');
    Route::put('/usermanagement/{user}', [UserManagementController::class, 'update'])->name('usermanagement.update');
    Route::delete('/usermanagement/{user}', [UserManagementController::class, 'destroy'])->name('usermanagement.destroy');
});
Route::prefix('api')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function (): void {
    Route::get('/vehicles/live', [ApiController::class, 'getLiveVehicles']);
    Route::get('/trips/active', [ApiController::class, 'getActiveTrips']);
    Route::get('/trip/{tripId}/route', [ApiController::class, 'getTripRoute'])->whereNumber('tripId');
    Route::get('/trip/{tripId}/eta', [ApiController::class, 'getTripEta'])->whereNumber('tripId');
    Route::post('/trip/start', [ApiController::class, 'startTrip']);
    Route::post('/location/update', [ApiController::class, 'updateLocation']);
    Route::get('/analytics/dashboard', [ApiController::class, 'getDashboardAnalytics']);
    Route::get('/notifications', [ApiController::class, 'getNotifications']);
    Route::post('/integration/system', [ApiController::class, 'handleSystemIntegration']);
    Route::get('/driver/dashboard', [ApiController::class, 'getDriverDashboard']);
    Route::get('/driver/rankings', [ApiController::class, 'getDriverRankings']);
    Route::get('/driver/analytics', [ApiController::class, 'getDriverAnalytics']);
    Route::get('/driver/reports', [ApiController::class, 'getDriverReports']);
    Route::get('/driver/{driverId}/performance', [ApiController::class, 'getDriverPerformance'])->whereNumber('driverId');
});
