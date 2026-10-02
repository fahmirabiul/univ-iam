<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOAuthClientRequest;
use App\Services\OAuth\OAuthClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\Client;

class OAuthClientController extends Controller
{
    public function __construct(
        private readonly OAuthClientService $oauthClientService,
    ) {}

    /**
     * Display a listing of registered OAuth2 clients.
     */
    public function index(Request $request): View
    {
        $clients = $this->oauthClientService->getPaginatedClients(10);

        return view('admin.clients.index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created OAuth2 client in storage.
     */
    public function store(StoreOAuthClientRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $client = $this->oauthClientService->createAuthorizationCodeClient(
            name: (string) $request->validated('name'),
            redirectUris: $request->getRedirectUris(),
            confidential: $request->boolean('confidential', true),
            user: $user,
        );

        return redirect()
            ->route('admin.clients.index')
            ->with('success', "Aplikasi klien '{$client->name}' berhasil didaftarkan.")
            ->with('new_client_id', (string) $client->id)
            ->with('new_client_secret', (string) ($client->plainSecret ?? $client->secret))
            ->with('new_client_name', (string) $client->name);
    }

    /**
     * Regenerate secret for a confidential OAuth2 client.
     */
    public function regenerateSecret(Client $client): RedirectResponse
    {
        $newSecret = $this->oauthClientService->regenerateSecret($client);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', "Secret untuk '{$client->name}' berhasil diperbarui.")
            ->with('new_client_id', (string) $client->id)
            ->with('new_client_secret', $newSecret)
            ->with('new_client_name', (string) $client->name);
    }

    /**
     * Revoke the specified OAuth2 client.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->oauthClientService->deleteClient($client);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', "Aplikasi klien '{$client->name}' berhasil dicabut (revoked).");
    }
}
