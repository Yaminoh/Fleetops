<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\TripRecord;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
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
            'trip_record_id' => fn () => TripRecord::query()->inRandomOrder()->value('id'),
            'icon' => 'ℹ️',
            'title' => $this->faker->sentence(4),
            'detail' => $this->faker->sentence(),
            'type' => $this->faker->word(),
            'message' => $this->faker->sentence(),
            'severity' => $this->faker->randomElement(['info', 'warning', 'critical']),
            ];
    }
}
