<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlaPolicyResource;
use App\Http\Resources\TicketResource;
use App\Models\SlaPolicy;
use App\Services\SlaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    public function __construct(
        protected SlaService $slaService,
    ) {}

    public function policies(): JsonResponse
    {
        $policies = SlaPolicy::orderBy('id')->get();

        return response()->json([
            'policies' => SlaPolicyResource::collection($policies),
        ]);
    }

    public function atRisk(Request $request): JsonResponse
    {
        $threshold = (int) $request->input('threshold', 30);
        $tickets = $this->slaService->getAtRiskAndBreachedTickets($threshold);

        return response()->json([
            'tickets' => TicketResource::collection($tickets),
            'threshold_minutes' => $threshold,
        ]);
    }

    public function updatePolicy(Request $request, SlaPolicy $slaPolicy): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'response_time_minutes' => ['sometimes', 'integer', 'min:1'],
            'resolution_time_minutes' => ['sometimes', 'integer', 'min:1'],
        ]);

        $slaPolicy->update($validated);

        return response()->json([
            'message' => 'SLA Policy updated successfully.',
            'policy' => new SlaPolicyResource($slaPolicy),
        ]);
    }
}
