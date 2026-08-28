<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_metrics_returns_real_aggregates(): void
    {
        $admin = User::where('email', 'admin@opsdesk.local')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_tickets',
                'open_tickets',
                'unassigned_tickets',
                'in_progress_tickets',
                'breached_tickets',
                'sla_compliance_percentage',
                'avg_response_time_minutes',
                'avg_resolution_time_minutes',
                'tickets_by_priority',
                'tickets_by_status',
            ]);

        $this->assertGreaterThan(0, $response->json('total_tickets'));
        $this->assertGreaterThan(0, $response->json('open_tickets'));
    }

    public function test_agent_workload_and_reports_endpoints(): void
    {
        $manager = User::where('email', 'manager@opsdesk.local')->first();

        $workloadResponse = $this->actingAs($manager, 'sanctum')
            ->getJson('/api/v1/reports/workload');

        $workloadResponse->assertStatus(200)
            ->assertJsonStructure(['workload']);

        $trendsResponse = $this->actingAs($manager, 'sanctum')
            ->getJson('/api/v1/reports/trends');

        $trendsResponse->assertStatus(200)
            ->assertJsonStructure([
                'category_breakdown',
                'volume_trend',
                'sla_by_priority',
            ]);
    }
}
