<?php

declare(strict_types=1);

namespace Tests\Feature\OAuth;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\OAuth\OAuthClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Tests\TestCase;

class OAuthConsentScreenTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'dosen', 'description' => 'Dosen']);

        $this->dosen = User::create([
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->dosen->roles()->attach($role->id);

        UserProfile::create([
            'user_id' => $this->dosen->id,
            'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
            'nomor_induk' => '198001012005011001',
            'fakultas' => 'Fakultas Teknologi Informasi',
            'program_studi' => 'Teknik Informatika',
            'status_akademik' => 'aktif',
        ]);

        /** @var OAuthClientService $service */
        $service = app(OAuthClientService::class);
        $this->client = $service->createAuthorizationCodeClient(
            name: 'Knowledge Hub Application',
            redirectUris: 'http://localhost:8001/callback',
        );
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://localhost:8001/callback',
            'response_type' => 'code',
            'state' => 'test_state_123',
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_custom_vuexy_consent_screen(): void
    {
        $response = $this->actingAs($this->dosen)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://localhost:8001/callback',
            'response_type' => 'code',
            'state' => 'test_state_123',
        ]));

        $response->assertOk()
            ->assertSee('Permintaan Otorisasi Akses')
            ->assertSee('Knowledge Hub Application')
            ->assertSee('Dr. Fahmi R., M.Kom.')
            ->assertSee('fahmi.dosen@univ.ac.id')
            ->assertSee('Izinkan')
            ->assertSee('Tolak');
    }

    public function test_user_can_approve_authorization_and_receive_auth_code(): void
    {
        // 1. Visit consent screen to initialize session authToken
        $this->actingAs($this->dosen)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://localhost:8001/callback',
            'response_type' => 'code',
            'state' => 'state_abc',
        ]));

        $authToken = session('authToken');
        $this->assertNotEmpty($authToken);

        // 2. Approve authorization
        $approveResponse = $this->actingAs($this->dosen)->post('/oauth/authorize', [
            'state' => 'state_abc',
            'client_id' => $this->client->id,
            'auth_token' => $authToken,
        ]);

        $approveResponse->assertRedirect();
        $targetUrl = (string) $approveResponse->headers->get('Location');

        $this->assertStringStartsWith('http://localhost:8001/callback', $targetUrl);
        $this->assertStringContainsString('code=', $targetUrl);
        $this->assertStringContainsString('state=state_abc', $targetUrl);
    }

    public function test_user_can_deny_authorization_and_be_redirected_with_access_denied(): void
    {
        // 1. Visit consent screen
        $this->actingAs($this->dosen)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://localhost:8001/callback',
            'response_type' => 'code',
            'state' => 'state_deny',
        ]));

        $authToken = session('authToken');

        // 2. Deny authorization
        $denyResponse = $this->actingAs($this->dosen)->delete('/oauth/authorize', [
            'state' => 'state_deny',
            'client_id' => $this->client->id,
            'auth_token' => $authToken,
        ]);

        $denyResponse->assertRedirect();
        $targetUrl = (string) $denyResponse->headers->get('Location');

        $this->assertStringStartsWith('http://localhost:8001/callback', $targetUrl);
        $this->assertStringContainsString('error=access_denied', $targetUrl);
    }
}
