<?php

namespace Database\Factories;

use App\Models\Dispatch;
use App\Models\DispatchLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DispatchLog>
 */
class DispatchLogFactory extends Factory
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
            'user_id' => fn () => fake()->boolean(80)
                ? User::query()->inRandomOrder()->value('id')
                : null,
            'action' => $this->faker->randomElement(['created', 'updated', 'departed', 'completed', 'cancelled']),
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
