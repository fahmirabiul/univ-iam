<?php

declare(strict_types=1);

namespace App\Services\UserManagement;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
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
            // 1. Create Core Authentication User (UUID PK)
            /** @var User $user */
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make((string) $data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2. Attach Global Role
            $role = Role::where('name', $data['role'])->firstOrFail();
            $user->roles()->attach($role->id);

            // 3. Create Master Demographic Profile (Triggers UserProfileObserver::created)
            UserProfile::create([
                'user_id' => $user->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'nomor_induk' => $data['nomor_induk'] ?? null,
                'unit_kerja' => $data['unit_kerja'] ?? null,
                'fakultas' => $data['fakultas'] ?? null,
                'program_studi' => $data['program_studi'] ?? null,
                'status_akademik' => $data['status_akademik'] ?? 'aktif',
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
}
