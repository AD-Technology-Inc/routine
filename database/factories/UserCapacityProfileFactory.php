<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\UserCapacityProfile>
 */
class UserCapacityProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'daily_available_minutes' => fake()->randomElement([120, 180, 240, 300, 360]),
            'preferred_time_blocks' => fake()->randomElements(['morning', 'afternoon', 'evening'], fake()->numberBetween(1, 3)),
            'monday_minutes' => null,
            'tuesday_minutes' => null,
            'wednesday_minutes' => null,
            'thursday_minutes' => null,
            'friday_minutes' => null,
            'saturday_minutes' => null,
            'sunday_minutes' => null,
        ];
    }

    public function withWeekendReduction(): static
    {
        return $this->state([
            'saturday_minutes' => 60,
            'sunday_minutes' => 60,
        ]);
    }

    public function withFridayReduction(): static
    {
        return $this->state(['friday_minutes' => 120]);
    }
}
