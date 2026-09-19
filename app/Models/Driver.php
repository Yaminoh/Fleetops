<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    /** @use HasFactory<\Database\Factories\DriverFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'employee_id', 'role', 'score', 'dispatch_count', 'license_number', 'license_expiry', 'status'];

    
    public function user() { return $this->belongsTo(User::class); }
    public function dispatches() { return $this->hasMany(Dispatch::class); }
    public function tripRecords() { return $this->hasMany(TripRecord::class); }
}
