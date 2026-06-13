<?php

namespace App\Services;

use App\Models\AnalyticsSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Compute and upsert the analytics snapshot for a given user and date.
     */
    public function snapshotForDate(User $user, Carbon $date): AnalyticsSnapshot
    {
        $logs = DB::table('task_logs')
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN action = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN action = "skipped" THEN 1 ELSE 0 END) as skipped,
                SUM(CASE WHEN action = "missed" THEN 1 ELSE 0 END) as missed,
                AVG(CASE WHEN action = "completed" AND duration_minutes IS NOT NULL THEN duration_minutes END) as avg_duration
            ')
            ->first();

        $scheduled = $logs?->total ?? 0;
        $completed = $logs?->completed ?? 0;
        $skipped = $logs?->skipped ?? 0;
        $missed = $logs?->missed ?? 0;
        $rate = $scheduled > 0 ? round($completed / $scheduled * 100, 2) : 0.0;

        return AnalyticsSnapshot::updateOrCreate(
            ['user_id' => $user->id, 'date' => $date->toDateString()],
            [
                'total_tasks_scheduled' => $scheduled,
                'total_tasks_completed' => $completed,
                'total_tasks_skipped' => $skipped,
                'total_tasks_missed' => $missed,
                'completion_rate' => $rate,
                'avg_task_duration_minutes' => $logs?->avg_duration,
            ]
        );
    }

    /** @return Collection<int, AnalyticsSnapshot> */
    public function getCompletionTrend(User $user, int $days = 30): Collection
    {
        $start = Carbon::today()->subDays($days - 1);

        return AnalyticsSnapshot::where('user_id', $user->id)
            ->where('date', '>=', $start)
            ->orderBy('date')
            ->get();
    }

    /**
     * Returns completion rate keyed by weekday name.
     *
     * @return array<string, float>
     */
    public function getWeekdayHeatmap(User $user): array
    {
        $rows = DB::table('analytics_snapshots')
            ->where('user_id', $user->id)
            ->selectRaw("strftime('%w', date) as dow, AVG(completion_rate) as avg_rate")
            ->groupByRaw("strftime('%w', date)")
            ->get();

        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $result = [];

        foreach ($rows as $row) {
            $dayIndex = (int) $row->dow;
            $result[$dayNames[$dayIndex]] = round((float) $row->avg_rate, 2);
        }

        return $result;
    }

    /**
     * Returns average completion rate grouped by task energy_level.
     *
     * @return array<string, float>
     */
    public function getEnergyPerformance(User $user): array
    {
        $rows = DB::table('task_logs')
            ->join('task_attributes', 'task_logs.task_id', '=', 'task_attributes.task_id')
            ->where('task_logs.user_id', $user->id)
            ->whereIn('task_logs.action', ['completed', 'skipped', 'missed'])
            ->selectRaw('task_attributes.energy_level,
                SUM(CASE WHEN task_logs.action = "completed" THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate')
            ->groupBy('task_attributes.energy_level')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->energy_level] = round((float) $row->rate, 2);
        }

        return $result;
    }
}
