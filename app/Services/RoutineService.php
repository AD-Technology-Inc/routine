<?php

namespace App\Services;

use App\Events\RoutineInstanceCreated;
use App\Models\Routine;
use App\Models\RoutineInstance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class RoutineService
{
    /**
     * @param array{title: string, frequency: string, time_block?: string, custom_days?: array<int>|null, goal_id?: int|null, is_active?: bool} $data
     */
    public function createRoutine(User $user, array $data): Routine
    {
        return $user->routines()->create($data);
    }

    /**
     * Generate RoutineInstances for all active routines that should run on the given date.
     * Idempotent — uses firstOrCreate, safe to call multiple times per day.
     *
     * @return Collection<int, RoutineInstance>
     */
    public function generateInstancesForDate(User $user, Carbon $date): Collection
    {
        $instances = collect();

        Routine::where('user_id', $user->id)
            ->active()
            ->with('steps')
            ->each(function (Routine $routine) use ($date, $instances): void {
                if (! $routine->shouldRunOnDate($date)) {
                    return;
                }

                [$instance, $created] = RoutineInstance::firstOrCreate(
                    ['routine_id' => $routine->id, 'date' => $date->toDateString()],
                    ['user_id' => $routine->user_id, 'status' => 'pending']
                ) instanceof RoutineInstance
                    ? [RoutineInstance::firstOrCreate(
                        ['routine_id' => $routine->id, 'date' => $date->toDateString()],
                        ['user_id' => $routine->user_id, 'status' => 'pending']
                    ), false]
                    : [null, false];

                // Re-do with proper firstOrCreate return
                $existing = RoutineInstance::firstOrCreate(
                    ['routine_id' => $routine->id, 'date' => $date->toDateString()],
                    ['user_id' => $routine->user_id, 'status' => 'pending', 'completed_step_ids' => null]
                );

                if ($existing->wasRecentlyCreated) {
                    event(new RoutineInstanceCreated($existing));
                }

                $instances->push($existing);
            });

        return $instances;
    }

    /**
     * Mark a single step as completed within a routine instance.
     */
    public function completeStep(RoutineInstance $instance, int $stepId): RoutineInstance
    {
        $completed = $instance->completed_step_ids ?? [];

        if (! in_array($stepId, $completed, strict: true)) {
            $completed[] = $stepId;
            $instance->update(['completed_step_ids' => $completed]);
        }

        // Compute overall status
        $totalSteps = $instance->routine->steps()->count();
        $completedCount = count($instance->fresh()->completed_step_ids ?? []);

        $status = match (true) {
            $completedCount === 0 => 'pending',
            $completedCount >= $totalSteps => 'completed',
            default => 'partial',
        };

        $instance->update(['status' => $status]);

        return $instance->fresh();
    }

    public function skipRoutine(RoutineInstance $instance): RoutineInstance
    {
        $instance->update(['status' => 'skipped']);

        return $instance->fresh();
    }

    /** @return Collection<int, RoutineInstance> */
    public function getTodayRoutines(User $user): Collection
    {
        return RoutineInstance::with(['routine.steps'])
            ->where('user_id', $user->id)
            ->forDate(Carbon::today())
            ->get();
    }
}
