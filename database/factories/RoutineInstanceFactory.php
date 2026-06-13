<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RoutineInstance>
 */
class RoutineInstanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'routine_id' => Routine::factory(),
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'status' => 'pending',
            'completed_step_ids' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }

    public function skipped(): static
    {
        return $this->state(['status' => 'skipped']);
    }
}
