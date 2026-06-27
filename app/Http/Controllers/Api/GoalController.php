<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GoalController extends Controller
{
    public function __construct(
        protected readonly GoalService $goalService,
    ) {}

    
    public function index(Request $request): JsonResponse
    {
        $goals = $this->goalService->getGoalsForUser($request->user());

        return GoalResource::collection($goals)->response();
    }

    public function store(StoreGoalRequest $request): GoalResource
    {
        $goal = $this->goalService->createGoal($request->user(), $request->validated());

        return new GoalResource($goal);
    }

    public function show(Request $request, Goal $goal): GoalResource
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $goal->load(['tasks.attribute', 'routines.steps']);

        return new GoalResource($goal);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): GoalResource
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $updatedGoal = $this->goalService->updateGoal($goal, $request->validated());

        return new GoalResource($updatedGoal);
    }

    public function destroy(Request $request, Goal $goal): Response
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        $goal->delete();

        return response()->noContent();
    }
}
