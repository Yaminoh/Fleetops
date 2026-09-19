<?php
echo "=== FleetOps Database State ===\n\n";

echo "--- VEHICLES ---\n";
$total = DB::table('vehicles')->count();
$active = DB::table('vehicles')->where('status', 'Active')->count();
$maint = DB::table('vehicles')->where('status', 'Maintenance')->count();
echo "Total: $total | Active: $active | Maintenance: $maint\n";

echo "\n--- DRIVERS ---\n";
$total = DB::table('drivers')->count();
$active = DB::table('drivers')->where('status', 'Active')->count();
echo "Total: $total | Active: $active\n";

echo "\n--- RESERVATIONS ---\n";
$total = DB::table('reservations')->count();
$pending = DB::table('reservations')->where('status', 'Pending')->count();
$approved = DB::table('reservations')->where('status', 'Approved')->count();
$dispatched = DB::table('reservations')->where('status', 'Dispatched')->count();
$rejected = DB::table('reservations')->where('status', 'Rejected')->count();
echo "Total: $total | Pending: $pending | Approved: $approved | Dispatched: $dispatched | Rejected: $rejected\n";

echo "\n--- DISPATCHES ---\n";
$total = DB::table('dispatches')->count();
$scheduled = DB::table('dispatches')->where('status', 'Scheduled')->count();
$activeDis = DB::table('dispatches')->where('status', 'Active')->count();
$completed = DB::table('dispatches')->where('status', 'Completed')->count();
echo "Total: $total | Scheduled: $scheduled | Active: $activeDis | Completed: $completed\n";

echo "\n--- BUSY RESOURCES ---\n";
$busyVehicles = DB::table('dispatches')->whereIn('status', ['Scheduled', 'Active'])->pluck('vehicle_id')->unique();
$busyDrivers = DB::table('dispatches')->whereIn('status', ['Scheduled', 'Active'])->pluck('driver_id')->unique();
echo "Busy Vehicles: " . $busyVehicles->count() . " (IDs: " . $busyVehicles->implode(', ') . ")\n";
echo "Busy Drivers: " . $busyDrivers->count() . " (IDs: " . $busyDrivers->implode(', ') . ")\n";

echo "\n--- AVAILABLE FOR DISPATCH ---\n";
$availVehicles = DB::table('vehicles')->where('status', 'Active')->whereNotIn('id', $busyVehicles->all())->get(['id', 'plate_number', 'type']);
$availDrivers = DB::table('drivers')
    ->join('users', 'drivers.user_id', '=', 'users.id')
    ->where('drivers.status', 'Active')
    ->whereNotIn('drivers.id', $busyDrivers->all())
    ->get(['drivers.id', 'users.name as driver_name']);

echo "Available Vehicles (" . count($availVehicles) . "):\n";
foreach ($availVehicles as $v) {
    echo "  [{$v->id}] {$v->plate_number} ({$v->type})\n";
}
echo "Available Drivers (" . count($availDrivers) . "):\n";
foreach ($availDrivers as $d) {
    echo "  [{$d->id}] {$d->driver_name}\n";
}

echo "\n--- ALL VEHICLES ---\n";
$allV = DB::table('vehicles')->get(['id', 'plate_number', 'type', 'status']);
foreach ($allV as $v) {
    echo "  [{$v->id}] {$v->plate_number} | {$v->type} | {$v->status}\n";
}

echo "\n--- ALL DRIVERS ---\n";
$allD = DB::table('drivers')
    ->join('users', 'drivers.user_id', '=', 'users.id')
    ->get(['drivers.id', 'users.name', 'drivers.status']);
foreach ($allD as $d) {
    echo "  [{$d->id}] {$d->name} | {$d->status}\n";
}

echo "\nDone.\n";
