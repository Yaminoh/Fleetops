<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_code' => 'required|string|unique:vehicles,vehicle_code',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'type' => 'required|string',
            'status' => 'required|string',
            'odometer' => 'nullable|string',
        ]);

        $fuelLevel = (float) preg_replace('/[^0-9.]/', '', $request->input('odometer', '100'));
        if ($fuelLevel <= 0) $fuelLevel = 100.0;

        DB::table('vehicles')->insert([
            'vehicle_code' => strtoupper($request->input('vehicle_code')),
            'plate_number' => strtoupper($request->input('plate_number')),
            'type' => $request->input('type'),
            'status' => $request->input('status', 'Active'),
            'fuel_level' => $fuelLevel,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Vehicle added successfully!');
    }

    public function update(Request $request, $id)
    {
        // First try to find by vehicle_code directly (e.g. "TRK-9620")
        $vehicle = DB::table('vehicles')->where('vehicle_code', $id)->first();
        
        if (!$vehicle) {
            // Fallback: check if it's an ID
            $numericId = (int) preg_replace('/\D/', '', $id);
            $vehicle = DB::table('vehicles')->where('id', $numericId)->first();
        }

        if (!$vehicle) {
            return redirect()->back()->withErrors(['message' => 'Vehicle not found.']);
        }

        // Parse fuel level (e.g. "33.29% fuel" -> 33.29)
        $fuelInput = $request->input('odometer'); // UI sends fuel level in the odometer field
        $fuelLevel = (float) preg_replace('/[^0-9.]/', '', $fuelInput);

        DB::table('vehicles')
            ->where('id', $vehicle->id)
            ->update([
                'plate_number' => $request->input('plate_number'),
                'type' => $request->input('type'),
                'fuel_level' => $fuelLevel,
                'status' => $request->input('status'),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Vehicle updated successfully!');
    }
}
