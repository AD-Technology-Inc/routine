<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TaskAttribute>
 */
class TaskAttributeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'type' => fake()->randomElement(['learning', 'practice', 'execution', 'review', 'planning']),
            'flexibility' => fake()->randomElement(['fixed', 'flexible', 'optional']),
            'reschedule_policy' => fake()->randomElement(['strict', 'soft', 'skip_allowed']),
            'energy_level' => fake()->randomElement(['low', 'medium', 'high']),
            'grouping_key' => fake()->optional(0.6)->randomElement(['interview_prep', 'fitness', 'frontend', 'backend', 'reading']),
            'can_merge' => fake()->boolean(30),
            'can_split' => fake()->boolean(40),
        ];
    }

    public function highEnergy(): static
    {
        return $this->state(['energy_level' => 'high', 'priority' => 'high']);
    }

    public function mergeable(): static
    {
        return $this->state(['can_merge' => true, 'grouping_key' => 'interview_prep']);
    }

    public function splittable(): static
    {
        return $this->state(['can_split' => true, 'flexibility' => 'flexible']);
    }
}
