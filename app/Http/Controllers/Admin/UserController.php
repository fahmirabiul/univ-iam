<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
    ) {}

    /**
     * Display the list of users with search and filter capabilities.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search'),
            'role' => $request->query('role'),
            'status' => $request->query('status'),
        ];

        $users = $this->userManagementService->getPaginatedUsers($filters, 10);
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $filters,
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
     * Update the academic status and active state of the specified user.
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $this->userManagementService->updateUserStatus(
            user: $user,
            statusAkademik: $request->validated('status_akademik'),
            isActive: $request->has('is_active') ? $request->boolean('is_active') : null,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Status sivitas berhasil diperbarui dan disinkronkan ke jaringan kampus.');
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
