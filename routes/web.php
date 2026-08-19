<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [PageController::class, 'show'])->defaults('page', 'dashboard')->name('dashboard');
    foreach (['vehicles', 'reservations', 'drivers', 'fuel-logs', 'cost-analytics', 'driver-analytics', 'routes', 'reports', 'settings', 'usermanagement', 'notifications'] as $page) {
        Route::get('/'.$page, [PageController::class, 'show'])->defaults('page', $page)->name($page);
    }
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
