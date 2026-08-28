<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketStatusRequest;
use App\Http\Resources\TicketDetailResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
    ) {
        $this->authorizeResource(Ticket::class, 'ticket');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Ticket::with(['requester', 'agent', 'category', 'slaPolicy'])
            ->forUser($user);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('agent_id')) {
            if ($request->agent_id === 'unassigned') {
                $query->whereNull('agent_id');
            } else {
                $query->where('agent_id', $request->agent_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return TicketResource::collection($tickets);
    }

    public function store(CreateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->createTicket(
            $request->validated(),
            $request->user()
        );

        $ticket->load(['requester', 'agent', 'category', 'slaPolicy']);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket' => new TicketResource($ticket),
        ], 201);
    }

    public function show(Ticket $ticket): TicketDetailResource
    {
        $ticket->load([
            'requester.department',
            'agent.department',
            'category',
            'slaPolicy',
            'comments.user',
            'attachments',
            'activityLogs.user',
        ]);

        return new TicketDetailResource($ticket);
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('updateStatus', $ticket);

        $status = TicketStatus::from($request->status);
        $updated = $this->ticketService->updateStatus($ticket, $status, $request->user());

        $updated->load(['requester', 'agent', 'category', 'slaPolicy']);

        return response()->json([
            'message' => "Ticket status updated to {$status->label()}.",
            'ticket' => new TicketResource($updated),
        ]);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('assign', $ticket);

        $agent = $request->agent_id ? User::find($request->agent_id) : null;
        $updated = $this->ticketService->assignTicket($ticket, $agent, $request->user());

        $updated->load(['requester', 'agent', 'category', 'slaPolicy']);

        return response()->json([
            'message' => $agent ? "Ticket assigned to {$agent->name}." : 'Ticket unassigned.',
            'ticket' => new TicketResource($updated),
        ]);
    }

    public function updatePriority(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
        ]);

        $priority = TicketPriority::from($request->priority);
        $updated = $this->ticketService->updatePriority($ticket, $priority, $request->user());

        $updated->load(['requester', 'agent', 'category', 'slaPolicy']);

        return response()->json([
            'message' => "Ticket priority updated to {$priority->label()}.",
            'ticket' => new TicketResource($updated),
        ]);
    }
}
