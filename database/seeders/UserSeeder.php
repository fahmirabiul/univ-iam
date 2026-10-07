<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // Resolve Units
        $unitSdm = WorkUnit::where('code', '502')->first();
        $unitLppm = WorkUnit::where('code', '503')->first();
        $unitTik = WorkUnit::where('code', '501')->first();

        // Resolve Study Programs
        $prodiIf = StudyProgram::where('code', '101')->first();
        $prodiEl = StudyProgram::where('code', '102')->first();
        $prodiMn = StudyProgram::where('code', '201')->first();
        $prodiAk = StudyProgram::where('code', '202')->first();
        $prodiDi = StudyProgram::where('code', '301')->first();
        $prodiDkv = StudyProgram::where('code', '302')->first();

        $usersData = [
            // 1. Super Administrator
            [
                'email' => 'superadmin@univ.ac.id',
                'role' => 'super_admin',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Super Administrator',
                    'nomor_induk' => 'SA-001',
                    'work_unit_id' => $unitTik?->id,
                    'study_program_id' => null,
                ],
            ],
            // 2. Admin Unit SDM
            [
                'email' => 'sdm@univ.ac.id',
                'role' => 'karyawan',
                'is_admin' => true,
                'profile' => [
                    'nama_lengkap' => 'Budi Santoso, S.Kom.',
                    'nomor_induk' => '20265020001',
                    'work_unit_id' => $unitSdm?->id,
                    'study_program_id' => null,
                ],
            ],
            // 3. Admin Unit LPPM
            [
                'email' => 'lppm@univ.ac.id',
                'role' => 'karyawan',
                'is_admin' => true,
                'profile' => [
                    'nama_lengkap' => 'Suryo Utomo, S.T.',
                    'nomor_induk' => '20265030001',
                    'work_unit_id' => $unitLppm?->id,
                    'study_program_id' => null,
                ],
            ],
            // 4. Admin Unit TIK
            [
                'email' => 'tik@univ.ac.id',
                'role' => 'karyawan',
                'is_admin' => true,
                'profile' => [
                    'nama_lengkap' => 'Anwar Sanusi, M.Kom.',
                    'nomor_induk' => '20265010001',
                    'work_unit_id' => $unitTik?->id,
                    'study_program_id' => null,
                ],
            ],
            // 5. Staf Biasa (Bukan Admin)
            [
                'email' => 'rina.staff@univ.ac.id',
                'role' => 'karyawan',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Rina Wulandari, A.Md.',
                    'nomor_induk' => '20265020002',
                    'work_unit_id' => $unitSdm?->id,
                    'study_program_id' => null,
                ],
            ],
            [
                'email' => 'agus.staff@univ.ac.id',
                'role' => 'karyawan',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Agus Setiawan, S.Sos.',
                    'nomor_induk' => '20265030002',
                    'work_unit_id' => $unitLppm?->id,
                    'study_program_id' => null,
                ],
            ],
            // 6. Dosen Pengajar
            [
                'email' => 'fahmi.dosen@univ.ac.id',
                'role' => 'dosen',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
                    'nomor_induk' => '202610110001',
                    'work_unit_id' => null,
                    'study_program_id' => $prodiIf?->id,
                ],
            ],
            [
                'email' => 'hendra.dosen@univ.ac.id',
                'role' => 'dosen',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Hendra Wijaya, S.E., M.M.',
                    'nomor_induk' => '202620110001',
                    'work_unit_id' => null,
                    'study_program_id' => $prodiMn?->id,
                ],
            ],
            // 7. Mahasiswa
            [
                'email' => 'ahmad.mhs@univ.ac.id',
                'role' => 'mahasiswa',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Ahmad Fauzi',
                    'nomor_induk' => '2611010001',
                    'work_unit_id' => null,
                    'study_program_id' => $prodiIf?->id,
                ],
            ],
            [
                'email' => 'rizky.mhs@univ.ac.id',
                'role' => 'mahasiswa',
                'is_admin' => false,
                'profile' => [
                    'nama_lengkap' => 'Rizky Pratama',
                    'nomor_induk' => '2612010001',
                    'work_unit_id' => null,
                    'study_program_id' => $prodiMn?->id,
                ],
            ],
        ];

        DB::transaction(function () use ($usersData, $defaultPassword): void {
            foreach ($usersData as $item) {
                $user = User::updateOrCreate(
                    ['email' => $item['email']],
                    [
                        'password' => $defaultPassword,
                        'is_active' => true,
                        'is_admin' => $item['is_admin'],
                        'email_verified_at' => now(),
                    ]
                );

                $role = Role::where('name', $item['role'])->first();
                if ($role) {
                    $user->roles()->sync([$role->id]);
                }

                UserProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    $item['profile']
                );
            }
        });
    }
}
