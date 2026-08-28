<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_requester_can_create_ticket(): void
    {
        $requester = User::where('email', 'requester@opsdesk.local')->first();
        $category = Category::first();

        $response = $this->actingAs($requester, 'sanctum')
            ->postJson('/api/v1/tickets', [
                'title' => 'Cannot print to 3rd floor LaserJet',
                'description' => 'Print jobs remain queued indefinitely in Windows print spooler.',
                'category_id' => $category->id,
                'priority' => 'medium',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('ticket.title', 'Cannot print to 3rd floor LaserJet')
            ->assertJsonPath('ticket.priority', 'medium')
            ->assertJsonPath('ticket.status', 'open');

        $this->assertDatabaseHas('tickets', [
            'title' => 'Cannot print to 3rd floor LaserJet',
            'requester_id' => $requester->id,
        ]);
    }

    public function test_requester_cannot_view_another_users_private_ticket(): void
    {
        $requester = User::where('email', 'requester@opsdesk.local')->first();
        $otherRequester = User::where('email', 'michael.c@opsdesk.local')->first();

        $ticket = Ticket::where('requester_id', $otherRequester->id)->first();

        $response = $this->actingAs($requester, 'sanctum')
            ->getJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }

    public function test_agent_can_update_ticket_status(): void
    {
        $agent = User::where('email', 'agent@opsdesk.local')->first();
        $ticket = Ticket::where('status', 'open')->first();

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/v1/tickets/{$ticket->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('ticket.status', 'in_progress');

        $this->assertEquals('in_progress', $ticket->fresh()->status->value);
    }

    public function test_agent_can_assign_ticket(): void
    {
        $admin = User::where('email', 'admin@opsdesk.local')->first();
        $agent = User::where('email', 'agent@opsdesk.local')->first();
        $ticket = Ticket::whereNull('agent_id')->first() ?: Ticket::first();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/tickets/{$ticket->id}/assign", [
                'agent_id' => $agent->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('ticket.agent.id', $agent->id);

        $this->assertEquals($agent->id, $ticket->fresh()->agent_id);
    }
}
