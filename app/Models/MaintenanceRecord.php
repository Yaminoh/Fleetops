<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = ['vehicle_id', 'description', 'cost', 'serviced_at'];

    protected function casts(): array
    {
        return [
            'serviced_at' => 'date',
            'cost' => 'float',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
