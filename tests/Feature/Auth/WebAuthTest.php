<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Portal Sivitas Akademika')
            ->assertSee('Masuk ke Portal');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $role = Role::create(['name' => 'dosen', 'description' => 'Dosen']);

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
            'status_akademik' => 'aktif',
        ]);

        $response = $this->post('/login', [
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('portal'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        User::create([
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_can_not_authenticate(): void
    {
        User::create([
            'email' => 'banned@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'banned@univ.ac.id',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_access_portal_and_see_role_applications(): void
    {
        $role = Role::create(['name' => 'dosen', 'description' => 'Dosen']);

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
            'fakultas' => 'Fakultas Teknologi Informasi',
            'program_studi' => 'Teknik Informatika',
            'status_akademik' => 'aktif',
        ]);

        $response = $this->actingAs($user)->get('/portal');

        $response->assertOk()
            ->assertSee('Selamat Datang, Dr. Fahmi R., M.Kom.!')
            ->assertSee('Knowledge Hub')
            ->assertSee('Sistem Informasi Akademik (SIAKAD)');
    }

    public function test_users_can_logout(): void
    {
        /** @var User $user */
        $user = User::create([
            'email' => 'fahmi.dosen@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
