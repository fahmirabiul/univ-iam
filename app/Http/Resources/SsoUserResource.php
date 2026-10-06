<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class SsoUserResource extends JsonResource
{
    /**
     * Disable resource wrapping (e.g. data envelope) to strictly match TDD contract.
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;
        $profile = $user->profile;
        $civitasRole = $user->getCivitasRole();

        return [
            'sso_id' => $user->id,
            'email' => $user->email,
            'role_global' => $civitasRole?->name ?? 'user',
            'roles' => $user->roles->pluck('name')->values()->all(),
            'profil' => [
                'nama_lengkap' => $profile?->nama_lengkap ?? '',
                'nomor_induk' => $profile?->nomor_induk,
                'fakultas' => $profile?->fakultas,
                'program_studi' => $profile?->program_studi,
                'status_akademik' => $profile?->status_akademik,
            ],
        ];
    }
}
