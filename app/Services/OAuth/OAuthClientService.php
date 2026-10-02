<?php

declare(strict_types=1);

namespace App\Services\OAuth;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class OAuthClientService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {}

    /**
     * Retrieve paginated list of non-revoked OAuth2 clients.
     */
    public function getPaginatedClients(int $perPage = 10): LengthAwarePaginator
    {
        return Passport::client()
            ->newQuery()
            ->where('revoked', false)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new Authorization Code Grant OAuth2 client.
     *
     * @param  list<string>|string  $redirectUris
     */
    public function createAuthorizationCodeClient(
        string $name,
        array|string $redirectUris,
        bool $confidential = true,
        ?User $user = null,
    ): Client {
        $uris = is_array($redirectUris)
            ? $redirectUris
            : array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $redirectUris) ?: [])));

        return $this->clientRepository->createAuthorizationCodeGrantClient(
            name: $name,
            redirectUris: $uris,
            confidential: $confidential,
            user: $user,
        );
    }

    /**
     * Regenerate secret for a confidential OAuth2 client.
     */
    public function regenerateSecret(Client $client): string
    {
        $this->clientRepository->regenerateSecret($client);

        return (string) ($client->plainSecret ?? $client->fresh()?->secret);
    }

    /**
     * Revoke (soft delete) an OAuth2 client.
     */
    public function deleteClient(Client $client): void
    {
        $this->clientRepository->delete($client);
    }

    /**
     * Find an active client by ID.
     */
    public function findActiveClient(string $id): ?Client
    {
        return $this->clientRepository->findActive($id);
    }
}
