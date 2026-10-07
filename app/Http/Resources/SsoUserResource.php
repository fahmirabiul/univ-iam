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
     * Disable resource wrapping (e.g. data envelope) to strictly match SSO contract.
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
        $workUnit = $profile?->workUnit;
        $studyProgram = $profile?->studyProgram;
        $faculty = $studyProgram?->faculty;
        $civitasRole = $user->getCivitasRole();

        return [
            'sso_id' => $user->id,
            'email' => $user->email,
            'role_global' => $civitasRole?->name ?? 'user',
            'is_superadmin' => $user->isSuperAdmin(),
            'is_admin' => (bool) $user->is_admin,
            'unit' => $workUnit ? [
                'id' => $workUnit->id,
                'kode' => $workUnit->code,
                'nama' => $workUnit->name,
            ] : null,
            'fakultas' => $faculty?->name,
            'program_studi' => $studyProgram?->name,
            'profil' => [
                'nama_lengkap' => $profile?->nama_lengkap ?? '',
                'nomor_induk' => $profile?->nomor_induk,
            ],
        ];
    }
}
