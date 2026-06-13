<?php

namespace App\Services;

use App\DTOs\MergedTaskDTO;
use App\Events\ScheduleGenerated;
use App\Models\ScheduledSlot;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SchedulingService
{
    private const MIN_SPLIT_MINUTES = 15;
    private const OPTIONAL_CAPACITY_THRESHOLD = 0.80;

    public function __construct(
        private readonly CapacityService $capacityService,
        private readonly DependencyService $dependencyService,
        private readonly GroupingService $groupingService,
    ) {}

    /**
     * Full 5-step deterministic scheduling pipeline.
     *
     * @return Collection<int, ScheduledSlot>
     */
    public function generateSchedule(User $user, ?Carbon $startDate = null, int $days = 7): Collection
    {
        $startDate ??= Carbon::today();

        // Step 1: Load pending tasks across all user goals
        $tasks = $this->loadPendingTasks($user);

        // Step 2: Apply constraints (dependency order, flexibility)
        $constrained = $this->applyConstraints($tasks, $user);

        // Step 3: Group tasks by grouping_key
        $groups = $this->groupingService->groupByKey($constrained);

        // Step 4: Pack into daily slots
        $slots = $this->packSchedule($groups, $user, $startDate, $days);

        // Step 5: Persist slots (atomic replace)
        $persisted = $this->persistSlots($slots, $user, $startDate, $days);

        event(new ScheduleGenerated($user, $persisted));

        return $persisted;
    }

    /**
     * @return Collection<int, ScheduledSlot>
     */
    public function getTodayPlan(User $user): Collection
    {
        $today = Carbon::today();
        $cacheKey = "schedule:{$user->id}:{$today->toDateString()}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user, $today): Collection {
            return ScheduledSlot::with('task.attribute')
                ->where('user_id', $user->id)
                ->forDate($today)
                ->pending()
                ->orderBy('slot_index')
                ->get();
        });
    }

    /**
     * @return Collection<int, ScheduledSlot>
     */
    public function getWindowPlan(User $user, int $days = 7): Collection
    {
        $start = Carbon::today();
        $end = $start->copy()->addDays($days - 1);

        return ScheduledSlot::with('task.attribute')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->orderBy('slot_index')
            ->get();
    }

    /**
     * Handle a missed task: log it and invalidate the schedule cache without shifting the calendar.
     */
    public function handleMissedTask(Task $task, User $user, Carbon $date): void
    {
        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'date' => $date,
            'action' => 'missed',
        ]);

        // Mark the slot as skipped without removing task from queue
        ScheduledSlot::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->update(['status' => 'skipped']);

        // Invalidate cache so next getTodayPlan recomputes
        Cache::forget("schedule:{$user->id}:{$date->toDateString()}");
    }

    /**
     * Step 1: Load pending tasks for a user, eager-loading attributes.
     *
     * @return Collection<int, Task>
     */
    private function loadPendingTasks(User $user): Collection
    {
        return Task::query()
            ->whereHas('goal', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->where('status', 'pending')
            ->with(['attribute', 'dependencies'])
            ->get();
    }

    /**
     * Step 2: Filter and reorder by dependency resolution.
     *
     * @param Collection<int, Task> $tasks
     * @return Collection<int, Task>
     */
    private function applyConstraints(Collection $tasks, User $user): Collection
    {
        // Resolve dependency order via topological sort
        $ordered = $this->dependencyService->getUnblockedTasks($tasks);

        // Stable sort: priority DESC, then order_index ASC
        return $ordered->sortBy(function (Task $task): array {
            $priority = $task->attribute?->priority ?? 'medium';
            $weight = match ($priority) {
                'critical' => 0,
                'high' => 1,
                'medium' => 2,
                'low' => 3,
                default => 2,
            };

            return [$weight, $task->order_index];
        })->values();
    }

    /**
     * Step 4: Pack tasks into daily capacity windows using greedy knapsack.
     *
     * @param array<string, Collection<int, Task>> $groups
     * @return array<int, array<string, mixed>>
     */
    private function packSchedule(array $groups, User $user, Carbon $startDate, int $days): array
    {
        $slots = [];
        /** @var array<int, array<string, mixed>> $deferrals Carry tasks to next day */
        $deferrals = [];
        /** @var array<int, int> $deferredMinutes Task ID → remaining minutes after split */
        $deferredMinutes = [];

        for ($dayOffset = 0; $dayOffset < $days; $dayOffset++) {
            $date = $startDate->copy()->addDays($dayOffset);
            $capacity = $this->capacityService->getAvailableMinutesForDate($user, $date);
            $used = 0;
            $slotIndex = 0;

            // First: schedule any deferred single tasks from previous days
            $remainingDeferrals = [];
            foreach ($deferrals as $deferred) {
                $task = $deferred['task'];
                $minutes = $deferred['minutes'];
                $attr = $task->attribute;

                if ($used + $minutes <= $capacity) {
                    $slots[] = $this->buildSlot($user, $task, $date, $minutes, $slotIndex++, $attr?->energy_level ?? 'medium');
                    $used += $minutes;
                } else {
                    $remainingDeferrals[] = $deferred;
                }
            }
            $deferrals = $remainingDeferrals;

            // Then: schedule grouped tasks
            foreach ($groups as $groupKey => $groupTasks) {
                if ($groupKey === '__ungrouped__') {
                    continue;
                }

                $merge = $this->groupingService->mergeTasks($groupTasks, $groupKey);

                if ($merge !== null) {
                    [$slots, $used, $slotIndex, $deferrals] = $this->scheduleMerged(
                        $merge, $date, $capacity, $used, $slotIndex, $slots, $deferrals, $user
                    );
                } else {
                    [$slots, $used, $slotIndex, $deferrals] = $this->scheduleIndividual(
                        $groupTasks, $date, $capacity, $used, $slotIndex, $slots, $deferrals, $user
                    );
                }
            }

            // Finally: ungrouped tasks
            $ungrouped = $groups['__ungrouped__'] ?? collect();
            [$slots, $used, $slotIndex, $deferrals] = $this->scheduleIndividual(
                $ungrouped, $date, $capacity, $used, $slotIndex, $slots, $deferrals, $user
            );
        }

        return $slots;
    }

    /**
     * Schedule a merged group into a single slot.
     *
     * @param array<int, array<string, mixed>> $slots
     * @param array<int, array<string, mixed>> $deferrals
     * @return array{0: array<int, array<string, mixed>>, 1: int, 2: int, 3: array<int, array<string, mixed>>}
     */
    private function scheduleMerged(
        MergedTaskDTO $merge,
        Carbon $date,
        int $capacity,
        int $used,
        int $slotIndex,
        array $slots,
        array $deferrals,
        User $user,
    ): array {
        $total = $merge->totalMinutes;
        $remaining = $capacity - $used;

        if ($total <= $remaining) {
            $slots[] = [
                'user_id' => $user->id,
                'task_id' => null,
                'grouping_key' => $merge->groupingKey,
                'date' => $date->toDateString(),
                'time_block' => $this->resolveTimeBlock($merge->tasks->first()?->attribute?->energy_level ?? 'medium'),
                'allocated_minutes' => $total,
                'slot_index' => $slotIndex++,
                'is_merged' => true,
                'merged_task_ids' => json_encode($merge->taskIds),
                'status' => 'pending',
            ];
            $used += $total;
        } elseif ($remaining >= self::MIN_SPLIT_MINUTES) {
            // Partial: fill today, defer the rest
            $slots[] = [
                'user_id' => $user->id,
                'task_id' => null,
                'grouping_key' => $merge->groupingKey,
                'date' => $date->toDateString(),
                'time_block' => $this->resolveTimeBlock($merge->tasks->first()?->attribute?->energy_level ?? 'medium'),
                'allocated_minutes' => $remaining,
                'slot_index' => $slotIndex++,
                'is_merged' => true,
                'merged_task_ids' => json_encode($merge->taskIds),
                'status' => 'pending',
            ];
            $used = $capacity;

            // Defer remaining as individual tasks
            foreach ($merge->tasks as $task) {
                $deferrals[] = ['task' => $task, 'minutes' => $task->estimated_minutes];
            }
        } else {
            // Defer entire group
            foreach ($merge->tasks as $task) {
                $deferrals[] = ['task' => $task, 'minutes' => $task->estimated_minutes];
            }
        }

        return [$slots, $used, $slotIndex, $deferrals];
    }

    /**
     * Schedule individual tasks from a collection.
     *
     * @param Collection<int, Task> $tasks
     * @param array<int, array<string, mixed>> $slots
     * @param array<int, array<string, mixed>> $deferrals
     * @return array{0: array<int, array<string, mixed>>, 1: int, 2: int, 3: array<int, array<string, mixed>>}
     */
    private function scheduleIndividual(
        Collection $tasks,
        Carbon $date,
        int $capacity,
        int $used,
        int $slotIndex,
        array $slots,
        array $deferrals,
        User $user,
    ): array {
        foreach ($tasks as $task) {
            $attr = $task->attribute;
            $minutes = $task->estimated_minutes;
            $flexibility = $attr?->flexibility ?? 'flexible';
            $canSplit = (bool) ($attr?->can_split ?? false);

            // Skip optional tasks when over 80% capacity used
            if ($flexibility === 'optional' && $used >= $capacity * self::OPTIONAL_CAPACITY_THRESHOLD) {
                continue;
            }

            $remaining = $capacity - $used;

            if ($minutes <= $remaining) {
                $slots[] = $this->buildSlot($user, $task, $date, $minutes, $slotIndex++, $attr?->energy_level ?? 'medium');
                $used += $minutes;
            } elseif ($canSplit && $remaining >= self::MIN_SPLIT_MINUTES) {
                // Split: schedule remaining capacity today, defer the rest
                $slots[] = $this->buildSlot($user, $task, $date, $remaining, $slotIndex++, $attr?->energy_level ?? 'medium');
                $used = $capacity;

                $deferrals[] = ['task' => $task, 'minutes' => $minutes - $remaining];
            } else {
                $deferrals[] = ['task' => $task, 'minutes' => $minutes];
            }
        }

        return [$slots, $used, $slotIndex, $deferrals];
    }

    /**
     * Build a slot array for a single task.
     *
     * @return array<string, mixed>
     */
    private function buildSlot(User $user, Task $task, Carbon $date, int $minutes, int $index, string $energyLevel): array
    {
        return [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'grouping_key' => $task->attribute?->grouping_key,
            'date' => $date->toDateString(),
            'time_block' => $this->resolveTimeBlock($energyLevel),
            'allocated_minutes' => $minutes,
            'slot_index' => $index,
            'is_merged' => false,
            'merged_task_ids' => null,
            'status' => 'pending',
        ];
    }

    /**
     * Map energy level to preferred time block.
     * High energy tasks go in the morning, low energy in the evening.
     */
    private function resolveTimeBlock(string $energyLevel): string
    {
        return match ($energyLevel) {
            'high' => 'morning',
            'low' => 'evening',
            default => 'afternoon',
        };
    }

    /**
     * Step 5: Atomically replace scheduled slots for the window.
     *
     * @param array<int, array<string, mixed>> $slots
     * @return Collection<int, ScheduledSlot>
     */
    private function persistSlots(array $slots, User $user, Carbon $startDate, int $days): Collection
    {
        $endDate = $startDate->copy()->addDays($days - 1);

        DB::transaction(function () use ($slots, $user, $startDate, $endDate): void {
            // Only delete pending slots — keep completed/skipped for history
            ScheduledSlot::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'pending')
                ->delete();

            if (! empty($slots)) {
                $now = now()->toDateTimeString();
                $rows = array_map(fn (array $slot): array => array_merge($slot, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]), $slots);

                ScheduledSlot::insert($rows);
            }
        });

        // Invalidate daily cache for window
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            Cache::forget("schedule:{$user->id}:{$date}");
        }

        return ScheduledSlot::with('task.attribute')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->orderBy('slot_index')
            ->get();
    }
}
