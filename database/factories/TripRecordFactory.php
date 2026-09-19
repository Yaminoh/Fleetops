<?php

namespace Database\Factories;

use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\TripRecord;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripRecord>
 */
class TripRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'dispatch_id' => fn () => Dispatch::query()->inRandomOrder()->value('id'),
            'vehicle_id' => fn () => Vehicle::query()->inRandomOrder()->value('id'),
            'driver_id' => fn () => Driver::query()->inRandomOrder()->value('id'),
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'origin_lat' => $this->faker->latitude(),
            'origin_lng' => $this->faker->longitude(),
            'dest_lat' => $this->faker->latitude(),
            'dest_lng' => $this->faker->longitude(),
            'departure_time' => $this->faker->dateTimeThisMonth(),
            'estimated_arrival' => $this->faker->dateTimeThisMonth(),
            'actual_arrival' => $this->faker->optional()->dateTimeThisMonth(),
            'total_distance' => $this->faker->randomFloat(2, 5, 500),
            'total_duration' => $this->faker->numberBetween(30, 600),
            'fuel_consumption' => $this->faker->randomFloat(2, 2, 50),
            'status' => $this->faker->randomElement(['Active', 'Completed']),
            ];
    }
}
