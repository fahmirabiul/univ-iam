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
        ]);
        $mahasiswa->roles()->attach($mhsRole->id);

        $response = $this->actingAs($mahasiswa)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_sdm_can_access_admin_routes(): void
    {
        $adminSdmRole = Role::create(['name' => 'admin_sdm', 'description' => 'Admin SDM']);

        /** @var User $adminSdm */
        $adminSdm = User::create([
            'email' => 'admin.sdm@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $adminSdm->roles()->attach($adminSdmRole->id);

        $response = $this->actingAs($adminSdm)->get('/admin/users');

        $response->assertOk();
    }

    public function test_super_admin_can_access_admin_routes(): void
    {
        $superAdminRole = Role::create(['name' => 'super_admin', 'description' => 'Super Admin']);

        /** @var User $superAdmin */
        $superAdmin = User::create([
            'email' => 'super.admin@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $superAdmin->roles()->attach($superAdminRole->id);

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
    }
}
