<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $titles = [
            'dashboard' => 'Dashboard Overview', 'vehicles' => 'Vehicles Management', 'reservations' => 'Reservations',
            'drivers' => 'Drivers', 'fuel-logs' => 'Fuel Logs', 'cost-analytics' => 'Cost Analytics',
            'driver-analytics' => 'Driver & Cost Analytics', 'routes' => 'Routes', 'reports' => 'Reports',
            'settings' => 'Settings', 'usermanagement' => 'User Management', 'notifications' => 'Notifications',
        ];

        abort_unless(isset($titles[$page]), 404);

        $user = request()->user();
        $vehicleCount = DB::table('vehicles')->count();
        $activeVehicleCount = DB::table('vehicles')->whereRaw('LOWER(status) = ?', ['active'])->count();
        $maintenanceVehicleCount = DB::table('vehicles')->whereRaw('LOWER(status) = ?', ['maintenance'])->count();
        $pendingReservationCount = DB::table('reservations')->whereRaw('LOWER(status) = ?', ['pending'])->count();

        $dashboard = [
            'page' => $page,
            'title' => $titles[$page],
            'basePath' => '',
            'user' => [
                'name' => $user->name,
                'title' => $user->role ?? 'Staff',
                'initials' => strtoupper(substr($user->name, 0, 2)),
            ],
            'stats' => [
                ['title' => 'Total Vehicles', 'value' => $vehicleCount, 'meta' => 'Live database total', 'positive' => true, 'currency' => false],
                ['title' => 'Active Vehicles', 'value' => $activeVehicleCount, 'meta' => 'Currently active', 'positive' => true, 'currency' => false],
                ['title' => 'Vehicles in Maintenance', 'value' => $maintenanceVehicleCount, 'meta' => 'Requires attention', 'positive' => false, 'currency' => false],
                ['title' => 'Available Dispatches', 'value' => $pendingReservationCount, 'meta' => 'Pending reservations', 'positive' => true, 'currency' => false],
                ['title' => 'Transport Costs This Month', 'value' => 0, 'meta' => 'Fuel logs not connected yet', 'positive' => true, 'currency' => true, 'currency_symbol' => 'PHP '],
            ],
            'reservations' => DB::table('reservations')
                ->orderByDesc('reservation_date')
                ->limit(5)
                ->get()
                ->map(fn (object $reservation): array => [
                    'name' => $reservation->driver_name,
                    'vehicle' => $reservation->vehicle_type,
                    'date' => $reservation->reservation_date,
                    'duration' => $reservation->duration_days.' day'.($reservation->duration_days === 1 ? '' : 's'),
                    'status' => ucfirst($reservation->status),
                ])
                ->all(),
            'alerts' => DB::table('alerts')
                ->orderByDesc('created_at')
                ->limit(4)
                ->get(['icon', 'title', 'detail'])
                ->map(fn (object $alert): array => (array) $alert)
                ->all(),
            'drivers' => DB::table('drivers')
                ->orderByDesc('score')
                ->limit(5)
                ->get()
                ->map(fn (object $driver): array => [
                    'name' => $driver->name,
                    'role' => $driver->role,
                    'dispatches' => $driver->dispatch_count.' dispatches',
                    'score' => number_format((float) $driver->score, 1),
                ])
                ->all(),
            'users' => $page === 'usermanagement'
                ? User::orderBy('name')->get(['id', 'name', 'email', 'role', 'status'])->all()
                : [],
            'quickActions' => ['Add Vehicle', 'Log Fuel', 'Create Reservation', 'Report Incident', 'Dispatch Log', 'View Routes', 'Check Drivers', 'Settings'],
        ];

        return view('layout', compact('dashboard'));
    }
}
