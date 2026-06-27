<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GoalService
{
    /**
     * @param array{title: string, description?: string|null, status?: string, target_date?: string|null, color?: string|null, order_index?: int} $data
     */
    public function createGoal(User $user, array $data): Goal
    {
        return DB::transaction(function() use ($user, $data) {
            $goal = $user->goals()->create($data);
            // proposed steps:
            // 1. create goal
            // 2. save Goal to DB
            // 3. save Goal even AIPlanGoalJob to outbox
            // 4. outbox -- async --> AIPlanGoalJob worker
            // 5. generate Goal tasks

            // issue: if AI fails??? async ??? sync??? AI slow!
            // $goal->aiPlan();

            return $goal;
        });
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
