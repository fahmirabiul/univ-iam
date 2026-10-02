<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\OAuth\OAuthClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Tests\TestCase;

class OAuthClientServiceTest extends TestCase
{
    use RefreshDatabase;

    private OAuthClientService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OAuthClientService::class);
    }

    public function test_service_can_create_authorization_code_client_with_uuid_and_secret(): void
    {
        /** @var User $owner */
        $owner = User::create([
            'email' => 'admin@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $client = $this->service->createAuthorizationCodeClient(
            name: 'Knowledge Hub Application',
            redirectUris: ['http://localhost:8001/callback', 'https://oauth.pstmn.io/v1/callback'],
            confidential: true,
            user: $owner,
        );

        $this->assertInstanceOf(Client::class, $client);
        $this->assertNotEmpty($client->id);
        $this->assertSame('Knowledge Hub Application', $client->name);
        $this->assertNotEmpty($client->secret);
        $this->assertFalse($client->revoked);

        $this->assertDatabaseHas('oauth_clients', [
            'id' => $client->id,
            'name' => 'Knowledge Hub Application',
            'revoked' => false,
        ]);
    }

    public function test_service_can_regenerate_client_secret(): void
    {
        $client = $this->service->createAuthorizationCodeClient(
            name: 'SIAKAD Mobile',
            redirectUris: 'http://localhost:8002/auth/callback',
            confidential: true,
        );

        $oldSecret = $client->secret;

        $newSecret = $this->service->regenerateSecret($client);

        $this->assertNotEmpty($newSecret);
        $this->assertNotEquals($oldSecret, $newSecret);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($newSecret, $client->fresh()->secret));
    }

    public function test_service_can_revoke_client(): void
    {
        $client = $this->service->createAuthorizationCodeClient(
            name: 'Old Deprecated App',
            redirectUris: 'http://localhost:9999/callback',
        );

        $this->assertFalse($client->revoked);

        $this->service->deleteClient($client);

        $this->assertTrue($client->fresh()->revoked);
        $this->assertNull($this->service->findActiveClient($client->id));
    }

    public function test_service_can_paginate_active_clients(): void
    {
        $this->service->createAuthorizationCodeClient(
            name: 'App One',
            redirectUris: 'http://localhost:8001/callback',
        );

        $this->service->createAuthorizationCodeClient(
            name: 'App Two',
            redirectUris: 'http://localhost:8002/callback',
        );

        $revoked = $this->service->createAuthorizationCodeClient(
            name: 'Revoked App',
            redirectUris: 'http://localhost:8003/callback',
        );
        $this->service->deleteClient($revoked);

        $paginator = $this->service->getPaginatedClients(10);

        $this->assertEquals(2, $paginator->total());
    }
}
