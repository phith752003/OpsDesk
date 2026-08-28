<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isStaff = $user?->isStaff() ?? false;

        $comments = $this->comments;
        if (!$isStaff) {
            $comments = $comments->where('is_internal', false);
        }

        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority?->value ?? $this->priority,
            'priority_label' => $this->priority?->label(),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'category' => new CategoryResource($this->category),
            'requester' => new UserResource($this->requester),
            'agent' => $this->agent ? new UserResource($this->agent) : null,
            'sla_policy' => $this->slaPolicy ? new SlaPolicyResource($this->slaPolicy) : null,
            'response_due_at' => $this->response_due_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
            'resolution_due_at' => $this->resolution_due_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'is_sla_breached' => $this->is_sla_breached,
            'is_at_risk' => $this->is_at_risk,
            'resolution_remaining_minutes' => $this->resolution_remaining_minutes,
            'comments' => CommentResource::collection($comments),
            'attachments' => $this->attachments->map(fn($att) => [
                'id' => $att->id,
                'file_name' => $att->file_name,
                'file_path' => $att->file_path,
                'file_type' => $att->file_type,
                'file_size' => $att->file_size,
                'created_at' => $att->created_at->toIso8601String(),
            ]),
            'activity_logs' => ActivityLogResource::collection($this->activityLogs),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
