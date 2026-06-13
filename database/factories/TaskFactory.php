<?php

namespace Database\Factories;

use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'parent_task_id' => null,
            'title' => fake()->sentence(4, false),
            'estimated_minutes' => fake()->randomElement([15, 30, 45, 60, 90, 120]),
            'actual_minutes' => null,
            'order_index' => fake()->numberBetween(0, 50),
            'status' => 'pending',
            'due_date' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'actual_minutes' => fake()->numberBetween(15, 180),
        ]);
    }

    public function skipped(): static
    {
        return $this->state(['status' => 'skipped']);
    }

    public function withDueDate(): static
    {
        return $this->state([
            'due_date' => fake()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
        ]);
    }
}
