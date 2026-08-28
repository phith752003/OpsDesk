<?php

namespace Tests\Feature;

use App\Enums\TicketPriority;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_sla_deadlines_calculated_correctly_for_critical(): void
    {
        $slaService = app(SlaService::class);
        $start = Carbon::now();

        $deadlines = $slaService->calculateDeadlines(TicketPriority::CRITICAL, $start);

        // Critical: response 15 min, resolution 120 min
        $this->assertEquals(15, $start->diffInMinutes($deadlines['response_due_at']));
        $this->assertEquals(120, $start->diffInMinutes($deadlines['resolution_due_at']));
    }

    public function test_at_risk_and_breached_tickets_endpoint_returns_data(): void
    {
        $agent = User::where('email', 'agent@opsdesk.local')->first();

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson('/api/v1/sla/at-risk?threshold=60');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tickets',
                'threshold_minutes',
            ]);

        $this->assertGreaterThanOrEqual(1, count($response->json('tickets')));
    }
}
