<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_requester_can_add_public_comment(): void
    {
        $requester = User::where('email', 'requester@opsdesk.local')->first();
        $ticket = Ticket::where('requester_id', $requester->id)->first();

        $response = $this->actingAs($requester, 'sanctum')
            ->postJson("/api/v1/tickets/{$ticket->id}/comments", [
                'body' => 'I verified that rebooting the router did not solve the issue.',
                'is_internal' => false,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('comment.body', 'I verified that rebooting the router did not solve the issue.')
            ->assertJsonPath('comment.is_internal', false);
    }

    public function test_requester_cannot_add_private_internal_note(): void
    {
        $requester = User::where('email', 'requester@opsdesk.local')->first();
        $ticket = Ticket::where('requester_id', $requester->id)->first();

        $response = $this->actingAs($requester, 'sanctum')
            ->postJson("/api/v1/tickets/{$ticket->id}/comments", [
                'body' => 'This should be forbidden for requesters.',
                'is_internal' => true,
            ]);

        $response->assertStatus(403);
    }

    public function test_agent_can_add_private_internal_note(): void
    {
        $agent = User::where('email', 'agent@opsdesk.local')->first();
        $ticket = Ticket::first();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/tickets/{$ticket->id}/comments", [
                'body' => 'Internal engineering note: investigated syslog and found port flapping.',
                'is_internal' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('comment.is_internal', true);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'is_internal' => true,
        ]);
    }

    public function test_requester_does_not_see_internal_notes_in_ticket_details(): void
    {
        $requester = User::where('email', 'requester@opsdesk.local')->first();
        $agent = User::where('email', 'agent@opsdesk.local')->first();
        $ticket = Ticket::where('requester_id', $requester->id)->first();

        // Create an internal note by agent
        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'body' => 'Super confidential internal note.',
            'is_internal' => true,
        ]);

        $response = $this->actingAs($requester, 'sanctum')
            ->getJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(200);

        $comments = $response->json('comments');
        foreach ($comments as $c) {
            $this->assertFalse($c['is_internal'], 'Requesters must never see internal notes.');
            $this->assertNotEquals('Super confidential internal note.', $c['body']);
        }
    }
}
