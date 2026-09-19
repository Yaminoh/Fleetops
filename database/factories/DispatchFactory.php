<?php

namespace Database\Factories;

use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Dispatch>
 */
class DispatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dispatch_no' => $this->faker->unique()->bothify('DSP-####'),
            'reservation_id' => fn () => fake()->boolean(60)
                ? Reservation::query()->inRandomOrder()->value('id')
                : null,
            'vehicle_id' => fn () => Vehicle::query()->inRandomOrder()->value('id'),
            'driver_id' => fn () => Driver::query()->inRandomOrder()->value('id'),
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'origin_lat' => $this->faker->latitude(),
            'origin_lng' => $this->faker->longitude(),
            'dest_lat' => $this->faker->latitude(),
            'dest_lng' => $this->faker->longitude(),
            'priority' => $this->faker->randomElement(['Low', 'Normal', 'High', 'Critical']),
            'status' => $this->faker->randomElement(['Scheduled', 'Active', 'Completed']),
        ];
    }
}
