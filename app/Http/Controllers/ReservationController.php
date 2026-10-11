<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ($request->user()->role === 'Admin' ? 'required' : 'nullable').'|string|max:50',
            'destination' => 'required|string|max:255',
            'requested_date' => 'required|date|after_or_equal:today',
            'passenger_count' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string|max:255',
            'vehicle_type' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'requested_time' => 'nullable|date_format:H:i',
        ], [
            'requested_date.after_or_equal' => 'Requested date cannot be in the past.',
            'passenger_count.min' => 'Passenger count must be at least 1.',
            'passenger_count.max' => 'Passenger count cannot exceed 60.',
            'purpose.required' => 'Purpose is required for reservation creation.',
            'destination.required' => 'Destination is required for reservation creation.',
        ]);

        if ($request->user()->role !== 'Admin') {
            $validated['employee_id'] = (string) $request->user()->id;
        } else {
            $validEmployee = \Illuminate\Support\Facades\DB::table('drivers')
                ->join('users', 'drivers.user_id', '=', 'users.id')
                ->whereRaw('LOWER(drivers.status) = ?', ['active'])
                ->where('drivers.employee_id', $validated['employee_id'])
                ->exists();

            if (! $validEmployee) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'employee_id' => 'Please choose a valid active driver through the employee ID list.',
                ]);
            }
        }

        $validated['reservation_no'] = 'RES-' . strtoupper((string) \Illuminate\Support\Str::uuid());
        $validated['status'] = 'Pending';
        $validated['user_id'] = $request->user()->id;
        if ($request->user()->role !== 'Admin') {
            $validated['employee_id'] = (string) $request->user()->id;
        }

        Reservation::create($validated);

        return back()->with('success', 'Reservation submitted and sent for approval.');
    }

    public function approve(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'Approved') {
            return back()->withErrors('Reservation is already approved.');
        }

        if ($reservation->status === 'Rejected') {
            return back()->withErrors('Cannot approve a rejected reservation.');
        }

        if ($reservation->status === 'Dispatched') {
            return back()->withErrors('Cannot approve an already dispatched reservation.');
        }

        if ($reservation->status !== 'Pending') {
            return back()->withErrors('Only pending reservations can be approved.');
        }

        $reservation->update([
            'status' => 'Approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Reservation approved successfully.');
    }

    public function reject(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'Approved') {
            return back()->withErrors('Cannot reject an approved reservation.');
        }

        if ($reservation->status === 'Rejected') {
            return back()->withErrors('Reservation is already rejected.');
        }

        if ($reservation->status === 'Dispatched') {
            return back()->withErrors('Cannot reject a dispatched reservation.');
        }

        if ($reservation->status !== 'Pending') {
            return back()->withErrors('Only pending reservations can be rejected.');
        }

        $reservation->update(['status' => 'Rejected']);

        return back()->with('success', 'Reservation rejected.');
    }
}
