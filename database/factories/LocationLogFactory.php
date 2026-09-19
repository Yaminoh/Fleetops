<?php

namespace Database\Factories;

use App\Models\LocationLog;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocationLog>
 */
class LocationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'vehicle_id' => fn () => Vehicle::query()->inRandomOrder()->value('id'),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'speed' => $this->faker->randomFloat(2, 0, 120),
            'fuel_level' => $this->faker->randomFloat(2, 0, 100),
            'timestamp' => $this->faker->dateTimeThisMonth(),
            ];
    }
}
