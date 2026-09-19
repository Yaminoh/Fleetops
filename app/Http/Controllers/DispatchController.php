<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\Reservation;
use App\Models\TripRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'destination' => 'required|string|max:255',
            'origin' => 'nullable|string|max:255',
            'priority' => 'nullable|in:Normal,High,Urgent',
        ]);

        // Operational Status Check: Vehicle must be Active
        $vehicle = DB::table('vehicles')->where('id', $validated['vehicle_id'])->first();
        if (!$vehicle || strtolower($vehicle->status) !== 'active') {
            return back()->withErrors('Selected vehicle is not active and cannot be dispatched (Status: ' . ($vehicle->status ?? 'Unknown') . ').');
        }

        // Operational Status Check: Driver must be Active
        $driver = DB::table('drivers')->where('id', $validated['driver_id'])->first();
        if (!$driver || strtolower($driver->status) !== 'active') {
            return back()->withErrors('Selected driver is not active and cannot be assigned (Status: ' . ($driver->status ?? 'Unknown') . ').');
        }

        // Double Booking Check: Vehicle
        $vehicleBusy = Dispatch::whereIn('status', ['Scheduled', 'Active'])
            ->where('vehicle_id', $validated['vehicle_id'])
            ->exists();
        if ($vehicleBusy) {
            return back()->withErrors('Selected vehicle is currently assigned to a Scheduled or Active dispatch.');
        }

        // Double Booking Check: Driver
        $driverBusy = Dispatch::whereIn('status', ['Scheduled', 'Active'])
            ->where('driver_id', $validated['driver_id'])
            ->exists();
        if ($driverBusy) {
            return back()->withErrors('Selected driver is currently assigned to a Scheduled or Active dispatch.');
        }

        $validated['dispatch_no'] = 'DSP-' . strtoupper(uniqid());
        $validated['status'] = 'Scheduled';

        DB::transaction(function () use ($validated) {
            Dispatch::create($validated);
            DB::table('vehicles')->where('id', $validated['vehicle_id'])->update(['status' => 'Reserved', 'updated_at' => now()]);
        });

        return back()->with('success', 'Direct Dispatch created successfully.');
    }

    public function convert(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'Approved') {
            return back()->withErrors('Only approved reservations can be dispatched.');
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
        ]);

        // Operational Status Check: Vehicle must be Active or Reserved
        $vehicle = DB::table('vehicles')->where('id', $validated['vehicle_id'])->first();
        if (!$vehicle || (!in_array(strtolower($vehicle->status), ['active', 'available', 'reserved']))) {
            return back()->withErrors('Selected vehicle is not available for dispatch (Status: ' . ($vehicle->status ?? 'Unknown') . ').');
        }

        // Operational Status Check: Driver must be Active
        $driver = DB::table('drivers')->where('id', $validated['driver_id'])->first();
        if (!$driver || strtolower($driver->status) !== 'active') {
            return back()->withErrors('Selected driver is not active and cannot be assigned (Status: ' . ($driver->status ?? 'Unknown') . ').');
        }

        // Double Booking Check: Vehicle
        $vehicleBusy = Dispatch::whereIn('status', ['Scheduled', 'Active'])
            ->where('vehicle_id', $validated['vehicle_id'])
            ->exists();
        if ($vehicleBusy) {
            return back()->withErrors('Selected vehicle is currently assigned to a Scheduled or Active dispatch.');
        }

        // Double Booking Check: Driver
        $driverBusy = Dispatch::whereIn('status', ['Scheduled', 'Active'])
            ->where('driver_id', $validated['driver_id'])
            ->exists();
        if ($driverBusy) {
            return back()->withErrors('Selected driver is currently assigned to a Scheduled or Active dispatch.');
        }

        DB::transaction(function () use ($reservation, $validated) {
            Dispatch::create([
                'dispatch_no' => 'DSP-' . strtoupper(uniqid()),
                'reservation_id' => $reservation->id,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'destination' => $reservation->destination,
                'status' => 'Scheduled',
            ]);

            DB::table('vehicles')->where('id', $validated['vehicle_id'])->update(['status' => 'Reserved', 'updated_at' => now()]);
            $reservation->update(['status' => 'Dispatched']);
        });

        return back()->with('success', 'Reservation converted to Dispatch successfully.');
    }

    public function updateStatus(Request $request, Dispatch $dispatch)
    {
        $validTransitions = [
            'Scheduled' => ['Active', 'Cancelled'],
            'Active' => ['Completed']
        ];

        $currentStatus = $dispatch->status;
        $newStatus = $request->input('status');

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            return back()->withErrors('Invalid state transition from ' . $currentStatus . ' to ' . $newStatus . '.');
        }

        DB::transaction(function () use ($dispatch, $newStatus) {
            $dispatch->update(['status' => $newStatus]);

            if ($newStatus === 'Active') {
                DB::table('vehicles')->where('id', $dispatch->vehicle_id)->update(['status' => 'In Transit', 'updated_at' => now()]);
                $existingTrip = TripRecord::where('dispatch_id', $dispatch->id)->first();
                if (!$existingTrip) {
                    TripRecord::create([
                        'dispatch_id' => $dispatch->id,
                        'vehicle_id' => $dispatch->vehicle_id,
                        'driver_id' => $dispatch->driver_id,
                        'origin' => $dispatch->origin,
                        'destination' => $dispatch->destination,
                        'departure_time' => now(),
                        'status' => 'Active'
                    ]);
                }
            }

            if ($newStatus === 'Completed' || $newStatus === 'Cancelled') {
                DB::table('vehicles')->where('id', $dispatch->vehicle_id)->update(['status' => 'Active', 'updated_at' => now()]);
                $trip = TripRecord::where('dispatch_id', $dispatch->id)->where('status', 'Active')->first();
                if ($trip) {
                    $trip->update([
                        'status' => 'Completed',
                        'actual_arrival' => now(),
                        'total_duration' => (int) max(0, round(now()->diffInMinutes($trip->departure_time)))
                    ]);
                }
            }
        });

        return back()->with('success', 'Dispatch status updated to ' . $newStatus . ' successfully.');
    }
}
