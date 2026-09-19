<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'employee_id' => $this->faker->unique()->bothify('DRV-####'),
            'role' => 'Driver',
            'score' => $this->faker->randomFloat(2, 70, 100),
            'dispatch_count' => $this->faker->numberBetween(0, 150),
            'license_number' => $this->faker->optional()->bothify('LIC-######'),
            'license_expiry' => $this->faker->optional()->dateTimeBetween('+1 year', '+5 years'),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }
}
