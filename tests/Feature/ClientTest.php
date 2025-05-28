<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }    public function test_can_list_clients(): void
    {
        Client::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/clients');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'contact_person', 'hourly_rate']
                ]
            ]);
    }

    public function test_can_create_client(): void
    {
        $clientData = [
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'phone' => '123-456-7890',
            'address' => '123 Test St',
            'hourly_rate' => 75.00,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/clients', $clientData);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Test Client']);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_show_client(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/clients/{$client->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $client->id]);
    }    public function test_can_update_client(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Client Name',
            'email' => $client->email // Include existing email to avoid validation error
        ];

        $response = $this->putJson("/api/clients/{$client->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Updated Client Name']);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Client Name'
        ]);
    }    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/clients/{$client->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_cannot_access_other_users_clients(): void
    {
        $otherUser = User::factory()->create();
        $otherClient = Client::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/clients/{$otherClient->id}");

        $response->assertForbidden();
    }    public function test_client_validation_rules(): void
    {
        $response = $this->postJson('/api/clients', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_client_email_must_be_unique_per_user(): void
    {
        Client::factory()->create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/clients', [
            'name' => 'Another Client',
            'email' => 'test@example.com',
            'hourly_rate' => 50.00,
            'status' => 'active'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
