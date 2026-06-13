<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TaskLog>
 */
class TaskLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'action' => fake()->randomElement(['scheduled', 'completed', 'skipped', 'missed', 'rescheduled']),
            'notes' => fake()->optional()->sentence(),
            'duration_minutes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'action' => 'completed',
            'duration_minutes' => fake()->numberBetween(10, 180),
        ]);
    }

    public function missed(): static
    {
        return $this->state(['action' => 'missed', 'duration_minutes' => null]);
    }

    public function forDate(string $date): static
    {
        return $this->state(['date' => $date]);
    }
}
