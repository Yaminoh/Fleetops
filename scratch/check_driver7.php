<?php
// Check if driver 7 has active dispatches
$dispatches = DB::table('dispatches')
    ->whereIn('status', ['Scheduled', 'Active'])
    ->where('driver_id', 7)
    ->get(['id', 'dispatch_no', 'status', 'driver_id']);

echo "Driver 7 active dispatches:\n";
foreach ($dispatches as $d) {
    echo "  Dispatch #{$d->id} ({$d->dispatch_no}) - Status: {$d->status}\n";
}

if ($dispatches->isEmpty()) {
    echo "  NONE - Driver 7 should be available!\n";
}

// Show all active/scheduled dispatches
echo "\nAll Scheduled/Active Dispatches:\n";
$all = DB::table('dispatches')
    ->whereIn('status', ['Scheduled', 'Active'])
    ->get(['id', 'dispatch_no', 'vehicle_id', 'driver_id', 'status', 'destination']);
foreach ($all as $d) {
    echo "  [{$d->id}] {$d->dispatch_no} | V:{$d->vehicle_id} D:{$d->driver_id} | {$d->status} | {$d->destination}\n";
}
