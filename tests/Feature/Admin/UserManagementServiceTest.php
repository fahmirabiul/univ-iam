<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
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

        Role::create(['name' => 'dosen', 'description' => 'Dosen']);

        $user = $this->service->createUser([
            'email' => 'budi.dosen@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Dr. Budi Santoso, M.T.',
            'nomor_induk' => '198501012010121001',
            'fakultas' => 'Fakultas Teknik',
            'program_studi' => 'Teknik Elektro',
            'status_akademik' => 'aktif',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'budi.dosen@univ.ac.id', 'is_active' => true]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'nama_lengkap' => 'Dr. Budi Santoso, M.T.',
            'nomor_induk' => '198501012010121001',
            'status_akademik' => 'aktif',
        ]);
        $this->assertTrue($user->hasRole('dosen'));
    }

    public function test_service_can_update_academic_status_and_active_state(): void
    {
        Redis::spy();

        Role::create(['name' => 'dosen', 'description' => 'Dosen']);

        $user = $this->service->createUser([
            'email' => 'budi.dosen@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Dr. Budi Santoso, M.T.',
            'status_akademik' => 'aktif',
            'is_active' => true,
        ]);

        $updatedUser = $this->service->updateUserStatus($user, 'studi_lanjut', false);

        $this->assertEquals('studi_lanjut', $updatedUser->profile->status_akademik);
        $this->assertFalse($updatedUser->is_active);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'status_akademik' => 'studi_lanjut',
        ]);
    }

    public function test_service_can_filter_and_paginate_users(): void
    {
        $dosenRole = Role::create(['name' => 'dosen', 'description' => 'Dosen']);
        $mhsRole = Role::create(['name' => 'mahasiswa', 'description' => 'Mahasiswa']);

        $this->service->createUser([
            'email' => 'andi.dosen@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Andi Wijaya',
            'status_akademik' => 'aktif',
        ]);

        $this->service->createUser([
            'email' => 'citra.mhs@univ.ac.id',
            'password' => 'secret12345',
            'role' => 'mahasiswa',
            'nama_lengkap' => 'Citra Lestari',
            'status_akademik' => 'cuti',
        ]);

        // Filter by role
        $dosenPaginator = $this->service->getPaginatedUsers(['role' => 'dosen']);
        $this->assertEquals(1, $dosenPaginator->total());
        $this->assertEquals('andi.dosen@univ.ac.id', $dosenPaginator->items()[0]->email);

        // Filter by status
        $cutiPaginator = $this->service->getPaginatedUsers(['status' => 'cuti']);
        $this->assertEquals(1, $cutiPaginator->total());
        $this->assertEquals('citra.mhs@univ.ac.id', $cutiPaginator->items()[0]->email);

        // Search by keyword
        $searchPaginator = $this->service->getPaginatedUsers(['search' => 'Citra']);
        $this->assertEquals(1, $searchPaginator->total());
    }
}
