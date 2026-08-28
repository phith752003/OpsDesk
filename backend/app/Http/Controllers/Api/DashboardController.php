<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected MetricService $metricService,
    ) {}

    public function metrics(Request $request): JsonResponse
    {
        $metrics = $this->metricService->getDashboardMetrics($request->user());

        return response()->json($metrics);
    }
}
