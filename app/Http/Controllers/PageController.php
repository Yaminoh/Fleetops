<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Dispatch;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        return $this->renderPage($page, '');
    }

    public function showUser(string $page): View
    {
        abort_unless(request()->user() && request()->user()->role !== 'Admin', 403, 'User access only.');

        return $this->renderPage($page, '/users');
    }

    public function activeDispatches(): JsonResponse
    {
        $user = request()->user();
        abort_unless($user, 401);

        $dispatches = Dispatch::query()
            ->with([
                'vehicle:id,vehicle_code,plate_number',
                'driver:id,user_id,name,employee_id',
                'driver.user:id,name',
                'tripRecords' => fn ($query) => $query
                    ->whereRaw('LOWER(TRIM(status)) <> ?', ['completed'])
                    ->orderByDesc('id'),
            ])
            ->whereRaw('LOWER(TRIM(dispatches.status)) = ?', ['active']);

        if ($user->role !== 'Admin') {
            $dispatches->whereHas('driver', fn ($query) => $query->where('user_id', $user->id));
        }

        return response()->json([
            'success' => true,
            'dispatches' => $dispatches
                ->orderByDesc('dispatches.updated_at')
                ->get()
                ->map(function (Dispatch $dispatch): array {
                    $trip = $dispatch->tripRecords->first();

                    return [
                        'dispatch_no' => $dispatch->dispatch_no,
                        'vehicle_id' => (int) $dispatch->vehicle_id,
                        'vehicle_code' => $dispatch->vehicle?->vehicle_code,
                        'plate_number' => $dispatch->vehicle?->plate_number,
                        'driver_name' => $dispatch->driver?->user?->name ?? $dispatch->driver?->name,
                        'employee_id' => $dispatch->driver?->employee_id,
                        'trip_record_id' => $trip?->id,
                        'origin' => $dispatch->origin ?: $trip?->origin,
                        'destination' => $dispatch->destination ?: $trip?->destination,
                        'status' => $dispatch->status,
                        'trip_status' => $trip?->status,
                    ];
                })
                ->values(),
        ]);
    }

    private function renderPage(string $page, string $basePath): View
    {
        $titles = [
            'dashboard' => 'Dashboard Overview', 'vehicles' => 'Vehicles Management', 'reservations' => 'Reservations',
            'drivers' => 'Drivers', 'fuel-logs' => 'Fuel Logs', 'cost-analytics' => 'Cost Analytics',
            'driver-analytics' => 'Driver & Cost Analytics', 'routes' => 'Routes', 'reports' => 'Reports',
            'settings' => 'Settings', 'usermanagement' => 'User Management', 'notifications' => 'Notifications',
        ];

        abort_unless(isset($titles[$page]), 404);

        $user = request()->user();
        $fuelLogs = [];
        if ($page === 'fuel-logs') {
            $fuelLogQuery = FuelLog::with(['user:id,name', 'vehicle:id,name,plate_number', 'driver.user:id,name']);
            if ($user->role !== 'Admin') {
                $fuelLogQuery->where('user_id', $user->id);
            }

            $fuelLogs = $fuelLogQuery->orderByDesc('logged_at')
                ->get()
                ->map(fn (FuelLog $log): array => [
                    'vehicle' => $log->vehicle->name ?? 'Unknown',
                    'plate_number' => $log->vehicle->plate_number ?? 'Unknown',
                    'driver' => $log->driver?->user?->name
                        ?? $log->driver?->name
                        ?? $log->user?->name
                        ?? 'No driver assigned',
                    'submitted_by' => $log->user->name ?? 'Legacy / deleted user',
                    'fuel_level_before' => $log->fuel_level_before,
                    'fuel_level_after' => $log->fuel_level_after,
                    'fuel_level_before_image' => $log->fuel_level_before_image,
                    'fuel_level_after_image' => $log->fuel_level_after_image,
                    'receipt_image' => $log->receipt_image,
                    'id' => $log->id,
                    'logged_at' => $log->logged_at->format('M d, Y'),
                    'liters' => number_format($log->liters, 1).'L',
                    'cost' => number_format($log->cost, 2),
                ])
                ->all();
        }
        $alertsQuery = DB::table('alerts')->orderByDesc('created_at');
        $unreadAlertsQuery = Alert::whereNull('read_at');
        $notificationsQuery = Alert::orderByDesc('created_at');
        if ($user->role !== 'Admin') {
            $alertsQuery->where('title', '<>', 'Fuel Logged');
            $unreadAlertsQuery->where('title', '<>', 'Fuel Logged');
            $notificationsQuery->where('title', '<>', 'Fuel Logged');
        }

        $vehicleCount = DB::table('vehicles')->count();
        $activeVehicleCount = DB::table('vehicles')->whereRaw('LOWER(status) IN (?, ?)', ['active', 'available'])->count();
        $maintenanceVehicleCount = DB::table('vehicles')->whereRaw('LOWER(status) = ?', ['maintenance'])->count();
        $reservationScope = DB::table('reservations');
        if (Schema::hasColumn('reservations', 'user_id')) {
            $reservationScope->leftJoin('users as requesters', 'reservations.user_id', '=', 'requesters.id')
                ->select('reservations.*', 'requesters.name as requester_name');
        }
        if ($user->role !== 'Admin' && Schema::hasColumn('reservations', 'user_id')) {
            $reservationScope->where('reservations.user_id', $user->id);
        } elseif ($user->role !== 'Admin') {
            // Until the owner migration is applied, don't expose other users' requests.
            $reservationScope->whereRaw('1 = 0');
        }
        $myReservations = (clone $reservationScope);
        $pendingReservationCount = (clone $reservationScope)->whereRaw('LOWER(reservations.status) = ?', ['pending'])->count('reservations.id');

        $finance = $this->buildFinanceData();

        // The original database used reservation_date; the workflow module uses
        // requested_date. Support both while the compatibility migration runs.
        $reservationDateColumn = Schema::hasColumn('reservations', 'requested_date')
            ? 'requested_date'
            : 'reservation_date';

        $dashboard = [
            'page' => $page,
            'isAdmin' => $user->role === 'Admin',
            'title' => $titles[$page],
            'basePath' => $basePath,
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
            'vehicleAvailability' => $page === 'dashboard' ? $this->buildVehicleAvailability() : [],
            'reservations' => (clone $myReservations)
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
            'alerts' => $alertsQuery->limit(4)
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
            'userRoles' => ['User', 'Admin'],
            'vehicleOptions' => Vehicle::orderBy('plate_number')->get(['id', 'name', 'type', 'plate_number', 'fuel_level'])->all(),
            'driverOptions' => $page === 'fuel-logs'
                ? DB::table('drivers')
                    ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
                    ->select('drivers.id', 'drivers.employee_id')
                    ->selectRaw("COALESCE(NULLIF(users.name, ''), NULLIF(drivers.name, ''), 'Driver') as display_name")
                    ->when($user->role !== 'Admin', fn ($query) => $query->where('drivers.user_id', $user->id))
                    ->when(
                        $user->role === 'Admin',
                        fn ($query) => $query->orderBy('display_name'),
                        fn ($query) => $query->orderBy('drivers.id'),
                    )
                    ->get()
                : [],
            'fuelLogs' => $fuelLogs,
            'quickActions' => $user->role === 'Admin'
                ? ['Add Vehicle', 'Log Fuel', 'Create Reservation', 'Report Incident', 'Dispatch Log', 'View Routes', 'Check Drivers', 'Settings']
                : ['View Vehicles', 'Review Fuel Logs', 'View Reservations', 'View Routes', 'Review Drivers', 'View Reports', 'View Notifications', 'Settings'],
            'finance' => $finance,
            'unreadNotifications' => $unreadAlertsQuery->count(),
            'notifications' => $page === 'notifications'
                ? $notificationsQuery->limit(30)
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

        if ($page === 'reservations') {
            $dashboard['pendingReservations'] = (clone $reservationScope)
                ->whereRaw('LOWER(reservations.status) = ?', ['pending'])
                ->orderByDesc('reservations.created_at')
                ->get();
            $dashboard['approvedReservations'] = (clone $reservationScope)
                ->whereRaw('LOWER(reservations.status) = ?', ['approved'])
                ->orderByDesc('reservations.approved_at')
                ->get();
            $dashboard['rejectedReservations'] = (clone $reservationScope)
                ->whereRaw('LOWER(reservations.status) = ?', ['rejected'])
                ->orderByDesc('reservations.updated_at')
                ->get();
            $dashboard['dispatchedReservations'] = (clone $reservationScope)
                ->whereRaw('LOWER(reservations.status) = ?', ['dispatched'])
                ->orderByDesc('reservations.updated_at')
                ->get();

            $dispatches = DB::table('dispatches')
                ->leftJoin('vehicles', 'dispatches.vehicle_id', '=', 'vehicles.id')
                ->leftJoin('drivers', 'dispatches.driver_id', '=', 'drivers.id')
                ->leftJoin('reservations', 'dispatches.reservation_id', '=', 'reservations.id')
                ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
                ->select('dispatches.*', 'vehicles.plate_number', 'users.name as driver_name');

            if ($user->role !== 'Admin') {
                if (Schema::hasColumn('reservations', 'user_id')) {
                    $dispatches->where('reservations.user_id', $user->id);
                } else {
                    $dispatches->whereRaw('1 = 0');
                }
            }

            $dashboard['scheduledDispatches'] = (clone $dispatches)
                ->whereRaw('LOWER(dispatches.status) = ?', ['scheduled'])
                ->orderByDesc('dispatches.created_at')
                ->get();
            $dashboard['activeDispatches'] = (clone $dispatches)
                ->whereRaw('LOWER(dispatches.status) = ?', ['active'])
                ->orderByDesc('dispatches.updated_at')
                ->get();
            $dashboard['availableVehicles'] = DB::table('vehicles')
                ->whereRaw('LOWER(status) IN (?, ?)', ['active', 'available'])
                ->orderBy('plate_number')
                ->get();
            $dashboard['availableDrivers'] = DB::table('drivers')
                ->join('users', 'drivers.user_id', '=', 'users.id')
                ->whereRaw('LOWER(drivers.status) = ?', ['active'])
                ->orderBy('users.name')
                ->get(['drivers.id', 'drivers.employee_id', 'users.name as driver_name']);
        }

        if ($page === 'vehicles') {
            $dashboard['vehicles'] = Vehicle::orderBy('plate_number')->get();

            // Generate the next vehicle code from the configured format (VHC-001, VHC-002, ...).
            $existingCodes = Vehicle::query()
                ->where('vehicle_code', 'like', 'VHC-%')
                ->pluck('vehicle_code');
            $nextVehicleNumber = 0;
            foreach ($existingCodes as $code) {
                if (preg_match('/^VHC-(\d+)$/i', (string) $code, $matches)) {
                    $nextVehicleNumber = max($nextVehicleNumber, (int) $matches[1]);
                }
            }
            $dashboard['nextVehicleCode'] = 'VHC-' . str_pad($nextVehicleNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return view('layout', compact('dashboard'));
    }

    private function buildVehicleAvailability(): array
    {
        $counts = ['Available' => 0, 'Booked' => 0, 'Maintenance' => 0, 'Delayed' => 0, 'Unavailable' => 0];
        $statuses = DB::table('vehicles')
            ->selectRaw('LOWER(TRIM(status)) as vehicle_status, COUNT(*) as vehicle_count')
            ->groupByRaw('LOWER(TRIM(status))')
            ->get();

        foreach ($statuses as $status) {
            $category = match ($status->vehicle_status) {
                'active', 'available' => 'Available',
                'reserved', 'booked', 'in transit' => 'Booked',
                'maintenance' => 'Maintenance',
                'delayed' => 'Delayed',
                default => 'Unavailable',
            };
            $counts[$category] += (int) $status->vehicle_count;
        }

        return ['counts' => $counts, 'total' => array_sum($counts)];
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

        return [
            'cards' => [
                ['key' => 'total_transport_cost', 'title' => 'Total Transport Cost', 'value' => $totalThisMonth, 'change' => $pctChange($totalThisMonth, $totalLastMonth), 'meta' => 'vs last month'],
                ['key' => 'fuel_expenses', 'title' => 'Fuel Expenses', 'value' => $fuelThisMonth, 'change' => $pctChange($fuelThisMonth, $fuelLastMonth), 'meta' => 'vs last month'],
                ['key' => 'maintenance_costs', 'title' => 'Maintenance Costs', 'value' => $maintThisMonth, 'change' => $pctChange($maintThisMonth, $maintLastMonth), 'meta' => 'vs last month'],
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
