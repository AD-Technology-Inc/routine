<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Routine>
 */
class RoutineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'goal_id' => null,
            'title' => fake()->randomElement(['Morning Workout', 'Deep Work Session', 'Reading Block', 'Evening Review', 'DSA Practice']),
            'frequency' => fake()->randomElement(['daily', 'weekdays', 'weekends', 'weekly']),
            'custom_days' => null,
            'time_block' => fake()->randomElement(['morning', 'afternoon', 'evening', 'anytime']),
            'is_active' => true,
        ];
    }

    public function daily(): static
    {
        return $this->state(['frequency' => 'daily']);
    }

    public function weekdays(): static
    {
        return $this->state(['frequency' => 'weekdays']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
