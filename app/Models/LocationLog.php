<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationLog extends Model
{
    /** @use HasFactory<\Database\Factories\LocationLogFactory> */
    use HasFactory;

    protected $fillable = ['vehicle_id', 'latitude', 'longitude', 'speed', 'fuel_level', 'timestamp'];

    
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}
