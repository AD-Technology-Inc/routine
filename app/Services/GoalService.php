<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GoalService
{
    /**
     * @param array{title: string, description?: string|null, status?: string, target_date?: string|null, color?: string|null, order_index?: int} $data
     */
    public function createGoal(User $user, array $data): Goal
    {
        return $user->goals()->create($data);
    }

    /**
     * @param array{title?: string, description?: string|null, status?: string, target_date?: string|null, color?: string|null, order_index?: int} $data
     */
    public function updateGoal(Goal $goal, array $data): Goal
    {
        $goal->update($data);

        return $goal->fresh();
    }

    public function archiveGoal(Goal $goal): Goal
    {
        $goal->update(['status' => 'archived']);

        return $goal->fresh();
    }

    /** @return Collection<int, Goal> */
    public function getGoalsForUser(User $user): Collection
    {
        return Goal::forUser($user)
            ->orderBy('order_index')
            ->orderByDesc('created_at')
            ->get();
    }
}
