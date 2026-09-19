<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripRecord extends Model
{
    /** @use HasFactory<\Database\Factories\TripRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'dispatch_id',
        'vehicle_id',
        'driver_id',
        'origin',
        'destination',
        'origin_lat',
        'origin_lng',
        'dest_lat',
        'dest_lng',
        'departure_time',
        'estimated_arrival',
        'actual_arrival',
        'total_distance',
        'total_duration',
        'fuel_consumption',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'estimated_arrival' => 'datetime',
            'actual_arrival' => 'datetime',
        ];
    }

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
