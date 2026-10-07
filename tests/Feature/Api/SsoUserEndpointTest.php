<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Faculty;
use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SsoUserEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_is_rejected_with_401_json(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_authenticated_dosen_can_retrieve_sso_profile_with_academic_info(): void
    {
        $role = Role::create([
            'name' => 'dosen',
            'description' => 'Dosen Pengajar',
        ]);

        /** @var User $user */
        $user = User::create([
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $user->roles()->attach($role->id);

        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Fakultas Teknik']);
        $studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'code' => '101',
            'nim_code' => '1101',
            'name' => 'Teknik Informatika',
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
            'nomor_induk' => '202610110001',
            'study_program_id' => $studyProgram->id,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'sso_id',
                'email',
                'role_global',
                'is_superadmin',
                'is_admin',
                'unit',
                'fakultas',
                'program_studi',
                'profil' => [
                    'nama_lengkap',
                    'nomor_induk',
                ],
            ])
            ->assertJson([
                'sso_id' => $user->id,
                'email' => 'fahmi.dosen@univ.ac.id',
                'role_global' => 'dosen',
                'is_superadmin' => false,
                'is_admin' => false,
                'unit' => null,
                'fakultas' => 'Fakultas Teknik',
                'program_studi' => 'Teknik Informatika',
                'profil' => [
                    'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
                    'nomor_induk' => '202610110001',
                ],
            ]);
    }

    public function test_authenticated_karyawan_admin_unit_can_retrieve_sso_profile_with_unit_info_and_is_admin_flag(): void
    {
        $role = Role::create([
            'name' => 'karyawan',
            'description' => 'Tenaga Kependidikan',
        ]);

        $unit = WorkUnit::create([
            'code' => '503',
            'name' => 'LPPM',
        ]);

        /** @var User $user */
        $user = User::create([
            'email' => 'admin.lppm@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => true,
        ]);
        $user->roles()->attach($role->id);

        UserProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Suryo Utomo, S.T.',
            'nomor_induk' => '20265030001',
            'work_unit_id' => $unit->id,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'sso_id' => $user->id,
                'email' => 'admin.lppm@univ.ac.id',
                'role_global' => 'karyawan',
                'is_superadmin' => false,
                'is_admin' => true,
                'unit' => [
                    'id' => $unit->id,
                    'kode' => '503',
                    'nama' => 'LPPM',
                ],
                'fakultas' => null,
                'program_studi' => null,
                'profil' => [
                    'nama_lengkap' => 'Suryo Utomo, S.T.',
                    'nomor_induk' => '20265030001',
                ],
            ]);
    }

    public function test_authenticated_superadmin_has_is_superadmin_flag_true(): void
    {
        $role = Role::create([
            'name' => 'super_admin',
            'description' => 'Super Administrator',
        ]);

        /** @var User $user */
        $user = User::create([
            'email' => 'superadmin@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $user->roles()->attach($role->id);

        UserProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Root Superadmin',
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'sso_id' => $user->id,
                'email' => 'superadmin@univ.ac.id',
                'role_global' => 'super_admin',
                'is_superadmin' => true,
                'is_admin' => false,
                'unit' => null,
            ]);
    }
}
