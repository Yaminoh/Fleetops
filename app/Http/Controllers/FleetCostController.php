<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FleetCostController extends Controller
{
    public function storeFuelLog(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')],
            'liters' => ['required', 'numeric', 'min:0.1', 'max:2000'],
            'cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'logged_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $log = FuelLog::create($data);
        $vehicle = Vehicle::find($data['vehicle_id']);

        Alert::log('⛽', 'Fuel Logged', sprintf(
            '%s: %.1fL logged for ₱%s by %s.',
            $vehicle->name ?? 'Vehicle',
            $log->liters,
            number_format($log->cost, 2),
            $request->user()->name
        ));

        return back()->with('status', 'Fuel log recorded.');
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

        Alert::log('🔧', 'Maintenance Logged', sprintf(
            '%s: %s (₱%s) logged by %s.',
            $vehicle->name ?? 'Vehicle',
            $record->description,
            number_format($record->cost, 2),
            $request->user()->name
        ), 'warning');

        return back()->with('status', 'Maintenance cost recorded.');
    }
}
