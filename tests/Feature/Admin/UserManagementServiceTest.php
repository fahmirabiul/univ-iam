<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Faculty;
use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserManagementService::class);
    }

    public function test_service_can_create_user_with_role_and_profile(): void
    {
        Redis::spy();

        Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);
        $unit = WorkUnit::create(['code' => '503', 'name' => 'LPPM']);

        $user = $this->service->createUser([
            'email' => 'staff.lppm@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'karyawan',
            'nama_lengkap' => 'Staff LPPM Kampus',
            'work_unit_id' => $unit->id,
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('karyawan'));
        $this->assertDatabaseHas('users', [
            'email' => 'staff.lppm@univ.ac.id',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'nama_lengkap' => 'Staff LPPM Kampus',
            'work_unit_id' => $unit->id,
        ]);
    }

    public function test_service_can_create_dosen_with_study_program(): void
    {
        Redis::spy();

        Role::create(['name' => 'dosen', 'description' => 'Dosen']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Fakultas Teknik']);
        $prodi = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'code' => '101',
            'nim_code' => '1101',
            'name' => 'Teknik Informatika',
        ]);

        $user = $this->service->createUser([
            'email' => 'dosen.it@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Dr. Budi Santoso, M.T.',
            'study_program_id' => $prodi->id,
            'is_admin' => false,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertFalse($user->is_admin);
        $this->assertTrue($user->hasRole('dosen'));
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'study_program_id' => $prodi->id,
        ]);
    }

    public function test_service_can_update_active_state_and_admin_status(): void
    {
        Redis::spy();

        Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);
        $unit = WorkUnit::create(['code' => '502', 'name' => 'Biro SDM']);

        $user = $this->service->createUser([
            'email' => 'sdm@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'karyawan',
            'nama_lengkap' => 'Staf SDM',
            'work_unit_id' => $unit->id,
            'is_admin' => false,
            'is_active' => true,
        ]);

        $updatedUser = $this->service->updateUserStatus($user, isActive: false, isAdmin: true);

        $this->assertFalse($updatedUser->is_active);
        $this->assertTrue($updatedUser->is_admin);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
            'is_admin' => true,
        ]);
    }

    public function test_service_can_toggle_admin_status(): void
    {
        Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);
        $unit = WorkUnit::create(['code' => '503', 'name' => 'LPPM']);

        $user = $this->service->createUser([
            'email' => 'admin.lppm@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'karyawan',
            'nama_lengkap' => 'Staf LPPM',
            'work_unit_id' => $unit->id,
            'is_admin' => false,
        ]);

        $this->assertFalse($user->is_admin);

        $toggled = $this->service->toggleAdminStatus($user, true);
        $this->assertTrue($toggled->is_admin);

        $untoggled = $this->service->toggleAdminStatus($user, false);
        $this->assertFalse($untoggled->is_admin);
    }

    public function test_service_can_filter_and_paginate_users(): void
    {
        $dosenRole = Role::create(['name' => 'dosen', 'description' => 'Dosen']);
        $karyawanRole = Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);
        $unit1 = WorkUnit::create(['code' => '501', 'name' => 'UPT TIK']);
        $unit2 = WorkUnit::create(['code' => '503', 'name' => 'LPPM']);

        $this->service->createUser([
            'email' => 'andi.dosen@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Andi Wijaya',
        ]);

        $this->service->createUser([
            'email' => 'citra.staff@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'karyawan',
            'nama_lengkap' => 'Citra Lestari',
            'work_unit_id' => $unit2->id,
            'is_admin' => true,
        ]);

        // Filter by role
        $dosenPaginator = $this->service->getPaginatedUsers(['role' => 'dosen']);
        $this->assertEquals(1, $dosenPaginator->total());
        $this->assertEquals('andi.dosen@univ.ac.id', $dosenPaginator->items()[0]->email);

        // Filter by unit
        $unitPaginator = $this->service->getPaginatedUsers(['unit' => (string) $unit2->id]);
        $this->assertEquals(1, $unitPaginator->total());
        $this->assertEquals('citra.staff@univ.ac.id', $unitPaginator->items()[0]->email);

        // Filter by admin status
        $adminPaginator = $this->service->getPaginatedUsers(['is_admin' => '1']);
        $this->assertEquals(1, $adminPaginator->total());
        $this->assertEquals('citra.staff@univ.ac.id', $adminPaginator->items()[0]->email);

        // Search by keyword
        $searchPaginator = $this->service->getPaginatedUsers(['search' => 'Citra']);
        $this->assertEquals(1, $searchPaginator->total());
    }

    public function test_service_can_delete_user(): void
    {
        Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);

        $user = $this->service->createUser([
            'email' => 'to.delete@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'karyawan',
            'nama_lengkap' => 'Delete Me',
        ]);

        $result = $this->service->deleteUser($user);
        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
