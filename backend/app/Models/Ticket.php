<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'title',
        'description',
        'priority',
        'status',
        'category_id',
        'requester_id',
        'agent_id',
        'sla_policy_id',
        'response_due_at',
        'responded_at',
        'resolution_due_at',
        'resolved_at',
        'closed_at',
        'is_sla_breached',
    ];

    protected $casts = [
        'priority' => TicketPriority::class,
        'status' => TicketStatus::class,
        'response_due_at' => 'datetime',
        'responded_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_sla_breached' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->orderBy('created_at', 'desc');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value]);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isManager()) {
            return $query;
        }

        if ($user->isAgent()) {
            return $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->id)
                  ->orWhereNull('agent_id');
            });
        }

        return $query->where('requester_id', $user->id);
    }

    public function scopeAtRisk(Builder $query, int $thresholdMinutes = 30): Builder
    {
        $now = Carbon::now();
        $threshold = Carbon::now()->addMinutes($thresholdMinutes);

        return $query->open()
            ->where(function ($q) use ($now, $threshold) {
                // At risk of response breach (not responded yet and response_due_at is near)
                $q->where(function ($sub) use ($now, $threshold) {
                    $sub->whereNull('responded_at')
                        ->whereNotNull('response_due_at')
                        ->where('response_due_at', '>', $now)
                        ->where('response_due_at', '<=', $threshold);
                })
                // Or at risk of resolution breach
                ->orWhere(function ($sub) use ($now, $threshold) {
                    $sub->whereNotNull('resolution_due_at')
                        ->where('resolution_due_at', '>', $now)
                        ->where('resolution_due_at', '<=', $threshold);
                });
            });
    }

    public function scopeBreached(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where(function ($q) use ($now) {
            $q->where('is_sla_breached', true)
              ->orWhere(function ($sub) use ($now) {
                  $sub->open()
                      ->where(function ($inner) use ($now) {
                          $inner->where(function ($res) use ($now) {
                              $res->whereNull('responded_at')
                                  ->whereNotNull('response_due_at')
                                  ->where('response_due_at', '<', $now);
                          })->orWhere(function ($resolv) use ($now) {
                              $resolv->whereNotNull('resolution_due_at')
                                     ->where('resolution_due_at', '<', $now);
                          });
                      });
              });
        });
    }

    /**
     * Helper to compute remaining minutes to resolution deadline
     */
    public function getResolutionRemainingMinutesAttribute(): ?int
    {
        if (!$this->resolution_due_at || $this->resolved_at) {
            return null;
        }

        return (int) Carbon::now()->diffInMinutes($this->resolution_due_at, false);
    }

    /**
     * Check if ticket is currently at risk (<30 min remaining)
     */
    public function getIsAtRiskAttribute(): bool
    {
        if ($this->status->isCompleted() || $this->is_sla_breached) {
            return false;
        }

        $now = Carbon::now();
        if (!$this->responded_at && $this->response_due_at) {
            $diff = $now->diffInMinutes($this->response_due_at, false);
            if ($diff > 0 && $diff <= 30) {
                return true;
            }
        }

        if ($this->resolution_due_at) {
            $diff = $now->diffInMinutes($this->resolution_due_at, false);
            if ($diff > 0 && $diff <= 30) {
                return true;
            }
        }

        return false;
    }
}
