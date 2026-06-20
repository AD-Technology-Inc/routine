<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected readonly AnalyticsService $analyticsService,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 30);
        $trend = $this->analyticsService->getCompletionTrend($request->user(), $days);

        return response()->json($trend);
    }

    public function heatmap(Request $request): JsonResponse
    {
        $heatmap = $this->analyticsService->getWeekdayHeatmap($request->user());

        return response()->json($heatmap);
    }

    public function energyPerformance(Request $request): JsonResponse
    {
        $performance = $this->analyticsService->getEnergyPerformance($request->user());

        return response()->json($performance);
    }
}
