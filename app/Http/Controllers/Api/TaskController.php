<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Goal;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        protected readonly TaskService $taskService,
    ) {}

    public function index(Request $request, Goal $goal): AnonymousResourceCollection
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $tasks = $this->taskService->getTasksForGoal($goal);

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Goal $goal): TaskResource
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();
        $taskKeys = ['title', 'parent_task_id', 'estimated_minutes', 'due_date', 'order_index'];
        $attributeKeys = ['priority', 'type', 'flexibility', 'reschedule_policy', 'energy_level', 'grouping_key', 'can_merge', 'can_split'];

        $taskData = array_intersect_key($validated, array_flip($taskKeys));
        $attributeData = array_intersect_key($validated, array_flip($attributeKeys));

        $task = $this->taskService->createTask($goal, $taskData, $attributeData);

        return new TaskResource($task);
    }

    public function show(Request $request, Task $task): TaskResource
    {
        if ($task->goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $task->load(['attribute', 'dependencies']);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        if ($task->goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();
        $taskKeys = ['title', 'parent_task_id', 'estimated_minutes', 'due_date', 'order_index', 'status'];
        $attributeKeys = ['priority', 'type', 'flexibility', 'reschedule_policy', 'energy_level', 'grouping_key', 'can_merge', 'can_split'];

        $taskData = array_intersect_key($validated, array_flip($taskKeys));
        $attributeData = array_intersect_key($validated, array_flip($attributeKeys));

        $updatedTask = $this->taskService->updateTask($task, $taskData, $attributeData);

        return new TaskResource($updatedTask);
    }

    public function complete(CompleteTaskRequest $request, Task $task): TaskResource
    {
        if ($task->goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $duration = $request->input('duration_minutes', 0);
        $this->taskService->completeTask($task, $request->user(), (int) $duration);

        return new TaskResource($task->load('attribute'));
    }

    public function skip(Request $request, Task $task): TaskResource
    {
        if ($task->goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $notes = $request->input('notes');
        $this->taskService->skipTask($task, $request->user(), $notes);

        return new TaskResource($task->load('attribute'));
    }
}
