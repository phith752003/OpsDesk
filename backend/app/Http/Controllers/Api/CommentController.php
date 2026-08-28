<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AddCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
    ) {}

    public function store(AddCommentRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $isInternal = (bool) $request->input('is_internal', false);
        if ($isInternal) {
            $this->authorize('addInternalNote', $ticket);
        }

        $comment = $this->ticketService->addComment(
            $ticket,
            $request->body,
            $isInternal,
            $request->user()
        );

        $comment->load('user');

        return response()->json([
            'message' => $isInternal ? 'Internal note added.' : 'Comment added.',
            'comment' => new CommentResource($comment),
        ], 201);
    }
}
