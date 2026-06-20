<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduledSlotResource;
use App\Models\Goal;
use App\Models\Task;
use App\Services\SchedulingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduleController extends Controller
{
    public function __construct(
        protected readonly SchedulingService $schedulingService,
    ) {}

    public function generate(Request $request, Goal $goal): AnonymousResourceCollection
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $slots = $this->schedulingService->generateSchedule($request->user());

        return ScheduledSlotResource::collection($slots);
    }

    public function show(Request $request, Goal $goal): AnonymousResourceCollection
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $slots = $this->schedulingService->getWindowPlan($request->user());

        $filtered = $slots->filter(function ($slot) use ($goal) {
            if ($slot->task_id) {
                return $slot->task?->goal_id === $goal->id;
            }
            if ($slot->is_merged && $slot->merged_task_ids) {
                $taskIds = is_array($slot->merged_task_ids) ? $slot->merged_task_ids : json_decode($slot->merged_task_ids, true);
                if ($taskIds) {
                    return Task::whereIn('id', $taskIds)->where('goal_id', $goal->id)->exists();
                }
            }
            return false;
        });

        return ScheduledSlotResource::collection($filtered);
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        $slots = $this->schedulingService->getTodayPlan($request->user());

        return ScheduledSlotResource::collection($slots);
    }

    public function window(Request $request): AnonymousResourceCollection
    {
        $days = (int) $request->input('days', 7);
        $slots = $this->schedulingService->getWindowPlan($request->user(), $days);

        return ScheduledSlotResource::collection($slots);
    }
}
