<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\UserProfile;
use JsonSerializable;

final class UserProfileUpdatedDto implements JsonSerializable
{
    public function __construct(
        public readonly string $ssoId,
        public readonly string $namaLengkap,
        public readonly ?string $statusAkademik,
        public readonly string $roleGlobal,
        public readonly string $event = 'UserProfileUpdated',
        public readonly ?string $timestamp = null,
    ) {}

    /**
     * Create a strongly-typed DTO instance from an Eloquent UserProfile model.
     */
    public static function fromModel(UserProfile $profile): self
    {
        $user = $profile->relationLoaded('user') ? $profile->user : $profile->user()->with('roles')->first();
        $primaryRole = $user?->roles?->first()?->name ?? 'user';

        return new self(
            ssoId: (string) $profile->user_id,
            namaLengkap: $profile->nama_lengkap,
            statusAkademik: $profile->status_akademik,
            roleGlobal: $primaryRole,
            event: 'UserProfileUpdated',
            timestamp: now()->toIso8601ZuluString(),
        );
    }

    /**
     * Transform the DTO into the standardized JSON payload array matching the TDD contract.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event' => $this->event,
            'timestamp' => $this->timestamp ?? now()->toIso8601ZuluString(),
            'data' => [
                'sso_id' => $this->ssoId,
                'nama_lengkap' => $this->namaLengkap,
                'status_akademik' => $this->statusAkademik,
                'role_global' => $this->roleGlobal,
            ],
        ];
    }

    /**
     * Convert the DTO to a JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
