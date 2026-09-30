<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
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

    public function test_authenticated_user_can_retrieve_sso_profile_matching_tdd_contract(): void
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
        ]);

        $user->roles()->attach($role->id);

        UserProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
            'nomor_induk' => '0412058801',
            'fakultas' => 'Fakultas Teknologi Informasi',
            'program_studi' => 'Teknik Informatika',
            'status_akademik' => 'aktif',
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'sso_id',
                'email',
                'role_global',
                'profil' => [
                    'nama_lengkap',
                    'nomor_induk',
                    'fakultas',
                    'program_studi',
                    'status_akademik',
                ],
            ])
            ->assertJson([
                'sso_id' => $user->id,
                'email' => 'fahmi.dosen@univ.ac.id',
                'role_global' => 'dosen',
                'profil' => [
                    'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
                    'nomor_induk' => '0412058801',
                    'fakultas' => 'Fakultas Teknologi Informasi',
                    'program_studi' => 'Teknik Informatika',
                    'status_akademik' => 'aktif',
                ],
            ]);
    }
}
