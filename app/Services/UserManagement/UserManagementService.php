<?php

declare(strict_types=1);

namespace App\Services\UserManagement;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Academic\AcademicMasterDataService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(
        private readonly AcademicMasterDataService $academicService,
    ) {}

    /**
     * Retrieve paginated users with relationships and optional search/filters.
     *
     * @param  array{search?: ?string, role?: ?string, status?: ?string}  $filters
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::with(['profile', 'roles'])->latest();

        // Search Filter (Email, Full Name, or Identification Number)
        if (! empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('email', 'like', "%{$searchTerm}%")
                    ->orWhereHas('profile', function (Builder $profileQuery) use ($searchTerm): void {
                        $profileQuery->where('nama_lengkap', 'like', "%{$searchTerm}%")
                            ->orWhere('nomor_induk', 'like', "%{$searchTerm}%")
                            ->orWhere('fakultas', 'like', "%{$searchTerm}%")
                            ->orWhere('program_studi', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Filter by Role
        if (! empty($filters['role'])) {
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $filters['role']));
        }

        // Filter by Academic Status
        if (! empty($filters['status'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('status_akademik', $filters['status']));
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new user with demographic profile and role inside a database transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $roleName = (string) $data['role'];

            // 1. Create Core Authentication User (UUID PK)
            /** @var User $user */
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make((string) $data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2. Attach Primary Civitas Role & Optional Admin Roles
            $role = Role::where('name', $roleName)->firstOrFail();
            $user->roles()->attach($role->id);

            if (! empty($data['admin_roles'])) {
                $adminRoles = Role::whereIn('name', (array) $data['admin_roles'])->get();
                $user->roles()->syncWithoutDetaching($adminRoles->pluck('id'));
            }

            // 3. Resolve Role-Specific Demographic Data
            $fakultas = null;
            $programStudi = null;
            $unitKerja = null;
            $statusAkademik = $data['status_akademik'] ?? 'aktif';

            if (in_array($roleName, ['dosen', 'mahasiswa'], true)) {
                $programStudi = $data['program_studi'] ?? null;
                $fakultas = $data['fakultas']
                    ?? ($programStudi ? $this->academicService->getFacultyByProgram($programStudi) : null);
            } else {
                $unitKerja = $data['unit_kerja'] ?? null;
            }

            // 4. Resolve or Auto-Generate Nomor Induk
            $nomorInduk = ! empty($data['nomor_induk'])
                ? (string) $data['nomor_induk']
                : $this->academicService->generateNomorInduk(
                    role: $roleName,
                    programStudi: $programStudi,
                    unitKerja: $unitKerja,
                );

            // 5. Create Master Demographic Profile (Triggers UserProfileObserver::created)
            UserProfile::create([
                'user_id' => $user->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'nomor_induk' => $nomorInduk,
                'unit_kerja' => $unitKerja,
                'fakultas' => $fakultas,
                'program_studi' => $programStudi,
                'status_akademik' => $statusAkademik,
            ]);

            return $user->load(['profile', 'roles']);
        });
    }

    /**
     * Update user's academic status and account active state inside a database transaction.
     */
    public function updateUserStatus(User $user, string $statusAkademik, ?bool $isActive = null): User
    {
        return DB::transaction(function () use ($user, $statusAkademik, $isActive): User {
            // Update or create demographic profile status (Triggers UserProfileObserver::updated)
            if ($user->profile) {
                $user->profile->update([
                    'status_akademik' => $statusAkademik,
                ]);
            } else {
                UserProfile::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $user->email,
                    'status_akademik' => $statusAkademik,
                ]);
            }

            // Update account active status if provided
            if ($isActive !== null) {
                $user->update([
                    'is_active' => $isActive,
                ]);
            }

            return $user->fresh(['profile', 'roles']);
        });
    }

    /**
     * Soft delete user account.
     */
    public function deleteUser(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * Update administrative roles assigned to a user while preserving civitas identity role.
     *
     * @param  array<int, string>  $adminRoleNames
     */
    public function updateUserAdminRoles(User $user, array $adminRoleNames): User
    {
        return DB::transaction(function () use ($user, $adminRoleNames): User {
            $civitasRole = $user->getCivitasRole();
            $civitasRoleId = $civitasRole?->id;

            $targetAdminRoleIds = Role::whereIn('name', $adminRoleNames)->pluck('id');

            $allRoleIds = $civitasRoleId
                ? $targetAdminRoleIds->push($civitasRoleId)->unique()->values()
                : $targetAdminRoleIds->unique()->values();

            $user->roles()->sync($allRoleIds);

            return $user->fresh(['profile', 'roles']);
        });
    }
}
