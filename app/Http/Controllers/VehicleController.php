<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_code' => ['nullable', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:30', 'unique:vehicles,plate_number'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Active', 'Available', 'In Transit', 'Reserved', 'Maintenance', 'Inactive'])],
            'odometer' => ['nullable', 'integer', 'min:0'],
        ]);

        // Auto-generate the next available vehicle code; do not trust a user-submitted value.
        $existingCodes = Vehicle::where('vehicle_code', 'like', 'VHC-%')->pluck('vehicle_code');
        $nextVehicleNumber = 0;
        foreach ($existingCodes as $code) {
            if (preg_match('/^VHC-(\d+)$/i', (string) $code, $matches)) {
                $nextVehicleNumber = max($nextVehicleNumber, (int) $matches[1]);
            }
        }
        $data['vehicle_code'] = 'VHC-' . str_pad($nextVehicleNumber + 1, 3, '0', STR_PAD_LEFT);
        $data['plate_number'] = strtoupper($data['plate_number']);
        if ($data['status'] === 'Available') $data['status'] = 'Active';
        Vehicle::create($data);

        return redirect()->route('vehicles')->with('success', 'Vehicle saved to the fleet database.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'vehicle_code' => ['required', 'string', 'max:50', Rule::unique('vehicles', 'vehicle_code')->ignore($vehicle->id)],
            'plate_number' => ['required', 'string', 'max:30', Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Active', 'Available', 'In Transit', 'Reserved', 'Maintenance', 'Inactive'])],
            'odometer' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['vehicle_code'] = strtoupper($data['vehicle_code']);
        $data['plate_number'] = strtoupper($data['plate_number']);
        if ($data['status'] === 'Available') $data['status'] = 'Active';
        $vehicle->update($data);

        return redirect()->route('vehicles')->with('success', 'Vehicle changes saved to the fleet database.');
    }
}
