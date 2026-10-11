<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'vehicle_id', 'driver_id', 'liters', 'cost', 'fuel_level_before', 'fuel_level_after', 'fuel_level_before_image', 'fuel_level_after_image', 'logged_at', 'receipt_image'];

    protected function casts(): array
    {
        return [
            'logged_at' => 'date',
            'liters' => 'float',
            'cost' => 'float',
            'fuel_level_before' => 'float',
            'fuel_level_after' => 'float',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
