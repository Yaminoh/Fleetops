<?php

namespace Database\Seeders;

use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FleetCostSeeder extends Seeder
{
    /**
     * Seeds 6 months of realistic historical fuel + maintenance costs
     * per vehicle so Cost Analytics has real rows to aggregate from day one.
     */
    public function run(): void
    {
        if (FuelLog::exists() || MaintenanceRecord::exists()) {
            return;
        }

        mt_srand(20260908);

        $fuelProfile = [
            'Cargo Truck' => ['fills' => 4, 'liters' => 120, 'price' => 60],
            'SUV Cargo' => ['fills' => 3, 'liters' => 55, 'price' => 60],
            'Van Shuttle' => ['fills' => 3, 'liters' => 60, 'price' => 60],
            'Executive Sedan' => ['fills' => 2, 'liters' => 45, 'price' => 60],
        ];

        // Declining month-over-month multiplier so the trend chart shows
        // genuine cost improvement (oldest to most recent).
        $monthMultipliers = [1.15, 1.10, 1.05, 1.02, 0.98, 0.95];

        $vehicles = Vehicle::all();
        $now = Carbon::now();

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $multiplier = $monthMultipliers[5 - $i];

            foreach ($vehicles as $vehicle) {
                $profile = $fuelProfile[$vehicle->type] ?? ['fills' => 2, 'liters' => 50, 'price' => 60];

                for ($fill = 0; $fill < $profile['fills']; $fill++) {
                    $liters = round($profile['liters'] * $this->jitter(0.9, 1.1), 1);
                    $cost = round($liters * $profile['price'] * $multiplier, 2);
                    $day = min(28, 2 + (int) (($fill + 1) * (28 / ($profile['fills'] + 1))));

                    FuelLog::create([
                        'vehicle_id' => $vehicle->id,
                        'liters' => $liters,
                        'cost' => $cost,
                        'logged_at' => $monthStart->copy()->addDays($day - 1),
                    ]);
                }

                // Roughly 1 in 3 vehicle-months gets a maintenance entry.
                if ($this->jitter(0, 1) > 0.6) {
                    $descriptions = ['Oil change & filter', 'Brake pad replacement', 'Tire rotation', 'Scheduled inspection', 'Battery replacement'];
                    $description = $descriptions[array_rand($descriptions)];
                    $cost = round($this->jitter(800, 6000) * $multiplier, 2);

                    MaintenanceRecord::create([
                        'vehicle_id' => $vehicle->id,
                        'description' => $description,
                        'cost' => $cost,
                        'serviced_at' => $monthStart->copy()->addDays((int) $this->jitter(3, 25)),
                    ]);
                }
            }
        }
    }

    private function jitter(float $min, float $max): float
    {
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }
}
