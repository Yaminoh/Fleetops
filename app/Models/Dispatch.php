<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    /** @use HasFactory<\Database\Factories\DispatchFactory> */
    use HasFactory;

    protected $fillable = [
        'dispatch_no',
        'reservation_id',
        'vehicle_id',
        'driver_id',
        'origin',
        'destination',
        'origin_lat',
        'origin_lng',
        'dest_lat',
        'dest_lng',
        'priority',
        'status',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function logs()
    {
        return $this->hasMany(DispatchLog::class);
    }

    public function tripRecords()
    {
        return $this->hasMany(TripRecord::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }
}
