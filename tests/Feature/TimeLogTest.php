<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TimeLogTest extends TestCase
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

    public function test_can_list_time_logs(): void
    {
        TimeLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/time-logs');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'description', 'start_time', 'end_time', 'hours', 'project']
                ]
            ]);
    }

    public function test_can_create_time_log(): void
    {
        $timeLogData = [
            'project_id' => $this->project->id,
            'description' => 'Working on feature X',
            'start_time' => '2025-05-28 09:00:00',
            'end_time' => '2025-05-28 17:00:00'
        ];

        $response = $this->postJson('/api/time-logs', $timeLogData);

        $response->assertCreated()
            ->assertJsonFragment(['description' => 'Working on feature X']);

        $this->assertDatabaseHas('time_logs', [
            'description' => 'Working on feature X',
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);
    }

    public function test_can_start_time_log(): void
    {
        $timeLogData = [
            'project_id' => $this->project->id,
            'description' => 'Starting work session'
        ];

        $response = $this->postJson('/api/time-logs', $timeLogData);

        $response->assertCreated();
        $timeLog = $response->json('data');

        $this->assertNull($timeLog['end_time']);
        $this->assertNull($timeLog['hours']);
    }

    public function test_can_stop_time_log(): void
    {
        $timeLog = TimeLog::factory()->running()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->postJson("/api/time-logs/{$timeLog->id}/stop");

        $response->assertOk();
        
        $timeLog->refresh();
        $this->assertNotNull($timeLog->end_time);
        $this->assertNotNull($timeLog->hours);
    }

    public function test_can_get_running_time_logs(): void
    {
        TimeLog::factory()->running()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        TimeLog::factory()->completed()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/time-logs/running');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_time_log(): void
    {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson("/api/time-logs/{$timeLog->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $timeLog->id]);
    }

    public function test_can_update_time_log(): void
    {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $updateData = ['description' => 'Updated description'];

        $response = $this->putJson("/api/time-logs/{$timeLog->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment(['description' => 'Updated description']);
    }

    public function test_can_delete_time_log(): void
    {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->deleteJson("/api/time-logs/{$timeLog->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('time_logs', ['id' => $timeLog->id]);
    }

    public function test_cannot_access_other_users_time_logs(): void
    {
        $otherUser = User::factory()->create();
        $otherClient = Client::factory()->create(['user_id' => $otherUser->id]);
        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
            'client_id' => $otherClient->id
        ]);
        $otherTimeLog = TimeLog::factory()->create([
            'user_id' => $otherUser->id,
            'project_id' => $otherProject->id
        ]);

        $response = $this->getJson("/api/time-logs/{$otherTimeLog->id}");

        $response->assertForbidden();
    }

    public function test_time_log_validation_rules(): void
    {
        $response = $this->postJson('/api/time-logs', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['project_id', 'description']);
    }

    public function test_cannot_have_multiple_running_time_logs(): void
    {
        TimeLog::factory()->running()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->postJson('/api/time-logs', [
            'project_id' => $this->project->id,
            'description' => 'Another running log'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_time']);
    }
}
