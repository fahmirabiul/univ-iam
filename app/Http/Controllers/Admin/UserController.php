<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Academic\AcademicMasterDataService;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
        private readonly AcademicMasterDataService $academicService,
    ) {}

    /**
     * Display the list of users with search and filter capabilities.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search'),
            'role' => $request->query('role'),
            'unit' => $request->query('unit'),
            'is_admin' => $request->query('is_admin'),
            'is_active' => $request->query('is_active'),
        ];

        $users = $this->userManagementService->getPaginatedUsers($filters, 10);
        $roles = Role::whereIn('name', ['dosen', 'karyawan', 'mahasiswa'])->orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $filters,
            'faculties' => $this->academicService->getFaculties(),
            'studyPrograms' => $this->academicService->getStudyProgramsGrouped(),
            'workUnits' => $this->academicService->getWorkUnits(),
        ]);
    }

    /**
     * Store a newly created user and demographic profile in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userManagementService->createUser($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data sivitas akademika berhasil ditambahkan ke sistem.');
    }

    /**
     * Update the active state and admin unit status of the specified user.
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $this->userManagementService->updateUserStatus(
            user: $user,
            isActive: $request->has('is_active') ? $request->boolean('is_active') : null,
            isAdmin: $request->has('is_admin') ? $request->boolean('is_admin') : null,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Status akun dan hak akses pengguna berhasil diperbarui.');
    }

    /**
     * Soft delete the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->userManagementService->deleteUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dinonaktifkan.');
    }
}
