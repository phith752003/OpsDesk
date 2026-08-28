<?php

namespace Tests\Feature;

use App\Models\SlaPolicy;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_non_admin_cannot_create_users(): void
    {
        $agent = User::where('email', 'agent@opsdesk.local')->first();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@opsdesk.local',
                'password' => 'Password123!',
                'role' => 'agent',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_users(): void
    {
        $admin = User::where('email', 'admin@opsdesk.local')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Support Engineer',
                'email' => 'support.eng@opsdesk.local',
                'password' => 'Password123!',
                'role' => 'agent',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.email', 'support.eng@opsdesk.local')
            ->assertJsonPath('user.role', 'agent');
    }

    public function test_non_admin_cannot_update_sla_policies(): void
    {
        $requester = User::where('email', 'requester@opsdesk.local')->first();
        $policy = SlaPolicy::first();

        $response = $this->actingAs($requester, 'sanctum')
            ->putJson("/api/v1/sla/policies/{$policy->id}", [
                'response_time_minutes' => 5,
            ]);

        $response->assertStatus(403);
    }
}
