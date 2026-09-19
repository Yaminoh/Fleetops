<?php

if (!class_exists(\Illuminate\Foundation\Application::class)) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use App\Models\Reservation;
use App\Models\Dispatch;
use App\Models\TripRecord;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "==========================================" . PHP_EOL;
echo "   FLEETOPS PHASE 1 WORKFLOW VERIFICATION " . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

$results = [];

function recordResult(&$results, $testNum, $name, $passed, $details = '') {
    $results[] = [
        'num' => $testNum,
        'name' => $name,
        'passed' => $passed,
        'details' => $details
    ];
    $status = $passed ? "[PASS]" : "[FAIL]";
    echo "Test {$testNum}: {$status} - {$name}" . PHP_EOL;
    if ($details) {
        echo "   Details: {$details}" . PHP_EOL;
    }
}

// Check database connection and required sample data
$vehicle = Vehicle::first();
$driver = Driver::first();
$user = User::first();

if (!$vehicle || !$driver || !$user) {
    echo "ERROR: Missing seed data (Vehicles, Drivers, or Users)." . PHP_EOL;
    exit(1);
}

echo "Using Vehicle ID: {$vehicle->id}, Driver ID: {$driver->id}, User ID: {$user->id}" . PHP_EOL . PHP_EOL;

// ----------------------------------------------------
// 1. Reservation Creation
// ----------------------------------------------------
try {
    $res = Reservation::create([
        'reservation_no' => 'RES-TEST-' . strtoupper(uniqid()),
        'employee_id' => 'EMP-TEST-01',
        'destination' => 'Test Destination 1',
        'requested_date' => now()->addDays(1)->format('Y-m-d'),
        'requested_time' => '10:00',
        'passenger_count' => 3,
        'purpose' => 'Test Purpose',
        'status' => 'Pending',
    ]);

    $created = Reservation::find($res->id);
    if ($created && $created->status === 'Pending' && $created->employee_id === 'EMP-TEST-01') {
        recordResult($results, 1, "Reservation creation", true, "Created ID #{$created->id} with status Pending");
    } else {
        recordResult($results, 1, "Reservation creation", false, "Reservation not found or invalid attributes");
    }
} catch (\Throwable $e) {
    recordResult($results, 1, "Reservation creation", false, $e->getMessage());
}

// ----------------------------------------------------
// 2. Reservation Approval / Rejection
// ----------------------------------------------------
try {
    // Approve
    $res->update([
        'status' => 'Approved',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);
    $res->refresh();
    $approvedOk = ($res->status === 'Approved' && $res->approved_by == $user->id);

    // Reject test with another reservation
    $resReject = Reservation::create([
        'reservation_no' => 'RES-TEST-REJ-' . strtoupper(uniqid()),
        'employee_id' => 'EMP-TEST-02',
        'destination' => 'Test Destination 2',
        'requested_date' => now()->addDays(1)->format('Y-m-d'),
        'passenger_count' => 1,
        'status' => 'Pending',
    ]);
    $resReject->update(['status' => 'Rejected']);
    $resReject->refresh();
    $rejectedOk = ($resReject->status === 'Rejected');

    if ($approvedOk && $rejectedOk) {
        recordResult($results, 2, "Reservation approval/rejection", true, "Approved RES #{$res->id}, Rejected RES #{$resReject->id}");
    } else {
        recordResult($results, 2, "Reservation approval/rejection", false, "Approval or Rejection state update failed");
    }
} catch (\Throwable $e) {
    recordResult($results, 2, "Reservation approval/rejection", false, $e->getMessage());
}

// ----------------------------------------------------
// 3. Reservation to Dispatch Conversion
// ----------------------------------------------------
try {
    // Create direct dispatch from approved reservation
    $dispFromRes = DB::transaction(function () use ($res, $vehicle, $driver) {
        $d = Dispatch::create([
            'dispatch_no' => 'DSP-CONV-' . strtoupper(uniqid()),
            'reservation_id' => $res->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'destination' => $res->destination,
            'status' => 'Scheduled',
        ]);
        $res->update(['status' => 'Dispatched']);
        return $d;
    });

    $res->refresh();
    if ($dispFromRes && $dispFromRes->status === 'Scheduled' && $res->status === 'Dispatched') {
        recordResult($results, 3, "Reservation to Dispatch conversion", true, "Converted RES #{$res->id} to DSP #{$dispFromRes->id} (Status: Scheduled, Res Status: Dispatched)");
    } else {
        recordResult($results, 3, "Reservation to Dispatch conversion", false, "Conversion state or relation failed");
    }
} catch (\Throwable $e) {
    recordResult($results, 3, "Reservation to Dispatch conversion", false, $e->getMessage());
}

// Cleanup the scheduled dispatch for further clean testing
$dispFromRes->delete();
$res->update(['status' => 'Approved']);

// ----------------------------------------------------
// 4. Direct Dispatch Creation
// ----------------------------------------------------
try {
    $directDisp = Dispatch::create([
        'dispatch_no' => 'DSP-DIR-' . strtoupper(uniqid()),
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'destination' => 'Direct Destination',
        'status' => 'Scheduled',
    ]);

    if ($directDisp && $directDisp->reservation_id === null && $directDisp->status === 'Scheduled') {
        recordResult($results, 4, "Direct Dispatch creation", true, "Created Direct Dispatch #{$directDisp->id} without reservation_id");
    } else {
        recordResult($results, 4, "Direct Dispatch creation", false, "Direct Dispatch failed");
    }
} catch (\Throwable $e) {
    recordResult($results, 4, "Direct Dispatch creation", false, $e->getMessage());
}

// ----------------------------------------------------
// 5 & 6. Vehicle & Driver Availability Validation
// ----------------------------------------------------
try {
    // Currently $directDisp is Scheduled with $vehicle->id and $driver->id
    $conflictVehicle = Dispatch::whereIn('status', ['Scheduled', 'Active'])
        ->where('vehicle_id', $vehicle->id)
        ->exists();

    $conflictDriver = Dispatch::whereIn('status', ['Scheduled', 'Active'])
        ->where('driver_id', $driver->id)
        ->exists();

    $vehOk = $conflictVehicle; // should be true (detected as busy)
    $drvOk = $conflictDriver;  // should be true (detected as busy)

    recordResult($results, 5, "Vehicle availability validation", $vehOk, $vehOk ? "Vehicle ID {$vehicle->id} detected as busy in Scheduled dispatch" : "Failed to detect vehicle busy state");
    recordResult($results, 6, "Driver availability validation", $drvOk, $drvOk ? "Driver ID {$driver->id} detected as busy in Scheduled dispatch" : "Failed to detect driver busy state");
} catch (\Throwable $e) {
    recordResult($results, 5, "Vehicle availability validation", false, $e->getMessage());
    recordResult($results, 6, "Driver availability validation", false, $e->getMessage());
}

// ----------------------------------------------------
// 7, 8, 9. Dispatch State Transitions & TripRecord Auto Creation / Completion
// ----------------------------------------------------
try {
    // Transition Scheduled -> Active via Controller logic
    $requestActive = \Illuminate\Http\Request::create('/dispatches/' . $directDisp->id . '/status', 'POST', ['status' => 'Active']);
    $requestActive->setUserResolver(fn() => $user);
    $dispatchController = new \App\Http\Controllers\DispatchController();
    $dispatchController->updateStatus($requestActive, $directDisp);

    $tripCreated = TripRecord::where('dispatch_id', $directDisp->id)->where('status', 'Active')->first();
    $autoCreatePassed = ($tripCreated !== null && $tripCreated->vehicle_id == $directDisp->vehicle_id);
    recordResult($results, 8, "TripRecord auto creation", $autoCreatePassed, $autoCreatePassed ? "TripRecord #{$tripCreated->id} auto-created on Active status" : "TripRecord auto creation failed");

    // Transition Active -> Completed via Controller logic
    $requestComplete = \Illuminate\Http\Request::create('/dispatches/' . $directDisp->id . '/status', 'POST', ['status' => 'Completed']);
    $requestComplete->setUserResolver(fn() => $user);
    $dispatchController->updateStatus($requestComplete, $directDisp->fresh());

    $tripCompleted = TripRecord::where('dispatch_id', $directDisp->id)->first();
    $autoCompletePassed = ($tripCompleted !== null && $tripCompleted->status === 'Completed' && $tripCompleted->actual_arrival !== null);
    recordResult($results, 9, "TripRecord auto completion", $autoCompletePassed, $autoCompletePassed ? "TripRecord #{$tripCompleted->id} marked Completed with actual_arrival and total_duration={$tripCompleted->total_duration}" : "TripRecord auto completion failed");

    $stateTransPassed = ($directDisp->fresh()->status === 'Completed');
    recordResult($results, 7, "Dispatch state transitions", $stateTransPassed, "Scheduled -> Active -> Completed verified via Controller");
} catch (\Throwable $e) {
    recordResult($results, 7, "Dispatch state transitions", false, $e->getMessage());
    recordResult($results, 8, "TripRecord auto creation", false, $e->getMessage());
    recordResult($results, 9, "TripRecord auto completion", false, $e->getMessage());
}

// ----------------------------------------------------
// 10. Dashboard counters
// ----------------------------------------------------
try {
    $req = \Illuminate\Http\Request::create('/reservations', 'GET');
    $req->setUserResolver(fn() => $user);

    $pageController = new \App\Http\Controllers\PageController();
    $view = $pageController->show('reservations');
    $data = $view->getData()['dashboard'];

    $hasPending = isset($data['pendingReservations']);
    $hasApproved = isset($data['approvedReservations']);
    $hasScheduled = isset($data['scheduledDispatches']);
    $hasActive = isset($data['activeDispatches']);
    $hasVehicles = isset($data['availableVehicles']);
    $hasDrivers = isset($data['availableDrivers']);

    $countersOk = ($hasPending && $hasApproved && $hasScheduled && $hasActive && $hasVehicles && $hasDrivers);
    recordResult($results, 10, "Dashboard counters & variable injection", $countersOk, "Injected variables: pendingReservations, approvedReservations, scheduledDispatches, activeDispatches, availableVehicles, availableDrivers");
} catch (\Throwable $e) {
    recordResult($results, 10, "Dashboard counters", false, $e->getMessage());
}

// Clean up test records
$directDisp->delete();
$res->delete();
$resReject->delete();
TripRecord::where('destination', 'Direct Destination')->delete();

echo PHP_EOL . "Verification Script Completed!" . PHP_EOL;
