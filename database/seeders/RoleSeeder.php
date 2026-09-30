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
                'description' => 'Super Administrator (Pusat Akses & Konfigurasi Sistem)',
            ],
            [
                'name' => 'admin_sdm',
                'description' => 'Administrator Kepegawaian & SDM (Kelola Master Data Dosen & Karyawan)',
            ],
            [
                'name' => 'dosen',
                'description' => 'Sivitas Akademika - Dosen Pengajar',
            ],
            [
                'name' => 'mahasiswa',
                'description' => 'Sivitas Akademika - Mahasiswa',
            ],
            [
                'name' => 'karyawan',
                'description' => 'Tenaga Kependidikan / Staf Non-Dosen',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['description' => $roleData['description']]
            );
        }
    }
}
