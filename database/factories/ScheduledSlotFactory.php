<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ScheduledSlot>
 */
class ScheduledSlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'grouping_key' => null,
            'date' => fake()->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'time_block' => fake()->randomElement(['morning', 'afternoon', 'evening', 'anytime']),
            'allocated_minutes' => fake()->randomElement([30, 60, 90, 120]),
            'slot_index' => fake()->numberBetween(0, 10),
            'is_merged' => false,
            'merged_task_ids' => null,
            'status' => 'pending',
        ];
    }

    public function merged(): static
    {
        return $this->state([
            'is_merged' => true,
            'task_id' => null,
            'grouping_key' => 'interview_prep',
            'merged_task_ids' => [1, 2],
        ]);
    }
}
