<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Goal>
 */
class GoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3, false),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['active', 'paused', 'completed', 'archived']),
            'target_date' => fake()->optional()->dateTimeBetween('now', '+6 months')?->format('Y-m-d'),
            'color' => fake()->optional()->hexColor(),
            'order_index' => fake()->numberBetween(0, 20),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }
}
