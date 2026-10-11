<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FleetCostController extends Controller
{
    public function storeFuelLog(Request $request): RedirectResponse
    {
        if ($request->user()->role !== 'Admin') {
            // Regular users act as the driver for their own fuel log. The user_id
            // records their account; do not let the browser assign another driver.
            $request->merge(['driver_id' => null]);
        }

        $data = $request->validate([
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')],
            'driver_id' => ['nullable', Rule::exists('drivers', 'id')],
            'liters' => ['required', 'numeric', 'min:0.1', 'max:2000'],
            'cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'fuel_level_before' => ['required', 'numeric', 'between:0,100'],
            'fuel_level_after' => ['required', 'numeric', 'between:0,100', 'gte:fuel_level_before'],
            'receipt_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'logged_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $data['receipt_image'] = $request->file('receipt_image')->store('fuel-logs/proofs', 'local');
        $data['user_id'] = $request->user()->id;
        $log = FuelLog::create($data);
        $vehicle = Vehicle::find($data['vehicle_id']);
        $vehicle->update(['fuel_level' => $data['fuel_level_after']]);
        $driver = isset($data['driver_id']) ? Driver::with('user')->find($data['driver_id']) : null;
        $driverName = $driver?->user?->name
            ?? $driver?->name
            ?? ($request->user()->role !== 'Admin' ? $request->user()->name : 'No driver assigned');

        Alert::log("\u{26FD}", 'Fuel Logged', sprintf(
            '%s: %.1fL logged for PHP %s by %s (driver: %s).',
            $vehicle->name ?? 'Vehicle',
            $log->liters,
            number_format($log->cost, 2),
            $request->user()->name,
            $driverName
        ));

        return back()->with('status', 'Fuel log recorded.');
    }

    public function showFuelProof(FuelLog $fuelLog): BinaryFileResponse
    {
        $user = request()->user();
        abort_unless($user && ($user->role === 'Admin' || (int) $fuelLog->user_id === (int) $user->id), 404);
        abort_unless($fuelLog->receipt_image && Storage::disk('local')->exists($fuelLog->receipt_image), 404);

        return response()->file(Storage::disk('local')->path($fuelLog->receipt_image));
    }

    public function storeMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')],
            'description' => ['required', 'string', 'max:150'],
            'cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'serviced_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $record = MaintenanceRecord::create($data);
        $vehicle = Vehicle::find($data['vehicle_id']);

        Alert::log("\u{1F527}", 'Maintenance Logged', sprintf(
            '%s: %s (â‚±%s) logged by %s.',
            $vehicle->name ?? 'Vehicle',
            $record->description,
            number_format($record->cost, 2),
            $request->user()->name
        ), 'warning');

        return back()->with('status', 'Maintenance cost recorded.');
    }
}
