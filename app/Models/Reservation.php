<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'reservation_no', 'employee_id', 'destination', 'purpose', 
        'requested_date', 'requested_time', 'vehicle_type', 
        'passenger_count', 'remarks', 'status', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class);
    }
}
