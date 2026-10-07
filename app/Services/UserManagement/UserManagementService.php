<?php

declare(strict_types=1);

namespace App\Services\UserManagement;

use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkUnit;
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
     * @param  array{search?: ?string, role?: ?string, unit?: ?string, is_admin?: ?string, is_active?: ?string}  $filters
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::with([
            'profile.workUnit',
            'profile.studyProgram.faculty',
            'roles',
        ])->latest();

        // Search Filter (Email, Full Name, or Identification Number)
        if (! empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('email', 'like', "%{$searchTerm}%")
                    ->orWhereHas('profile', function (Builder $profileQuery) use ($searchTerm): void {
                        $profileQuery->where('nama_lengkap', 'like', "%{$searchTerm}%")
                            ->orWhere('nomor_induk', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Filter by Role
        if (! empty($filters['role'])) {
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $filters['role']));
        }

        // Filter by Work Unit
        if (! empty($filters['unit'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('work_unit_id', $filters['unit']));
        }

        // Filter by Admin Status
        if (isset($filters['is_admin']) && $filters['is_admin'] !== '') {
            $query->where('is_admin', filter_var($filters['is_admin'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by Active Account State
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
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
            $isAdmin = (bool) ($data['is_admin'] ?? false);

            // 1. Resolve Work Unit or Study Program ID
            $workUnitId = null;
            $studyProgramId = null;

            if (in_array($roleName, ['dosen', 'mahasiswa'], true)) {
                $rawProdi = $data['study_program_id'] ?? ($data['program_studi'] ?? null);
                if ($rawProdi) {
                    $studyProgramId = is_numeric($rawProdi)
                        ? (int) $rawProdi
                        : StudyProgram::where('code', $rawProdi)->orWhere('name', $rawProdi)->value('id');
                }
            } else {
                $rawUnit = $data['work_unit_id'] ?? ($data['unit_kerja'] ?? null);
                if ($rawUnit) {
                    $workUnitId = is_numeric($rawUnit)
                        ? (int) $rawUnit
                        : WorkUnit::where('code', $rawUnit)->orWhere('name', $rawUnit)->value('id');
                }
            }

            // 2. Create Core Authentication User (UUID PK)
            /** @var User $user */
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make((string) $data['password']),
                'is_active' => $data['is_active'] ?? true,
                'is_admin' => $isAdmin,
            ]);

            // 3. Attach Role
            $role = Role::where('name', $roleName)->firstOrFail();
            $user->roles()->sync([$role->id]);

            // 4. Resolve or Auto-Generate Nomor Induk
            $nomorInduk = ! empty($data['nomor_induk'])
                ? (string) $data['nomor_induk']
                : $this->academicService->generateNomorInduk(
                    role: $roleName,
                    studyProgram: $studyProgramId,
                    workUnit: $workUnitId,
                );

            // 5. Create Master Demographic Profile (Triggers UserProfileObserver::created)
            UserProfile::create([
                'user_id' => $user->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'nomor_induk' => $nomorInduk,
                'work_unit_id' => $workUnitId,
                'study_program_id' => $studyProgramId,
            ]);

            return $user->load(['profile.workUnit', 'profile.studyProgram.faculty', 'roles']);
        });
    }

    /**
     * Update user's active state and admin status inside a database transaction.
     */
    public function updateUserStatus(User $user, ?bool $isActive = null, ?bool $isAdmin = null): User
    {
        return DB::transaction(function () use ($user, $isActive, $isAdmin): User {
            $updates = [];

            if ($isActive !== null) {
                $updates['is_active'] = $isActive;
            }

            if ($isAdmin !== null) {
                $updates['is_admin'] = $isAdmin;
            }

            if (! empty($updates)) {
                $user->update($updates);
            }

            // Touch profile if exists to trigger UserProfileObserver broadcast
            if ($user->profile) {
                $user->profile->touch();
            }

            return $user->fresh(['profile.workUnit', 'profile.studyProgram.faculty', 'roles']);
        });
    }

    /**
     * Toggle or set admin status for user.
     */
    public function toggleAdminStatus(User $user, bool $isAdmin): User
    {
        return $this->updateUserStatus($user, null, $isAdmin);
    }

    /**
     * Soft delete user account.
     */
    public function deleteUser(User $user): bool
    {
        return (bool) $user->delete();
    }
}
