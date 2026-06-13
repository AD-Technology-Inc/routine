<?php

namespace Database\Factories;

use App\Models\Routine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RoutineStep>
 */
class RoutineStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'routine_id' => Routine::factory(),
            'title' => fake()->sentence(3, false),
            'estimated_minutes' => fake()->randomElement([5, 10, 15, 20, 30]),
            'energy_level' => fake()->randomElement(['low', 'medium', 'high']),
            'order_index' => fake()->numberBetween(0, 10),
        ];
    }
}
