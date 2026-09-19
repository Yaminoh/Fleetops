<?php

namespace Database\Factories;

use App\Models\Dispatch;
use App\Models\FuelLog;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FuelLog>
 */
class FuelLogFactory extends Factory
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
            'dispatch_id' => fn () => fake()->boolean(70)
                ? Dispatch::query()->inRandomOrder()->value('id')
                : null,
            'liters' => $this->faker->randomFloat(2, 10, 200),
            'cost' => $this->faker->optional()->randomFloat(2, 500, 10000),
            'logged_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'receipt_image' => $this->faker->optional()->filePath(),
        ];
    }

    /**
     * Configure the factory to align vehicle with the linked dispatch when present.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (FuelLog $fuelLog) {
            if ($fuelLog->dispatch_id && $fuelLog->dispatch?->vehicle_id) {
                $fuelLog->vehicle_id = $fuelLog->dispatch->vehicle_id;
            }
        });
    }
}
