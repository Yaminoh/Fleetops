<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'vehicle_code' => $this->faker->unique()->bothify('TRK-####'),
            'plate_number' => $this->faker->unique()->bothify('???-####'),
            'name' => $this->faker->company() . ' Unit',
            'type' => $this->faker->randomElement(['Heavy Cargo Truck', 'Delivery Van', 'Refrigerated Transport']),
            'status' => $this->faker->randomElement(['Active', 'Maintenance', 'Inactive']),
            'fuel_level' => $this->faker->randomFloat(2, 10, 100),
            ];
    }
}
