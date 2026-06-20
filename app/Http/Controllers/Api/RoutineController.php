<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutineRequest;
use App\Http\Resources\RoutineInstanceResource;
use App\Http\Resources\RoutineResource;
use App\Models\Routine;
use App\Models\RoutineInstance;
use App\Models\RoutineStep;
use App\Services\RoutineService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoutineController extends Controller
{
    public function __construct(
        protected readonly RoutineService $routineService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $routines = Routine::where('user_id', $request->user()->id)
            ->with('steps')
            ->get();

        return RoutineResource::collection($routines);
    }

    public function store(StoreRoutineRequest $request): RoutineResource
    {
        $validated = $request->validated();
        $routineData = collect($validated)->except('steps')->toArray();

        $routine = $this->routineService->createRoutine($request->user(), $routineData);

        if (! empty($validated['steps'])) {
            foreach ($validated['steps'] as $step) {
                $routine->steps()->create($step);
            }
        }

        return new RoutineResource($routine->load('steps'));
    }

    public function update(Request $request, Routine $routine): RoutineResource
    {
        if ($routine->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'frequency' => ['sometimes', 'required', 'string', 'in:daily,weekdays,weekends,weekly,custom'],
            'custom_days' => ['nullable', 'array'],
            'custom_days.*' => ['integer', 'min:0', 'max:6'],
            'time_block' => ['sometimes', 'required', 'string', 'in:morning,afternoon,evening,anytime'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $routine->update($validated);

        return new RoutineResource($routine->load('steps'));
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        // First ensure today's routine instances are generated
        $this->routineService->generateInstancesForDate($request->user(), now());

        $instances = $this->routineService->getTodayRoutines($request->user());

        return RoutineInstanceResource::collection($instances);
    }

    public function completeStep(Request $request, RoutineInstance $instance, RoutineStep $step): RoutineInstanceResource
    {
        if ($instance->user_id !== $request->user()->id) {
            abort(403);
        }

        // Verify the step belongs to the routine
        if ($step->routine_id !== $instance->routine_id) {
            abort(400, 'Step does not belong to this routine instance.');
        }

        $updatedInstance = $this->routineService->completeStep($instance, $step->id);

        return new RoutineInstanceResource($updatedInstance->load('routine.steps'));
    }

    public function skipInstance(Request $request, RoutineInstance $instance): RoutineInstanceResource
    {
        if ($instance->user_id !== $request->user()->id) {
            abort(403);
        }

        $updatedInstance = $this->routineService->skipRoutine($instance);

        return new RoutineInstanceResource($updatedInstance->load('routine.steps'));
    }
}
