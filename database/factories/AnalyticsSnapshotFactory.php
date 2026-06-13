<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AnalyticsSnapshot>
 */
class AnalyticsSnapshotFactory extends Factory
{
    public function definition(): array
    {
        $scheduled = fake()->numberBetween(2, 8);
        $completed = fake()->numberBetween(0, $scheduled);
        $skipped = fake()->numberBetween(0, $scheduled - $completed);
        $missed = $scheduled - $completed - $skipped;
        $rate = $scheduled > 0 ? round($completed / $scheduled * 100, 2) : 0.0;

        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'total_tasks_scheduled' => $scheduled,
            'total_tasks_completed' => $completed,
            'total_tasks_skipped' => $skipped,
            'total_tasks_missed' => max(0, $missed),
            'completion_rate' => $rate,
            'avg_task_duration_minutes' => $completed > 0 ? fake()->randomFloat(2, 20, 120) : null,
        ];
    }
}
