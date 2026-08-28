<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'action' => $this->action?->value ?? $this->action,
            'metadata' => $this->metadata,
            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role?->value ?? $this->user->role,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
