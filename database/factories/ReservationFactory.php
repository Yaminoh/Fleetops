<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_no' => $this->faker->unique()->bothify('RES-####'),
            'employee_id' => $this->faker->unique()->bothify('EMP-####'),
            'destination' => $this->faker->city(),
            'purpose' => $this->faker->sentence(3),
            'requested_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'requested_time' => $this->faker->optional()->time('H:i:s'),
            'vehicle_type' => $this->faker->randomElement(['SUV Cargo', 'Van Shuttle', 'Executive Sedan', 'Cargo Truck']),
            'passenger_count' => $this->faker->numberBetween(1, 8),
            'remarks' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the reservation has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
