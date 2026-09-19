<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'requested_date' => 'required|date|after_or_equal:today',
            'passenger_count' => 'required|integer|min:1',
            'purpose' => 'required|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'requested_time' => 'nullable|string',
        ], [
            'requested_date.after_or_equal' => 'Requested date cannot be in the past.',
            'passenger_count.min' => 'Passenger count must be at least 1.',
            'purpose.required' => 'Purpose is required for reservation creation.',
            'destination.required' => 'Destination is required for reservation creation.',
        ]);

        $validated['reservation_no'] = 'RES-' . strtoupper(uniqid());
        $validated['status'] = 'Pending';

        Reservation::create($validated);

        return back()->with('success', 'Reservation created successfully.');
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
