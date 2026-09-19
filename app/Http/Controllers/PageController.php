<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $finance = $this->buildFinanceData();

        // The original database used reservation_date; the workflow module uses
        // requested_date. Support both while the compatibility migration runs.
        $reservationDateColumn = Schema::hasColumn('reservations', 'requested_date')
            ? 'requested_date'
            : 'reservation_date';

        $dashboard = [
            'page' => $page,
            'title' => $titles[$page],
            'basePath' => '',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'title' => $user->role ?? 'Staff',
                'role' => $user->role ?? 'Staff',
                'initials' => strtoupper(substr($user->name, 0, 2)),
                'preferences' => $user->preferences_with_defaults,
            ],
            'stats' => [
                ['title' => 'Total Vehicles', 'value' => $vehicleCount, 'meta' => 'Live database total', 'positive' => true, 'currency' => false],
                ['title' => 'Active Vehicles', 'value' => $activeVehicleCount, 'meta' => 'Currently active', 'positive' => true, 'currency' => false],
                ['title' => 'Vehicles in Maintenance', 'value' => $maintenanceVehicleCount, 'meta' => 'Requires attention', 'positive' => false, 'currency' => false],
                ['title' => 'Available Dispatches', 'value' => $pendingReservationCount, 'meta' => 'Pending reservations', 'positive' => true, 'currency' => false],
                ['title' => 'Transport Costs This Month', 'value' => $finance['totals']['total_this_month'], 'meta' => 'Fuel + maintenance, live', 'positive' => true, 'currency' => true, 'currency_symbol' => 'PHP '],
            ],
            'reservations' => DB::table('reservations')
                ->orderByDesc($reservationDateColumn)
                ->limit(5)
                ->get()
                ->map(fn (object $reservation): array => [
                    'name' => $reservation->driver_name ?? $reservation->employee_id ?? 'Unassigned',
                    'vehicle' => $reservation->vehicle_type,
                    'date' => $reservation->{$reservationDateColumn},
                    'duration' => ($reservation->duration_days ?? 1).' day'.(($reservation->duration_days ?? 1) === 1 ? '' : 's'),
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
            'userRoles' => ['Admin', 'Manager', 'Dispatcher', 'Accountant', 'Staff'],
            'vehicleOptions' => Vehicle::orderBy('name')->get(['id', 'name', 'type'])->all(),
            'fuelLogs' => $page === 'fuel-logs'
                ? FuelLog::with('vehicle:id,name')
                    ->orderByDesc('logged_at')
                    ->limit(30)
                    ->get()
                    ->map(fn (FuelLog $log): array => [
                        'vehicle' => $log->vehicle->name ?? 'Unknown',
                        'logged_at' => $log->logged_at->format('M d, Y'),
                        'liters' => number_format($log->liters, 1).'L',
                        'cost' => number_format($log->cost, 2),
                    ])
                    ->all()
                : [],
            'quickActions' => ['Add Vehicle', 'Log Fuel', 'Create Reservation', 'Report Incident', 'Dispatch Log', 'View Routes', 'Check Drivers', 'Settings'],
            'finance' => $finance,
            'unreadNotifications' => Alert::whereNull('read_at')->count(),
            'notifications' => $page === 'notifications'
                ? Alert::orderByDesc('created_at')
                    ->limit(30)
                    ->get()
                    ->map(fn (Alert $alert): array => [
                        'id' => $alert->id,
                        'icon' => $alert->icon,
                        'title' => $alert->title,
                        'detail' => $alert->detail,
                        'severity' => $alert->severity,
                        'read' => $alert->read_at !== null,
                        'time' => $alert->created_at->diffForHumans(),
                    ])
                    ->all()
                : [],
        ];

        return view('layout', compact('dashboard'));
    }

    private function buildFinanceData(): array
    {
        $now = Carbon::now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $startOfThisMonth->copy()->subSecond();

        $fuelThisMonth = (float) FuelLog::whereBetween('logged_at', [$startOfThisMonth, $now])->sum('cost');
        $fuelLastMonth = (float) FuelLog::whereBetween('logged_at', [$startOfLastMonth, $endOfLastMonth])->sum('cost');
        $maintThisMonth = (float) MaintenanceRecord::whereBetween('serviced_at', [$startOfThisMonth, $now])->sum('cost');
        $maintLastMonth = (float) MaintenanceRecord::whereBetween('serviced_at', [$startOfLastMonth, $endOfLastMonth])->sum('cost');

        $totalThisMonth = $fuelThisMonth + $maintThisMonth;
        $totalLastMonth = $fuelLastMonth + $maintLastMonth;

        $pctChange = function (float $current, float $previous): ?float {
            if ($previous <= 0.0) {
                return null;
            }

            return round((($current - $previous) / $previous) * 100, 1);
        };

        $trendLabels = [];
        $trendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $fuel = (float) FuelLog::whereBetween('logged_at', [$monthStart, $monthEnd])->sum('cost');
            $maint = (float) MaintenanceRecord::whereBetween('serviced_at', [$monthStart, $monthEnd])->sum('cost');

            $trendLabels[] = $monthStart->format('M');
            $trendValues[] = round($fuel + $maint, 2);
        }

        $firstHalfAvg = array_sum(array_slice($trendValues, 0, 3)) / 3;
        $secondHalfAvg = array_sum(array_slice($trendValues, 3, 3)) / 3;

        if ($firstHalfAvg > $secondHalfAvg) {
            $projectedSavings = round(($firstHalfAvg - $secondHalfAvg) * 3, 2);
            $savingsMeta = 'Based on your declining 6-month cost trend';
        } else {
            $projectedSavings = 0.0;
            $savingsMeta = 'No declining cost trend detected yet';
        }

        return [
            'cards' => [
                ['key' => 'total_transport_cost', 'title' => 'Total Transport Cost', 'value' => $totalThisMonth, 'change' => $pctChange($totalThisMonth, $totalLastMonth), 'meta' => 'vs last month'],
                ['key' => 'fuel_expenses', 'title' => 'Fuel Expenses', 'value' => $fuelThisMonth, 'change' => $pctChange($fuelThisMonth, $fuelLastMonth), 'meta' => 'vs last month'],
                ['key' => 'maintenance_costs', 'title' => 'Maintenance Costs', 'value' => $maintThisMonth, 'change' => $pctChange($maintThisMonth, $maintLastMonth), 'meta' => 'vs last month'],
                ['key' => 'projected_savings', 'title' => 'Projected Savings', 'value' => $projectedSavings, 'meta' => $savingsMeta],
            ],
            'trend' => [
                'labels' => $trendLabels,
                'values' => $trendValues,
            ],
            'breakdown' => [
                'labels' => ['Fuel', 'Maintenance'],
                'values' => [round($fuelThisMonth, 2), round($maintThisMonth, 2)],
                'colors' => ['#ef4444', '#f59e0b'],
            ],
            'totals' => [
                'fuel_this_month' => $fuelThisMonth,
                'maintenance_this_month' => $maintThisMonth,
                'total_this_month' => $totalThisMonth,
            ],
        ];
    }
}
