<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_admin_routes(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect(route('login'));
    }

    public function test_dosen_cannot_access_admin_routes_and_receives_forbidden_403(): void
    {
        $dosenRole = Role::create(['name' => 'dosen', 'description' => 'Dosen']);

        /** @var User $dosen */
        $dosen = User::create([
            'email' => 'dosen@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $dosen->roles()->attach($dosenRole->id);

        $response = $this->actingAs($dosen)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_mahasiswa_cannot_access_admin_routes_and_receives_forbidden_403(): void
    {
        $mhsRole = Role::create(['name' => 'mahasiswa', 'description' => 'Mahasiswa']);

        /** @var User $mahasiswa */
        $mahasiswa = User::create([
            'email' => 'mahasiswa@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $mahasiswa->roles()->attach($mhsRole->id);

        $response = $this->actingAs($mahasiswa)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_super_admin_can_access_admin_routes(): void
    {
        $superAdminRole = Role::create(['name' => 'super_admin', 'description' => 'Super Admin']);

        /** @var User $superAdmin */
        $superAdmin = User::create([
            'email' => 'super.admin@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $superAdmin->roles()->attach($superAdminRole->id);

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
    }

    public function test_karyawan_without_admin_flag_cannot_access_admin_routes_and_receives_forbidden_403(): void
    {
        $karyawanRole = Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);

        /** @var User $karyawan */
        $karyawan = User::create([
            'email' => 'staff.biasa@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $karyawan->roles()->attach($karyawanRole->id);

        $response = $this->actingAs($karyawan)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_karyawan_with_is_admin_flag_can_access_admin_routes(): void
    {
        $karyawanRole = Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);

        /** @var User $karyawanAdmin */
        $karyawanAdmin = User::create([
            'email' => 'karyawan.sdm@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => true,
        ]);
        $karyawanAdmin->roles()->attach($karyawanRole->id);

        $response = $this->actingAs($karyawanAdmin)->get('/admin/users');

        $response->assertOk();
    }
}
