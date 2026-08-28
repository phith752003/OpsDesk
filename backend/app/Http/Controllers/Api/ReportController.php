<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected MetricService $metricService,
    ) {}

    public function workload(Request $request): JsonResponse
    {
        $workload = $this->metricService->getAgentWorkload();

        return response()->json([
            'workload' => $workload,
        ]);
    }

    public function trends(Request $request): JsonResponse
    {
        $trends = $this->metricService->getReportsTrends();

        return response()->json($trends);
    }
}
