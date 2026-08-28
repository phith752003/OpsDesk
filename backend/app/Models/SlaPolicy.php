<?php

namespace App\Models;

use App\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'response_time_minutes',
        'resolution_time_minutes',
        'is_default',
    ];

    protected $casts = [
        'priority' => TicketPriority::class,
        'response_time_minutes' => 'integer',
        'resolution_time_minutes' => 'integer',
        'is_default' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
