<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'type' => Role::TYPE_ADMIN,
                'description' => 'Super Administrator (Pusat Akses & Konfigurasi Sistem)',
            ],
            [
                'name' => 'admin_sdm',
                'type' => Role::TYPE_ADMIN,
                'description' => 'Administrator Kepegawaian & SDM (Kelola Master Data Dosen & Karyawan)',
            ],
            [
                'name' => 'admin_lppm',
                'type' => Role::TYPE_ADMIN,
                'description' => 'Administrator Lembaga Penelitian & Pengabdian Masyarakat',
            ],
            [
                'name' => 'dosen',
                'type' => Role::TYPE_CIVITAS,
                'description' => 'Sivitas Akademika - Dosen Pengajar',
            ],
            [
                'name' => 'mahasiswa',
                'type' => Role::TYPE_CIVITAS,
                'description' => 'Sivitas Akademika - Mahasiswa',
            ],
            [
                'name' => 'karyawan',
                'type' => Role::TYPE_CIVITAS,
                'description' => 'Tenaga Kependidikan / Staf Non-Dosen',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                [
                    'type' => $roleData['type'],
                    'description' => $roleData['description'],
                ]
            );
        }
    }
}
