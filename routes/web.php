<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\FleetCostController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VehicleController;
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

    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');

    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::put('/notifications/{alert}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::post('/fuel-logs', [FleetCostController::class, 'storeFuelLog'])->name('fuel-logs.store');
    Route::post('/cost-analytics/maintenance', [FleetCostController::class, 'storeMaintenance'])->name('maintenance.store');

    // Operations workflow: reserve a vehicle, create a dispatch, then track its progress.
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservations/{reservation}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/dispatches', [DispatchController::class, 'store'])->name('dispatches.store');
    Route::post('/dispatches/convert/{reservation}', [DispatchController::class, 'convert'])->name('dispatches.convert');
    Route::post('/dispatches/{dispatch}/status', [DispatchController::class, 'updateStatus'])->name('dispatches.update-status');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::post('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
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
