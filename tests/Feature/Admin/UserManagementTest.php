<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Faculty;
use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkUnit;
use Database\Seeders\AcademicMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminSdm;

    private Role $superAdminRole;

    private Role $dosenRole;

    private Role $mahasiswaRole;

    private Role $karyawanRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AcademicMasterDataSeeder::class);

        $this->superAdminRole = Role::create(['name' => 'super_admin', 'description' => 'Super Admin']);
        $this->dosenRole = Role::create(['name' => 'dosen', 'description' => 'Dosen']);
        $this->mahasiswaRole = Role::create(['name' => 'mahasiswa', 'description' => 'Mahasiswa']);
        $this->karyawanRole = Role::create(['name' => 'karyawan', 'description' => 'Karyawan']);

        $sdmUnit = WorkUnit::where('code', '502')->first();

        $this->adminSdm = User::create([
            'email' => 'admin.sdm@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);
        $this->adminSdm->roles()->attach($this->karyawanRole->id);

        UserProfile::create([
            'user_id' => $this->adminSdm->id,
            'nama_lengkap' => 'Biro Kepegawaian & SDM',
            'work_unit_id' => $sdmUnit?->id,
        ]);
    }

    public function test_admin_can_view_users_index_page_with_data_and_filters(): void
    {
        $prodi = StudyProgram::where('code', '101')->firstOrFail();

        /** @var User $dosen */
        $dosen = User::create([
            'email' => 'budi.dosen@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $dosen->roles()->attach($this->dosenRole->id);

        UserProfile::create([
            'user_id' => $dosen->id,
            'nama_lengkap' => 'Dr. Budi Santoso, M.T.',
            'nomor_induk' => '198501012010121001',
            'study_program_id' => $prodi->id,
        ]);

        $response = $this->actingAs($this->adminSdm)->get('/admin/users');

        $response->assertOk()
            ->assertSee('Manajemen Master Data Sivitas Akademika')
            ->assertSee('Dr. Budi Santoso, M.T.')
            ->assertSee('budi.dosen@univ.ac.id')
            ->assertSee('198501012010121001')
            ->assertSee('Teknik Informatika');
    }

    public function test_admin_can_create_new_user_and_trigger_redis_broadcast(): void
    {
        Redis::spy();

        $prodi = StudyProgram::where('code', '101')->firstOrFail();

        $response = $this->actingAs($this->adminSdm)->post('/admin/users', [
            'email' => 'citra.dosen@univ.ac.id',
            'password' => 'password12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Prof. Dr. Citra Lestari',
            'nomor_induk' => '197901012005012001',
            'study_program_id' => $prodi->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'citra.dosen@univ.ac.id',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'citra.dosen@univ.ac.id')->firstOrFail();

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $newUser->id,
            'nama_lengkap' => 'Prof. Dr. Citra Lestari',
            'nomor_induk' => '197901012005012001',
            'study_program_id' => $prodi->id,
        ]);

        $this->assertTrue($newUser->hasRole('dosen'));

        // Verify that UserProfileObserver broadcasted creation to Redis
        $expectedChannel = (string) config('services.redis_channels.user_profile_updated', 'university.user.profile_updated');
        Redis::shouldHaveReceived('publish')
            ->with(
                $expectedChannel,
                Mockery::on(function (string $jsonPayload) use ($newUser): bool {
                    $decoded = json_decode($jsonPayload, true, 512, JSON_THROW_ON_ERROR);

                    return $decoded['event'] === 'UserProfileUpdated'
                        && $decoded['data']['sso_id'] === $newUser->id
                        && $decoded['data']['nama_lengkap'] === 'Prof. Dr. Citra Lestari'
                        && $decoded['data']['role_global'] === 'dosen';
                })
            );
    }

    public function test_admin_can_update_user_status_and_admin_right(): void
    {
        Redis::spy();

        $unitLppm = WorkUnit::where('code', '503')->firstOrFail();

        /** @var User $karyawan */
        $karyawan = User::create([
            'email' => 'staf.lppm@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $karyawan->roles()->attach($this->karyawanRole->id);

        UserProfile::create([
            'user_id' => $karyawan->id,
            'nama_lengkap' => 'Staf LPPM',
            'work_unit_id' => $unitLppm->id,
        ]);

        $response = $this->actingAs($this->adminSdm)->put("/admin/users/{$karyawan->id}/status", [
            'is_admin' => '1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $karyawan->refresh();
        $this->assertTrue($karyawan->is_admin);
        $this->assertTrue($karyawan->is_active);
    }

    public function test_admin_can_soft_delete_user(): void
    {
        /** @var User $userToDelete */
        $userToDelete = User::create([
            'email' => 'delete.me@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $userToDelete->roles()->attach($this->mahasiswaRole->id);

        $response = $this->actingAs($this->adminSdm)->delete("/admin/users/{$userToDelete->id}");

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id,
        ]);
    }

    public function test_validation_errors_when_creating_user_with_invalid_data(): void
    {
        $response = $this->actingAs($this->adminSdm)->post('/admin/users', [
            'email' => 'not-an-email',
            'password' => 'short',
            'role' => 'invalid_role_name',
            'nama_lengkap' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password', 'role', 'nama_lengkap']);
    }

    public function test_non_admin_cannot_perform_admin_crud_actions(): void
    {
        /** @var User $dosen */
        $dosen = User::create([
            'email' => 'dosen.regular@univ.ac.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'is_admin' => false,
        ]);
        $dosen->roles()->attach($this->dosenRole->id);

        // Attempt Create
        $createResponse = $this->actingAs($dosen)->post('/admin/users', [
            'email' => 'hacked@univ.ac.id',
            'password' => 'password12345',
            'role' => 'dosen',
            'nama_lengkap' => 'Hacker User',
        ]);
        $createResponse->assertForbidden();

        // Attempt Status Update
        $updateResponse = $this->actingAs($dosen)->put("/admin/users/{$this->adminSdm->id}/status", [
            'is_active' => '0',
        ]);
        $updateResponse->assertForbidden();

        // Attempt Delete
        $deleteResponse = $this->actingAs($dosen)->delete("/admin/users/{$this->adminSdm->id}");
        $deleteResponse->assertForbidden();
    }

    public function test_admin_can_create_karyawan_with_work_unit_and_is_admin_flag(): void
    {
        $unitLppm = WorkUnit::where('code', '503')->firstOrFail();

        $response = $this->actingAs($this->adminSdm)->post('/admin/users', [
            'email' => 'staff.lppm@univ.ac.id',
            'password' => 'password12345',
            'role' => 'karyawan',
            'nama_lengkap' => 'Staff LPPM Riset',
            'work_unit_id' => $unitLppm->id,
            'is_admin' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        /** @var User $newUser */
        $newUser = User::where('email', 'staff.lppm@univ.ac.id')->firstOrFail();

        $this->assertTrue($newUser->hasRole('karyawan'));
        $this->assertTrue($newUser->is_admin);
        $this->assertSame($unitLppm->id, $newUser->profile?->work_unit_id);
    }
}
