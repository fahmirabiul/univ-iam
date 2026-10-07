<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\DTOs\UserProfileUpdatedDto;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class UserProfileRedisBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_user_profile_broadcasts_payload_to_redis(): void
    {
        Redis::spy();

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

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
            'nomor_induk' => '0412058801',
        ]);

        $profile->update([
            'nama_lengkap' => 'Prof. Dr. Fahmi R., M.Kom.',
        ]);

        $expectedChannel = (string) config('services.redis_channels.user_profile_updated', 'university.user.profile_updated');

        Redis::shouldHaveReceived('publish')
            ->with(
                $expectedChannel,
                \Mockery::on(function (string $jsonPayload) use ($user): bool {
                    $decoded = json_decode($jsonPayload, true, 512, JSON_THROW_ON_ERROR);

                    return $decoded['event'] === 'UserProfileUpdated'
                        && $decoded['data']['sso_id'] === $user->id
                        && $decoded['data']['nama_lengkap'] === 'Prof. Dr. Fahmi R., M.Kom.'
                        && $decoded['data']['role_global'] === 'dosen'
                        && $decoded['data']['is_admin'] === false;
                })
            );
    }

    public function test_user_profile_updated_dto_formats_data_correctly(): void
    {
        $role = Role::create([
            'name' => 'karyawan',
            'description' => 'Staf Karyawan',
        ]);

        $unit = WorkUnit::create([
            'code' => '503',
            'name' => 'LPPM',
        ]);

        /** @var User $user */
        $user = User::create([
            'email' => 'staff.lppm@univ.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        $user->roles()->attach($role->id);

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Ahmad Fauzi',
            'nomor_induk' => '20265030001',
            'work_unit_id' => $unit->id,
        ]);

        $dto = UserProfileUpdatedDto::fromModel($profile);
        $array = $dto->toArray();

        $this->assertSame('UserProfileUpdated', $array['event']);
        $this->assertArrayHasKey('timestamp', $array);
        $this->assertSame($user->id, $array['data']['sso_id']);
        $this->assertSame('Ahmad Fauzi', $array['data']['nama_lengkap']);
        $this->assertSame('karyawan', $array['data']['role_global']);
        $this->assertTrue($array['data']['is_admin']);
        $this->assertSame('503', $array['data']['unit']['kode']);
        $this->assertSame('LPPM', $array['data']['unit']['nama']);
    }
}
