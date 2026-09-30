<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
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

        $usersData = [
            // Super Admin
            [
                'email' => 'superadmin@univ.ac.id',
                'role' => 'super_admin',
                'profile' => [
                    'nama_lengkap' => 'Super Administrator',
                    'nomor_induk' => 'SA-001',
                    'unit_kerja' => 'Pusat Teknologi Informasi (BSI)',
                    'fakultas' => null,
                    'program_studi' => null,
                    'status_akademik' => 'aktif',
                ],
            ],
            // Admin SDM
            [
                'email' => 'sdm@univ.ac.id',
                'role' => 'admin_sdm',
                'profile' => [
                    'nama_lengkap' => 'Budi Santoso, S.Kom.',
                    'nomor_induk' => 'SDM-19850101',
                    'unit_kerja' => 'Biro Kepegawaian & SDM',
                    'fakultas' => null,
                    'program_studi' => null,
                    'status_akademik' => 'aktif',
                ],
            ],
            // Dosen
            [
                'email' => 'fahmi.dosen@univ.ac.id',
                'role' => 'dosen',
                'profile' => [
                    'nama_lengkap' => 'Dr. Fahmi R., M.Kom.',
                    'nomor_induk' => '0412058801',
                    'unit_kerja' => null,
                    'fakultas' => 'Fakultas Teknologi Informasi',
                    'program_studi' => 'Teknik Informatika',
                    'status_akademik' => 'aktif',
                ],
            ],
            [
                'email' => 'siti.dosen@univ.ac.id',
                'role' => 'dosen',
                'profile' => [
                    'nama_lengkap' => 'Dr. Siti Aminah, M.T.',
                    'nomor_induk' => '0415088902',
                    'unit_kerja' => null,
                    'fakultas' => 'Fakultas Teknologi Informasi',
                    'program_studi' => 'Sistem Informasi',
                    'status_akademik' => 'studi_lanjut',
                ],
            ],
            [
                'email' => 'hendra.dosen@univ.ac.id',
                'role' => 'dosen',
                'profile' => [
                    'nama_lengkap' => 'Hendra Wijaya, S.E., M.M.',
                    'nomor_induk' => '0420118503',
                    'unit_kerja' => null,
                    'fakultas' => 'Fakultas Ekonomi dan Bisnis',
                    'program_studi' => 'Manajemen',
                    'status_akademik' => 'aktif',
                ],
            ],
            // Mahasiswa
            [
                'email' => 'ahmad.mhs@univ.ac.id',
                'role' => 'mahasiswa',
                'profile' => [
                    'nama_lengkap' => 'Ahmad Fauzi',
                    'nomor_induk' => '220101001',
                    'unit_kerja' => null,
                    'fakultas' => 'Fakultas Teknologi Informasi',
                    'program_studi' => 'Teknik Informatika',
                    'status_akademik' => 'aktif',
                ],
            ],
            [
                'email' => 'dewi.mhs@univ.ac.id',
                'role' => 'mahasiswa',
                'profile' => [
                    'nama_lengkap' => 'Dewi Lestari',
                    'nomor_induk' => '220102015',
                    'unit_kerja' => null,
                    'fakultas' => 'Fakultas Teknologi Informasi',
                    'program_studi' => 'Sistem Informasi',
                    'status_akademik' => 'cuti',
                ],
            ],
            [
                'email' => 'rizky.mhs@univ.ac.id',
                'role' => 'mahasiswa',
                'profile' => [
                    'nama_lengkap' => 'Rizky Pratama',
                    'nomor_induk' => '230201045',
                    'unit_kerja' => null,
                    'fakultas' => 'Fakultas Ekonomi dan Bisnis',
                    'program_studi' => 'Manajemen',
                    'status_akademik' => 'aktif',
                ],
            ],
            // Karyawan / Tendik
            [
                'email' => 'rina.staff@univ.ac.id',
                'role' => 'karyawan',
                'profile' => [
                    'nama_lengkap' => 'Rina Wulandari, A.Md.',
                    'nomor_induk' => 'TDK-201801',
                    'unit_kerja' => 'Biro Keuangan',
                    'fakultas' => null,
                    'program_studi' => null,
                    'status_akademik' => 'aktif',
                ],
            ],
            [
                'email' => 'agus.staff@univ.ac.id',
                'role' => 'karyawan',
                'profile' => [
                    'nama_lengkap' => 'Agus Setiawan, S.Sos.',
                    'nomor_induk' => 'TDK-201904',
                    'unit_kerja' => 'Biro Administrasi Akademik',
                    'fakultas' => null,
                    'program_studi' => null,
                    'status_akademik' => 'aktif',
                ],
            ],
        ];

        DB::transaction(function () use ($usersData, $defaultPassword): void {
            foreach ($usersData as $item) {
                $user = User::firstOrCreate(
                    ['email' => $item['email']],
                    [
                        'password' => $defaultPassword,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );

                $role = Role::where('name', $item['role'])->first();
                if ($role) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                }

                UserProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    $item['profile']
                );
            }
        });
    }
}
