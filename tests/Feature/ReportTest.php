<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id
        ]);
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_project_reports(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/reports?type=project');

        $response->assertOk()
            ->assertJsonStructure([
                'type',
                'data' => [
                    '*' => ['project_id', 'project_name', 'client_name', 'total_hours']
                ]
            ]);
    }

    public function test_can_get_client_reports(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/reports?type=client');

        $response->assertOk()
            ->assertJsonStructure([
                'type',
                'data' => [
                    '*' => ['client_id', 'client_name', 'total_hours', 'projects_count']
                ]
            ]);
    }

    public function test_can_get_daily_reports(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/reports?type=daily');

        $response->assertOk()
            ->assertJsonStructure([
                'type',
                'data' => [
                    '*' => ['date', 'total_hours', 'logs_count']
                ]
            ]);
    }

    public function test_can_get_weekly_reports(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/reports?type=weekly');

        $response->assertOk()
            ->assertJsonStructure([
                'type',
                'data' => [
                    '*' => ['week', 'week_start', 'week_end', 'total_hours']
                ]
            ]);
    }

    public function test_can_get_user_summary(): void
    {
        TimeLog::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/reports/summary');

        $response->assertOk()
            ->assertJsonStructure([
                'summary' => [
                    'total_hours',
                    'total_earnings',
                    'total_projects',
                    'total_clients',
                    'average_hours_per_day'
                ]
            ]);
    }

    public function test_can_filter_reports_by_date_range(): void
    {
        TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'start_time' => '2025-05-01 09:00:00'
        ]);

        TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'start_time' => '2025-05-15 09:00:00'
        ]);

        $response = $this->getJson('/api/reports?type=daily&from=2025-05-01&to=2025-05-10');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_get_client_specific_report(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson("/api/reports/client/{$this->client->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'client_id',
                'data' => ['client_id', 'client_name', 'total_hours']
            ]);
    }

    public function test_can_export_pdf_report(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/reports/export/pdf?group_by=daily');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_report_validation(): void
    {
        $response = $this->getJson('/api/reports');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_cannot_access_other_users_reports(): void
    {
        $otherUser = User::factory()->create();
        $otherClient = Client::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/reports/client/{$otherClient->id}");

        $response->assertNotFound();
    }
}
