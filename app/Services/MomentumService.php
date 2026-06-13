<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MomentumService
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    /**
     * Detect behavioral patterns and return actionable adaptation suggestions.
     *
     * @return array<int, array{type: string, message: string, action: string}>
     */
    public function generateAdaptations(User $user): array
    {
        $adaptations = [];

        $weekdayRates = $this->analyticsService->getWeekdayHeatmap($user);
        foreach ($weekdayRates as $day => $rate) {
            if ($rate < 50.0) {
                $adaptations[] = [
                    'type' => 'load_reduction',
                    'message' => "Your {$day} completion rate is {$rate}%. Consider scheduling fewer tasks.",
                    'action' => "reduce_{$day}_load",
                ];
            }
        }

        $energyPerf = $this->analyticsService->getEnergyPerformance($user);
        if (isset($energyPerf['high']) && $energyPerf['high'] < 50.0) {
            $adaptations[] = [
                'type' => 'energy_shift',
                'message' => "High-energy tasks are only completing at {$energyPerf['high']}%. Try scheduling them in the morning.",
                'action' => 'shift_high_energy_to_morning',
            ];
        }

        $longSkips = $this->detectFrequentlySkippedLongTasks($user);
        if ($longSkips > 0) {
            $adaptations[] = [
                'type' => 'task_split',
                'message' => "You have {$longSkips} tasks over 60 minutes that are frequently skipped. Enable task splitting.",
                'action' => 'enable_split_on_long_tasks',
            ];
        }

        return $adaptations;
    }

    /**
     * Count tasks that are >60 min and skipped more than 50% of the time.
     */
    private function detectFrequentlySkippedLongTasks(User $user): int
    {
        return DB::table('task_logs')
            ->join('tasks', 'task_logs.task_id', '=', 'tasks.id')
            ->where('task_logs.user_id', $user->id)
            ->where('tasks.estimated_minutes', '>', 60)
            ->selectRaw('task_logs.task_id,
                SUM(CASE WHEN task_logs.action = "skipped" THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as skip_rate')
            ->groupBy('task_logs.task_id')
            ->havingRaw('skip_rate > 50')
            ->count();
    }
}
