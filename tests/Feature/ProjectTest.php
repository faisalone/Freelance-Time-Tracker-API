<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        Sanctum::actingAs($this->user);
    }    public function test_can_list_projects(): void
    {
        Project::factory()->count(3)->create([
            'client_id' => $this->client->id
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'status', 'client']
                ]
            ]);
    }    public function test_can_create_project(): void
    {
        $projectData = [
            'client_id' => $this->client->id,
            'title' => 'Test Project',
            'description' => 'A test project',
            'status' => 'active',
            'deadline' => '2025-06-01'
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'Test Project']);

        $this->assertDatabaseHas('projects', [
            'title' => 'Test Project',
            'client_id' => $this->client->id
        ]);
    }    public function test_can_show_project(): void
    {
        $project = Project::factory()->create([
            'client_id' => $this->client->id
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $project->id]);
    }    public function test_can_update_project(): void
    {
        $project = Project::factory()->create([
            'client_id' => $this->client->id
        ]);

        $updateData = ['title' => 'Updated Project Title'];

        $response = $this->putJson("/api/projects/{$project->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment(['title' => 'Updated Project Title']);
    }    public function test_can_delete_project(): void
    {
        $project = Project::factory()->create([
            'client_id' => $this->client->id
        ]);

        $response = $this->deleteJson("/api/projects/{$project->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }    public function test_cannot_access_other_users_projects(): void
    {
        $otherUser = User::factory()->create();
        $otherClient = Client::factory()->create(['user_id' => $otherUser->id]);
        $otherProject = Project::factory()->create([
            'client_id' => $otherClient->id
        ]);

        $response = $this->getJson("/api/projects/{$otherProject->id}");

        $response->assertForbidden();
    }    public function test_project_validation_rules(): void
    {
        $response = $this->postJson('/api/projects', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['client_id', 'title', 'status']);
    }    public function test_cannot_create_project_for_other_users_client(): void
    {
        $otherUser = User::factory()->create();
        $otherClient = Client::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->postJson('/api/projects', [
            'client_id' => $otherClient->id,
            'title' => 'Test Project',
            'status' => 'active'
        ]);

        $response->assertForbidden();
    }
}
