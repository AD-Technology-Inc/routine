<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TaskDependency>
 */
class TaskDependencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'depends_on_task_id' => Task::factory(),
        ];
    }
}
