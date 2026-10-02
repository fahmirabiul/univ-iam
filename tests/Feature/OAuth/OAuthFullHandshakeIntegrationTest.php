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

class OAuthFullHandshakeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;

    private Client $client;

    private string $clientSecret;

    private string $redirectUri = 'http://localhost:8001/callback';

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Global Role
        $role = Role::create([
            'name' => 'dosen',
            'description' => 'Dosen Pengajar Aktif',
        ]);

        // 2. Create User Account (UUID PK)
        $this->dosen = User::create([
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->dosen->roles()->attach($role->id);

        // 3. Create Demographic Master Profile
        UserProfile::create([
            'user_id' => $this->dosen->id,
            'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
            'nomor_induk' => '198001012005011001',
            'fakultas' => 'FTI',
            'program_studi' => 'Informatika',
            'status_akademik' => 'aktif',
        ]);

        // 4. Create OAuth2 Client (Knowledge Hub)
        /** @var OAuthClientService $clientService */
        $clientService = app(OAuthClientService::class);
        $this->client = $clientService->createAuthorizationCodeClient(
            name: 'Knowledge Hub Application',
            redirectUris: $this->redirectUri,
            confidential: true,
        );
        $this->clientSecret = (string) ($this->client->plainSecret ?? $this->client->secret);
    }

    /**
     * Test the entire End-to-End OAuth2 Authorization Code Grant cycle:
     * Request Authorize -> Approve Consent -> Receive Code -> Exchange Code for Token -> Fetch /api/user.
     */
    public function test_complete_oauth2_authorization_code_grant_and_sso_user_api_handshake(): void
    {
        // -------------------------------------------------------------
        // STEP 1: User visits authorization URL (SSO Consent Request)
        // -------------------------------------------------------------
        $authorizeResponse = $this->actingAs($this->dosen)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => 'random_state_xyz_123',
        ]));

        $authorizeResponse->assertOk()
            ->assertSee('Permintaan Otorisasi Akses')
            ->assertSee('Knowledge Hub Application');

        $authToken = session('authToken');
        $this->assertNotEmpty($authToken);

        // -------------------------------------------------------------
        // STEP 2: User Approves Consent Screen
        // -------------------------------------------------------------
        $approveResponse = $this->actingAs($this->dosen)->post('/oauth/authorize', [
            'state' => 'random_state_xyz_123',
            'client_id' => $this->client->id,
            'auth_token' => $authToken,
        ]);

        $approveResponse->assertRedirect();
        $targetLocation = (string) $approveResponse->headers->get('Location');

        // Extract Authorization Code from redirect URI query string
        $parsedUrl = parse_url($targetLocation);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $this->assertArrayHasKey('code', $queryParams);
        $this->assertSame('random_state_xyz_123', $queryParams['state'] ?? null);

        $authCode = (string) $queryParams['code'];
        $this->assertNotEmpty($authCode);

        // -------------------------------------------------------------
        // STEP 3: Client Backend exchanges Auth Code for Access Token
        // -------------------------------------------------------------
        $tokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $authCode,
        ]);

        $tokenResponse->assertOk()
            ->assertJsonStructure([
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token',
            ]);

        $accessToken = $tokenResponse->json('access_token');
        $refreshToken = $tokenResponse->json('refresh_token');

        $this->assertSame('Bearer', $tokenResponse->json('token_type'));
        $this->assertNotEmpty($accessToken);
        $this->assertNotEmpty($refreshToken);

        // -------------------------------------------------------------
        // STEP 4: Client Backend calls GET /api/user with Bearer Token
        // -------------------------------------------------------------
        $userApiResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $userApiResponse->assertOk()
            ->assertJson([
                'sso_id' => $this->dosen->id,
                'email' => 'fahmi.dosen@univ.ac.id',
                'role_global' => 'dosen',
                'profil' => [
                    'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
                    'nomor_induk' => '198001012005011001',
                    'fakultas' => 'FTI',
                    'program_studi' => 'Informatika',
                    'status_akademik' => 'aktif',
                ],
            ]);
    }

    /**
     * Test that token exchange is rejected if an invalid client secret is provided.
     */
    public function test_token_exchange_fails_with_invalid_client_secret(): void
    {
        // 1. Get auth code
        $this->actingAs($this->dosen)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => 'state_err',
        ]));

        $approveResponse = $this->actingAs($this->dosen)->post('/oauth/authorize', [
            'state' => 'state_err',
            'client_id' => $this->client->id,
            'auth_token' => session('authToken'),
        ]);

        parse_str((string) parse_url((string) $approveResponse->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $authCode = (string) $queryParams['code'];

        // 2. Exchange with invalid secret
        $tokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => 'wrong_invalid_secret_key_123',
            'redirect_uri' => $this->redirectUri,
            'code' => $authCode,
        ]);

        $tokenResponse->assertStatus(401)
            ->assertJson([
                'error' => 'invalid_client',
            ]);
    }

    /**
     * Test that token exchange is rejected if an invalid or already-used auth code is provided.
     */
    public function test_token_exchange_fails_with_invalid_auth_code(): void
    {
        $tokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => 'non_existent_or_expired_code',
        ]);

        $tokenResponse->assertStatus(400)
            ->assertJson([
                'error' => 'invalid_grant',
            ]);
    }

    /**
     * Test that a refresh token can be used to obtain a new access token.
     */
    public function test_refresh_token_grant_issues_new_access_token(): void
    {
        // 1. Complete authorization flow to get initial tokens
        $this->actingAs($this->dosen)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => 'refresh_state',
        ]));

        $approveResponse = $this->actingAs($this->dosen)->post('/oauth/authorize', [
            'state' => 'refresh_state',
            'client_id' => $this->client->id,
            'auth_token' => session('authToken'),
        ]);

        parse_str((string) parse_url((string) $approveResponse->headers->get('Location'), PHP_URL_QUERY), $queryParams);
        $authCode = (string) $queryParams['code'];

        $initialTokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $authCode,
        ]);

        $refreshToken = (string) $initialTokenResponse->json('refresh_token');

        // 2. Exchange refresh token for new access token
        $refreshTokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
        ]);

        $refreshTokenResponse->assertOk()
            ->assertJsonStructure([
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token',
            ]);

        $newAccessToken = (string) $refreshTokenResponse->json('access_token');

        // 3. Verify that the new access token can query /api/user
        $userApiResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newAccessToken,
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $userApiResponse->assertOk()
            ->assertJsonPath('email', 'fahmi.dosen@univ.ac.id');
    }
}
