<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\OAuth\OAuthClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Tests\TestCase;

class OAuthClientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'super_admin', 'description' => 'Super Admin']);
        $dosenRole = Role::create(['name' => 'dosen', 'description' => 'Dosen']);

        $this->admin = User::create([
            'email' => 'admin.sdm@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id);

        UserProfile::create([
            'user_id' => $this->admin->id,
            'nama_lengkap' => 'Admin SDM Ekosistem',
        ]);

        $this->dosen = User::create([
            'email' => 'dosen@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->dosen->roles()->attach($dosenRole->id);
    }

    public function test_admin_can_view_oauth_clients_index_page(): void
    {
        /** @var OAuthClientService $service */
        $service = app(OAuthClientService::class);
        $service->createAuthorizationCodeClient(
            name: 'Knowledge Hub Application',
            redirectUris: 'http://localhost:8001/callback',
        );

        $response = $this->actingAs($this->admin)->get('/admin/clients');

        $response->assertOk()
            ->assertSee('Manajemen Aplikasi Klien OAuth2')
            ->assertSee('Knowledge Hub Application')
            ->assertSee('http://localhost:8001/callback');
    }

    public function test_admin_can_store_new_oauth_client_and_see_flash_credentials(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'SIAKAD Mobile App',
            'redirect' => 'http://localhost:8002/auth/callback, https://oauth.pstmn.io/v1/callback',
            'confidential' => '1',
        ]);

        $response->assertRedirect(route('admin.clients.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('new_client_id')
            ->assertSessionHas('new_client_secret');

        $this->assertDatabaseHas('oauth_clients', [
            'name' => 'SIAKAD Mobile App',
            'revoked' => false,
        ]);
    }

    public function test_admin_can_regenerate_client_secret(): void
    {
        /** @var OAuthClientService $service */
        $service = app(OAuthClientService::class);
        $client = $service->createAuthorizationCodeClient(
            name: 'E-Library App',
            redirectUris: 'http://localhost:8003/callback',
        );

        $oldSecret = $client->secret;

        $response = $this->actingAs($this->admin)->post("/admin/clients/{$client->id}/regenerate-secret");

        $response->assertRedirect(route('admin.clients.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('new_client_secret');

        $client->refresh();
        $this->assertNotEquals($oldSecret, $client->secret);
    }

    public function test_admin_can_revoke_client(): void
    {
        /** @var OAuthClientService $service */
        $service = app(OAuthClientService::class);
        $client = $service->createAuthorizationCodeClient(
            name: 'Legacy Service',
            redirectUris: 'http://localhost:9000/callback',
        );

        $response = $this->actingAs($this->admin)->delete("/admin/clients/{$client->id}");

        $response->assertRedirect(route('admin.clients.index'))
            ->assertSessionHas('success');

        $this->assertTrue($client->fresh()->revoked);
    }

    public function test_dosen_cannot_access_oauth_clients_management(): void
    {
        $response = $this->actingAs($this->dosen)->get('/admin/clients');

        $response->assertForbidden();
    }
}
