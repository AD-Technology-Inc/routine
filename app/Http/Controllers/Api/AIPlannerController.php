<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AIPlanGoalJob;
use App\Jobs\AIReviewWeekJob;
use App\Models\Goal;
use App\Services\AIPlannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AIPlannerController extends Controller
{
    public function __construct(
        protected readonly AIPlannerService $aiPlannerService,
    ) {}

    public function planGoal(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        AIPlanGoalJob::dispatch($goal, $request->user());

        return response()->json([
            'message' => 'AI Goal planning has been queued.',
        ], 202);
    }

    public function reviewWeek(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            abort(403);
        }

        AIReviewWeekJob::dispatch($request->user());

        return response()->json([
            'message' => 'AI Weekly review has been queued.',
        ], 202);
    }

    public function getWeeklyReview(Request $request): JsonResponse
    {
        $review = Cache::get("weekly_review:{$request->user()->id}");

        if (! $review) {
            return response()->json([
                'message' => 'No weekly review generated yet. Please trigger one.',
            ], 404);
        }

        return response()->json($review);
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string'],
            'context' => ['nullable', 'array'],
        ]);

        $message = $request->input('message');
        $context = $request->input('context', []);

        if (empty($context)) {
            $context = [
                'goals' => $request->user()->goals()->where('status', 'active')->get()->toArray(),
            ];
        }

        $reply = $this->aiPlannerService->chatResponse($request->user(), $message, $context);

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
